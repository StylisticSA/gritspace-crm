<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\HelpDesk;
use App\Models\HotDeskBooking;
use App\Models\Location;
use App\Models\User;
use App\Notifications\HotDeskBookingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HotDeskBookingController extends Controller
{
    /**
     * Display a office of the resource.
    */
    public function edit(HelpDesk $hotDesk)
    {
        $locations = Location::select('id', 'name')->get();

        $hotdesks = $hotDesk->load(['location', 'amenities']);

        $discount = Discount::where('help_desk_id',$hotDesk->id)
                   ->where('office_type', 'hotdesk')->first(['name','discount']);

        return Inertia::render('Bookings/HotDesks/EditHotDesk', [
            'helpDesks' => $hotdesks,
            'locations' => $locations,
            'discount' => $discount,
        ]);

    }

    /**
     * Display a office of the resource.
    */
    public function view(HotDeskBooking $hotDesk)
    {

        $user = auth()->user();

        if ($user->hasRole('admin') || $user->hasRole('super admin')) {
            $hotdesk = $hotDesk->load(['user','helpdesk','helpdesk.location',]);

        } else {
            $hotdesk = HotDeskBooking::where('id', $hotDesk->id)
                        ->where('user_id', auth()->id())
                        ->with(['user', 'helpdesk', 'helpdesk.location'])
                        ->firstOrFail();

        }

        return Inertia::render('Bookings/HotDesks/ViewHotDesk', [
                    'helpDesks' => $hotdesk,
            ]);


    }

    /**
     * Store hotdesks offices the resource.
     */
    public function store(Request $request)
    {

        // dd($request);
        $validated = $request->validate([
            'hotdesk_id'        => 'required|exists:help_desks,id',
            'plan'              => 'required',
            'is_half_day'       => 'required|boolean',
            'selected_dates'    => 'required|array|min:1',
            'selected_dates.*'  => 'date|after_or_equal:today',
            'time_slots'        => 'nullable|array',
            'days_count'        => 'required|integer|min:1',
            'selected_price'    => 'required|numeric|min:1',
        ]);

        // Manually validate the nested time_slots structure
        foreach ($validated['time_slots'] ?? [] as $date => $slot) {
            if (!is_array($slot) || !in_array($slot['block'] ?? null, ['morning', 'afternoon'])) {
                return back()->withErrors([
                    'time_slots' => "Invalid block for date $date. Must be 'morning' or 'afternoon'."
                ])->withInput();
            }
        }

        // Check for existing overlapping booking
        $conflict = HotDeskBooking::where('user_id', auth()->id())
            ->where('helpdesk_id', $validated['hotdesk_id'])
            ->where('plan', $validated['plan'])
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($validated) {
                foreach ($validated['selected_dates'] as $date) {
                    $query->orWhereJsonContains('selected_dates', $date);
                }
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors([
                'booking_conflict' => 'You already have a booking with this plan type that overlaps the selected dates.',
            ])->withInput();
        }

        if (!empty($validated['time_slots'])) {
            $half_day = $validated['is_half_day'] = 1;
            $validated['selected_dates'] = [];
        }

        //Store booking
        $hotDesk = HotDeskBooking::create([
            'user_id'         => auth()->id(),
            'helpdesk_id'     => $validated['hotdesk_id'],
            'plan'            => $validated['plan'],
            'is_half_day'     => $half_day ?? 0,
            'selected_dates'  => $validated['selected_dates'],
            'time_slots'      => $validated['time_slots'] ?? null,
            'days_count'      => $validated['days_count'],
            'selected_price'  => $validated['selected_price'],
            'status'          => 'pending',
        ]);

        $office = HelpDesk::findOrFail($validated['hotdesk_id']);

        // nortifications
        $bookingData = [
            'id' => $hotDesk->id,
            'room_type' => $office->virtualoffice_name,
            'status' => 'created',
            'user_name' => auth()->user()->name,
        ];

        auth()->user()->notify(new HotDeskBookingNotification($bookingData, 'created', 'user'));

        $admins = User::withRole('Admin')
            ->get()
            ->merge(User::withRole('Super Admin')->get())
            ->unique('id');


        $admins->each(fn ($user) => $user->notify(new HotDeskBookingNotification($bookingData, 'created', 'admin')));

        return back()->with('success', 'Booking created successfully!');
    }

    /**
     * View the booked HotDesks.
     */
    public function show(Request $request)
    {
        $user = auth()->user();

        $search = $request->input('search');

              

        if ($user->hasRole('admin') || $user->hasRole('super admin')) {

            $users = User::with('roles')
                ->whereHas('roles', function ($query) {
                    $query->whereIn(DB::raw('LOWER(name)'), ['user', 'users','admin','admins']);
                })->select('id', 'name')
                ->get();

            $bookings = HotDeskBooking::with(['user', 'helpdesk'])
                        ->when($search, function ($query) use ($search) {
                            $query->where(function ($q) use ($search) {
    
                                $q->whereHas('user', function ($userQuery) use ($search) {
                                    $userQuery->where('name', 'LIKE', '%' . $search . '%');
                                });

                                $q->orWhereHas('helpdesk', function ($helpdeskQuery) use ($search) {
                                    $helpdeskQuery->where('help_desk_name', 'LIKE', '%' . $search . '%');
                                });
                            });
                        })
                        ->latest()
                        ->paginate(10);


        } else {
            $bookings = HotDeskBooking::with(['user', 'helpdesk'])
                        ->where('user_id', $user->id)
                        ->when($search, function ($query) use ($search) {
                            $query->where(function ($q) use ($search) {
           
                                $q->whereHas('user', function ($userQuery) use ($search) {
                                    $userQuery->where('name', 'LIKE', '%' . $search . '%');
                                });

                                $q->orWhereHas('helpdesk', function ($helpdeskQuery) use ($search) {
                                    $helpdeskQuery->where('help_desk_name', 'LIKE', '%' . $search . '%');
                                });
                            });
                        })
                        ->latest()
                        ->paginate(10);

        }


        return Inertia::render('Bookings/HotDesks/ShowHotDesks', [
            'bookings' => $bookings,
            'users' => $users,
            'filters' => [
                'search' => $search,
            ]
        ]);
    }

    /**
    * Remove the resource from storage.
    */
    public function destroy(HotDeskBooking $hotdesk)
    {

        $hotdesk->delete();

        return back()->with('success', 'A Hot Desks booking has been deleted successfully.');
    }

    /**
     * Deleted office of office.
    */
    public function deleted(Request $request)
    {
        $search = $request->input('search');

        $user = auth()->user();

        if ($user->hasRole('admin') || $user->hasRole('super admin')) {

            $bookings = HotDeskBooking::with(['user', 'helpdesk'])
                       ->onlyTrashed()
                       ->latest()
                       ->paginate(10);

        }

        return Inertia::render('Bookings/HotDesks/DeletedHotDesks', [
            'bookings' => $bookings,
            'filters' => [
                'search' => $search,
            ]
        ]);

    }

    /**
     * Restore a deleted office.
    */
    public function restore($id)
    {
        $booking = HotDeskBooking::onlyTrashed()->findOrFail($id);

        $booking->restore();

        return redirect()->to('/hotdesk-booking')->with('success', 'Booking has been restored successfully.');
    }

    /**
     * Paid function
     *
     * @param HotDeskBooking $virtual
     * @return void
     */
    public function paid(HotDeskBooking $hotdesk)
    {
        // dd($hotdesk);

        $hotdesk->update([
            'status' => 'paid',
        ]);

        $office = HelpDesk::findOrFail($hotdesk->helpdesk_id);

        // nortifications
        $bookingData = [
            'id' => $hotdesk->id,
            'room_type' => $office->virtualoffice_name,
            'status' => 'paid',
            'user_name' => auth()->user()->name,
        ];

        $hotdesk->user->notify(new HotDeskBookingNotification($bookingData, 'paid', 'user'));

        $admins = User::withRole('Admin')
            ->get()
            ->merge(User::withRole('Super Admin')->get())
            ->unique('id');


        $admins->each(fn ($user) => $user->notify(new HotDeskBookingNotification($bookingData, 'paid', 'admin')));


        return back()->with('success', 'Booking status changed to Paid successfully.');
    }

    /**
     * Approve function
     *
     * @param HotDeskBooking $hotdeskBooking
     * @return void
     */
    public function approve(HotDeskBooking $hotdesk)
    {
        $hotdesk->update([
            'status' => 'approved',
        ]);

        $office = HelpDesk::findOrFail($hotdesk->helpdesk_id);

        // nortifications
        $bookingData = [
            'id' => $hotdesk->id,
            'room_type' => $office->virtualoffice_name,
            'status' => 'approved',
            'user_name' => auth()->user()->name,
        ];

        $hotdesk->user->notify(new HotDeskBookingNotification($bookingData, 'approved', 'user'));

        $admins = User::withRole('Admin')
            ->get()
            ->merge(User::withRole('Super Admin')->get())
            ->unique('id');


        $admins->each(fn ($user) => $user->notify(new HotDeskBookingNotification($bookingData, 'approved', 'admin')));


        return back()->with('success', 'Booking approved successfully.');
    }

    public function reject(Request $request, HotDeskBooking $hotdesk)
    {


        $hotdesk->update([
            'status' => 'rejected',
        ]);

        $office = HelpDesk::findOrFail($hotdesk->helpdesk_id);

        // nortifications
        $bookingData = [
            'id' => $hotdesk->id,
            'room_type' => $office->virtualoffice_name,
            'status' => 'rejected',
            'user_name' => auth()->user()->name,
        ];

        $hotdesk->user->notify(new HotDeskBookingNotification($bookingData, 'rejected', 'user'));

        $admins = User::withRole('Admin')
            ->get()
            ->merge(User::withRole('Super Admin')->get())
            ->unique('id');


        $admins->each(fn ($user) => $user->notify(new HotDeskBookingNotification($bookingData, 'rejected', 'admin')));


        return back()->with('success', 'Booking rejected.');
    }

    public function cancel(Request $request, HotDeskBooking $hotdesk)
    {

        $hotdesk->update([
            'status' => 'cancelled',
        ]);

        $office = HelpDesk::findOrFail($hotdesk->helpdesk_id);

        // nortifications
        $bookingData = [
            'id' => $hotdesk->id,
            'room_type' => $office->virtualoffice_name,
            'status' => 'cancelled',
            'user_name' => auth()->user()->name,
        ];

        $hotdesk->user->notify(new HotDeskBookingNotification($bookingData, 'cancelled', 'user'));

        $admins = User::withRole('Admin')
            ->get()
            ->merge(User::withRole('Super Admin')->get())
            ->unique('id');


        $admins->each(fn ($user) => $user->notify(new HotDeskBookingNotification($bookingData, 'cancelled', 'admin')));

        return back()->with('success', 'Booking cancelled.');
    }


}
