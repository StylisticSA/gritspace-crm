<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Location;
use App\Models\OfficePricing;
use App\Models\User;
use App\Models\VirtualBooking;
use App\Models\VirtualOffice;
use App\Notifications\VirtualBookingNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VirtualBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $virtualOffices = VirtualOffice::with('location', 'amenities')
                         ->select(
                             'id',
                             'location_id',
                             'virtualoffice_name',
                             'price'
                         )->get();


        $locations = Location::select('name', 'address', 'city')->get();

         $approvedVirtuals = VirtualBooking::with('virtualOffice.location')
                            ->where('user_id', auth()->id())
                            ->where('status', 'approved')
                            ->get();


        return Inertia::render('Bookings/Virtual/IndexVirtual', [
            'virtualOffices'    => $virtualOffices,
            'locations'         => $locations,
            'approvedVirtuals'  => $approvedVirtuals
        ]);

    }

    /**
     * Display a office of the resource.
     */
    public function edit(Request $request, VirtualOffice $virtual)
    {

        $locations = Location::select('id', 'name')->get();

        $pricings = OfficePricing::select('id', 'category_name', 'pricing_type', 'rate')
            ->where('category_name', 'Virtual Office')
            ->get();

        $bookedRanges = VirtualBooking::where('virtual_office_id', $virtual->id)
                        ->where('user_id', auth()->id())
                        ->get(['plan', 'selected_dates']);

        $virtuals = $virtual->load(['location','amenities']);

        $discount = Discount::where('virtual_office_id',$virtual->id)
                   ->where('office_type', 'virtuals')->first(['name','discount']);

        return Inertia::render('Bookings/Virtual/EditVirtual', [
            'virtual'       => $virtuals,
            'locations'     => $locations,
            'pricings'      => $pricings,
            'bookedRange'   => $bookedRanges,  
            'discount'      => $discount,
        ]);


    }

    /**
     * Display a office of the resource.
     */
    public function view(Request $request, VirtualBooking $virtual)
    {
        $user = auth()->user();

        if ($user->hasRole('admin') || $user->hasRole('super admin')) {
            $virtuals = $virtual->load(['user','virtualOffice','virtualOffice.location']);

        } else {
            $virtuals = VirtualBooking::where('id', $virtual->id)
                        ->where('user_id', auth()->id())
                        ->with(['user', 'virtualOffice', 'virtualOffice.location'])
                        ->firstOrFail();
        }

        return Inertia::render('Bookings/Virtual/ViewVirtual', [
            'virtual'       => $virtuals,
        ]);


    }

    /**
     * Work on store.
     */
    public function store(Request $request)
    {
        // dd($request);

        $validated = $request->validate([
            'virtual_office_id' => 'required|exists:virtual_offices,id',
            'plan'              => 'required|string|max:255',
            'start_date'        => 'required|date|after_or_equal:today',
            'end_date'          => 'nullable|date|after:start_date',
            'months'            => 'required|integer|min:3|max:12',
            'selected_price'    => 'required|numeric|min:0',
            'selected_dates'    => 'nullable|array',
            'selected_dates.*'  => 'date',
        ]);

        $startDate = Carbon::parse($validated['start_date'])->toDateString();
        $endDate = Carbon::parse($validated['end_date'])->toDateString();

        $hasOverlap = VirtualBooking::where('user_id', $request->user()->id)
            ->where('virtual_office_id', $validated['virtual_office_id'])
            ->where('plan', $validated['plan'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();

        if ($hasOverlap) {
            return redirect()->back()
                ->withErrors([
                    'booking_conflict' => 'You’ve already booked this office with that plan in the selected date range.',
                ])
                ->withInput();
        }

        $office = VirtualOffice::findOrFail($validated['virtual_office_id']);

        $booking = VirtualBooking::create([
            'user_id'           => $request->user()->id,
            'virtual_office_id' => $validated['virtual_office_id'],
            'plan'              => $validated['plan'],
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'months'            => $validated['months'],
            'selected_price'    => $validated['selected_price'],
            'selected_dates'    => $validated['selected_dates'],
        ]);

        // nortifications
        $bookingData = [
            'id' => $booking->id,
            'room_type' => $booking->virtualOffice->virtualoffice_name,
            'status' => 'pending',
            'user_name' => auth()->user()->name,
        ];

        auth()->user()->notify(new VirtualBookingNotification($bookingData, 'created', 'user'));

        $admins = User::withRole('Admin')
            ->get()
            ->merge(User::withRole('Super Admin')->get())
            ->unique('id');


        $admins->each(fn ($user) => $user->notify(new VirtualBookingNotification($bookingData, 'created', 'admin')));


        return redirect()->route('booking.virtual', ['virtual' => $validated['virtual_office_id']])
            ->with('success', 'Virtual office booked successfully!');

    }

    /**
     * View the booked virtuals.
     */
    public function show(Request $request)
    {

        $user = auth()->user();

        $search = $request->input('search');

        if ($user->hasRole('admin') || $user->hasRole('super admin')) {
            $bookings = VirtualBooking::with(['user', 'virtualOffice'])
                        ->when($search, function ($query, $search) {
                            $query->whereHas('user', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('virtualOffice', function ($q) use ($search) {
                                $q->where('virtualoffice_name', 'like', "%{$search}%");
                            });
                        })
                        ->latest()
                        ->paginate(10);

            $users = User::with('roles')
                    ->whereHas('roles', function ($query) {
                        $query->whereIn(DB::raw('LOWER(name)'), ['user', 'users','admin','admins']);
                    })->select('id', 'name')
                    ->get();

                    // dd($users);

            } else {
            $bookings = VirtualBooking::with(['virtualOffice', 'user'])
                        ->where('user_id', $user->id)
                        ->when($search, function ($query, $search) {
                            $query->whereHas('user', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('virtualOffice', function ($q) use ($search) {
                                $q->where('virtualoffice_name', 'like', "%{$search}%");
                            });
                        })
                        ->latest()
                        ->paginate(10);


        }


        return Inertia::render('Bookings/Virtual/ShowVirtual', [
            'bookings' => $bookings,
            'users' => $users,
            'filters' => [
                'search' => $search,
            ]
        ]);
    }

    /**
     * Paid function
     *
     * @param VirtualBooking $virtual
     * @return void
     */
    public function paid(VirtualBooking $virtual)
    {

        // dd($request);

        $virtual->update([
            'status' => 'paid',

        ]);

        $office = VirtualOffice::findOrFail($virtual->virtual_office_id);

        // nortifications
        $bookingData = [
            'id' => $virtual->id,
            'room_type' => $office->virtualoffice_name,
            'status' => 'paid',
            'user_name' => auth()->user()->name,
        ];

        $virtual->user->notify(new VirtualBookingNotification($bookingData, 'paid', 'user'));

        $admins = User::withRole('Admin')
            ->get()
            ->merge(User::withRole('Super Admin')->get())
            ->unique('id');


        $admins->each(fn ($user) => $user->notify(new VirtualBookingNotification($bookingData, 'paid', 'admin')));


        return back()->with('success', 'Virtual Office Marked Paid Successfully.');
    }
    /**
     * Approve function
     *
     * @param VirtualBooking $virtual
     * @return void
     */
    public function approve(VirtualBooking $virtual)
    {
        $virtual->update([
            'status' => 'approved',

        ]);

        $office = VirtualOffice::findOrFail($virtual->virtual_office_id);

        // nortifications
        $bookingData = [
            'id' => $virtual->id,
            'room_type' => $office->virtualoffice_name,
            'status' => 'approved',
            'user_name' => auth()->user()->name,
        ];

        $virtual->user->notify(new VirtualBookingNotification($bookingData, 'approved', 'user'));

        $admins = User::withRole('Admin')
            ->get()
            ->merge(User::withRole('Super Admin')->get())
            ->unique('id');


        $admins->each(fn ($user) => $user->notify(new VirtualBookingNotification($bookingData, 'approved', 'admin')));


        return back()->with('success', 'Booking approved successfully.');
    }

    public function reject(Request $request, VirtualBooking $virtual)
    {
        $virtual->update([
            'status' => 'rejected',
        ]);

        $office = VirtualOffice::findOrFail($virtual->virtual_office_id);

        // nortifications
        $bookingData = [
            'id' => $virtual->id,
            'room_type' => $office->virtualoffice_name,
            'status' => 'rejected',
            'user_name' => auth()->user()->name,
        ];

        $virtual->user->notify(new VirtualBookingNotification($bookingData, 'rejected', 'user'));

        $admins = User::withRole('Admin')
            ->get()
            ->merge(User::withRole('Super Admin')->get())
            ->unique('id');


        $admins->each(fn ($user) => $user->notify(new VirtualBookingNotification($bookingData, 'rejected', 'admin')));


        return back()->with('success', 'Booking rejected.');
    }

    public function cancel(Request $request, VirtualBooking $virtual)
    {
        $virtual->update([
            'status' => 'cancelled',
        ]);

        $office = VirtualOffice::findOrFail($virtual->virtual_office_id);

        // nortifications
        $bookingData = [
            'id' => $virtual->id,
            'room_type' => $office->virtualoffice_name,
            'status' => 'cancelled',
            'user_name' => auth()->user()->name,
        ];

        $virtual->user->notify(new VirtualBookingNotification($bookingData, 'cancelled', 'user'));

        $admins = User::withRole('Admin')
            ->get()
            ->merge(User::withRole('Super Admin')->get())
            ->unique('id');

        $admins->each(fn ($user) => $user->notify(new VirtualBookingNotification($bookingData, 'cancelled', 'admin')));


        return back()->with('success', 'Booking cancelled.');
    }

    
}
