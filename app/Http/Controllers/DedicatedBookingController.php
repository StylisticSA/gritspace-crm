<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Location;
use App\Models\Office;
use App\Models\OfficePricing;
use App\Models\User;
use App\Notifications\BookingNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DedicatedBookingController extends Controller
{
    /**
     * Dedicated Desks the specified resource.
     */
    public function show(Office $Office, Request $request)
    {
        $search = $request->input('search');

        $user = auth()->user();

        if ($user->hasRole('admin') || $user->hasRole('super admin')) {
            $bookings = Booking::with(['user', 'office.location', 'category'])
                        ->whereHas('category', function ($query) {
                            $query->whereRaw("LOWER(name) IN ('dedicated desk', 'dedicated desks')");
                        })
                        ->when($search, function ($query) use ($search) {
                            $query->where(function ($q) use ($search) {
                              
                                $q->whereHas('office', function ($officeQuery) use ($search) {
                                    $officeQuery->where('office_name', 'LIKE', '%' . $search . '%');
                                });
                      
                                $q->orWhereHas('user', function ($userQuery) use ($search) {
                                    $userQuery->where('name', 'LIKE', '%' . $search . '%');
                                });
                            });
                        })
                        ->latest()
                        ->paginate(10);

            $users = User::with('roles')
                    ->whereHas('roles', function ($query) {
                        $query->whereIn(DB::raw('LOWER(name)'), ['user', 'users','admin','admins']);
                    })->select('id', 'name')
                    ->get();

        } else {
            $bookings = Booking::with(['office.location', 'category'])
                ->whereHas('category', function ($query) {
                    $query->whereRaw("LOWER(name) IN ('dedicated desk', 'dedicated desks')");
                })
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(10);
            }
                
                $approvedDedicated = Booking::with(['office', 'office.location', 'category'])
                            ->whereHas('category', function ($query) {
                                $query->whereRaw("LOWER(name) IN ('dedicated desk', 'dedicated desks')");
                            })
                            ->where('user_id', auth()->id())
                            ->where('status', 'approved')
                            ->get();   

        return Inertia::render('Bookings/Dedicated/ShowDedicated', [
            'bookings' => $bookings,
            'users' => $users ?? null,
            'approvedDedicated'    => $approvedDedicated, 
            'filters' => [
                'search' => $search,
            ]
        ]);
    }

    /**
     * View Dedicated Desks resource.
     */
    public function view(Booking $dedicated)
    {

        $user = auth()->user();

        if ($user->hasRole('admin') || $user->hasRole('super admin')) {

            $office =  $dedicated->load([
                        'user',
                        'office',
                        'category',
                        'office.amenities',
                        'office.location'
                    ]);

        } else {
            $office->load([
                 'user',
                 'office',
                 'category',
                 'office.amenities',
                 'office.location',
             ]);

            $office->setRelation(
                'bookings',
                $office->bookings()
                    ->where('user_id', $user->id)
                    ->with(['category'])
                    ->get()
            );

        }

        return Inertia::render('Bookings/Dedicated/ViewDedicated', [
            'dedicated' => $office,
        ]);
    }

    /**
    * Store Dedicated Desks the resource.
    */
    public function store(Request $request)
    {

        // dd($request);

        $validated = $request->validate([
            'office_id'        => 'required|integer|exists:offices,id',
            'plan'             => 'required|string|in:daily,monthly,premium,standard',
            'selected_dates'   => 'nullable|array',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'months'           => 'nullable|integer|min:1',
            'weekdays_count'   => 'nullable|integer|min:0',
            'total_price'      => 'required|numeric',
            'category_id'      => 'nullable|numeric|exists:categories,id',
        ]);

        $office = Office::findOrFail($validated['office_id']);

        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $end   = Carbon::parse($validated['end_date'])->endOfDay();

        $conflict = Booking::where('user_id', auth()->id())
            ->where('office_id', $office->id)
            ->where('plan', $validated['plan']) // same plan only
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_date', '<=', $start)
                        ->where('end_date', '>=', $end);
                    });
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors([
                'booking_conflict' => 'You already have a booking with this plan type that overlaps the selected dates.',
            ])->withInput();
        }


        $categorySlug = strtolower(trim(Str::slug($office->category->name)));

        $booking = Booking::create([
            'user_id'         => auth()->id(),
            'office_id'       => $office->id,
            'category_id'     => $validated['category_id'] ?? null,
            'plan'            => $validated['plan'],
            'selected_dates'  => $validated['selected_dates'] ?? null,
            'weekdays_count'  => $validated['weekdays_count'] ?? null,
            'months'          => $validated['months'] ?? null,
            'start_date'      => $start->toDateString(),
            'end_date'        => $end->toDateString(),
            'total_price'     => $validated['total_price'],
            'status'          => 'pending',
        ]);



        // nortifications
        $bookingData = [
            'id' => $booking->id,
            'room_type' => $office->office_name,
            'user_name' => auth()->user()->name,
            'status' => 'created',
            'category' => $categorySlug,
        ];

        // Notify the user who booked
        auth()->user()->notify(new BookingNotification($bookingData, 'created', 'user'));

        $admins = User::withRole('Admin')
            ->get()
            ->merge(User::withRole('Super Admin')->get())
            ->unique('id');


        $admins->each(fn ($user) => $user->notify(new BookingNotification($bookingData, 'created', 'admin')));

        return back()->with('success', 'Booking created successfully!');
    }

    /**
     * Edit Dedicated Desks.
     */
    public function edit(Office $dedicated)
    {
        $locations = Location::select('id', 'name')->get();

        $pricings = OfficePricing::select('id', 'category_name', 'pricing_type', 'rate')
            ->where('category_name', 'Dedicated Desk')
            ->get();

        $amenities = Amenity::select('id', 'amenity_name')->get();

        $categories = Category::select('id', 'name')
            ->where('name', '!=', 'Virtual Office')
            ->get();

        $rawDates = Booking::where('user_id', auth()->id())
            ->where('office_id', optional($dedicated)->id)
            ->pluck('selected_dates');

        $selectedDates = $rawDates
            ->map(function ($item) {
                if (is_string($item)) {
                    $decoded = json_decode($item, true);
                    return is_array($decoded) ? $decoded : [];
                }
                return is_array($item) ? $item : [];
            })
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $rangeDates = Booking::where('user_id', auth()->id())
            ->where('office_id', optional($dedicated)->id)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get()
            ->flatMap(function ($booking) {
                $start = Carbon::parse($booking->start_date);
                $end = Carbon::parse($booking->end_date);
                $dates = [];
                while ($start->lte($end)) {
                    $dates[] = $start->toDateString();
                    $start->addDay();
                }
                return $dates;
            })
            ->unique()
            ->toArray();

        $allBookedDates = collect([...$selectedDates, ...$rangeDates])
            ->unique()
            ->values();

            // dd($dedicated);
        $discount = Discount::where('office_id',$dedicated->id)
                    ->where('office_type', 'dedicated')->first(['name','discount']);

        return Inertia::render('Bookings/Dedicated/EditDedicated', [
            'office' => $dedicated->load(['location', 'pricing', 'amenities']),
            'locations' => $locations,
            'pricings' => $pricings,
            'amenities' => $amenities,
            'categories' => $categories,
            'bookedDates' => $allBookedDates,
            'discount' => $discount,
        ]);
    }

    /**
     * Paid a dedicated desk.
     */
    public function paid(Booking $booking)
    {
        $booking->update([
            'status' => 'paid',
        ]);

        $office = Office::findOrFail($booking->office_id);

        $categorySlug = Str::slug($office->category->name);

        $bookingData = [
            'id' => $booking->id,
            'room_type' => $office->office_name,
            'status' => 'paid',
            'user_name' => auth()->user()->name,
            'category' => $categorySlug,
        ];

        // Notify the booking owner
        $booking->user->notify(new BookingNotification($bookingData, 'paid', 'user'));

        $admins = User::withRole('Super Admin')
            ->get()
            ->merge(User::withRole('Admin')->get())
            ->unique('id');

        $admins->each(
            fn ($user) =>
            $user->notify(new BookingNotification($bookingData, 'paid', 'admin'))
        );

        return back()->with('success', 'Booking paid successfully.');
    }

    /**
     * Approve a dedicated desk.
     */
    public function approve(Booking $booking)
    {
        $booking->update([
            'status' => 'approved',
        ]);

        $office = Office::findOrFail($booking->office_id);

        $categorySlug = Str::slug($office->category->name);

        $bookingData = [
            'id' => $booking->id,
            'room_type' => $office->office_name,
            'status' => 'approved',
            'user_name' => auth()->user()->name,
            'category' => $categorySlug,
        ];

        // Notify the booking owner
        $booking->user->notify(new BookingNotification($bookingData, 'approved', 'user'));

        $admins = User::withRole('Super Admin')
            ->get()
            ->merge(User::withRole('Admin')->get())
            ->unique('id');

        $admins->each(
            fn ($user) =>
            $user->notify(new BookingNotification($bookingData, 'approved', 'admin'))
        );

        return back()->with('success', 'Booking approved successfully.');
    }

    /**
     * Reject a dedicated desk.
     */
    public function reject(Request $request, Booking $booking)
    {
        $booking->update([
            'status' => 'rejected',
        ]);

        $office = Office::findOrFail($booking->office_id);
        $categorySlug = Str::slug($office->category->name);

        $bookingData = [
            'id' => $booking->id,
            'room_type' => $office->office_name,
            'status' => 'rejected',
            'user_name' => auth()->user()->name,
            'category' => $categorySlug,
        ];

        $booking->user->notify(new BookingNotification($bookingData, 'rejected', 'user'));

        $admins = User::withRole('Super Admin')
            ->get()
            ->merge(User::withRole('Admin')->get())
            ->unique('id');

        $admins->each(
            fn ($user) =>
            $user->notify(new BookingNotification($bookingData, 'rejected', 'admin'))
        );


        return back()->with('success', 'Booking rejected.');
    }

    /**
     * Cancelled a dedicated desk.
     */
    public function cancel(Request $request, Booking $booking)
    {
        $booking->update([
            'status' => 'cancelled',
        ]);

        $office = Office::findOrFail($booking->office_id);
        $categorySlug = Str::slug($office->category->name);

        $bookingData = [
            'id' => $booking->id,
            'room_type' => $office->office_name,
            'status' => 'cancelled',
            'user_name' => auth()->user()->name,
            'category' => $categorySlug,
        ];

        $booking->user->notify(new BookingNotification($bookingData, 'cancelled', 'user'));

        $admins = User::withRole('Super Admin')
            ->get()
            ->merge(User::withRole('Admin')->get())
            ->unique('id');

        $admins->each(
            fn ($user) =>
            $user->notify(new BookingNotification($bookingData, 'cancelled', 'admin'))
        );


        return back()->with('success', 'Booking cancelled.');
    }


}
