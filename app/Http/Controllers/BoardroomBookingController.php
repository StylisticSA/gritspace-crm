<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\Boardroom;
use App\Models\BoardroomBooking;
use App\Models\Booking;
use App\Models\Discount;
use App\Models\FreeHours;
use App\Models\HotDeskBooking;
use App\Models\Location;
use App\Models\User;
use App\Models\VirtualBooking;
use App\Notifications\BoardroomBookingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BoardroomBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $boardrooms = Boardroom::with('location', 'amenities')->get();

        $locations = Location::select('name', 'address', 'city')->get();

        return Inertia::render('Bookings/Boardrooms/IndexBoardrooms', [
            'boardrooms'        => $boardrooms,
            'locations'         => $locations,
           
        ]);

    }

    /**
     * Display a boardroom of the resource.
     */
    public function view(BoardroomBooking $boardroom)
    {
        $user = auth()->user();


        if ($user->hasRole('Admin') || $user->hasRole('Super Admin')) {
            $boardrooms = $boardroom->load(['user','boardroom','boardroom.location']);

        } else {
            $boardrooms = BoardroomBooking::where('id', $boardroom->id)
                        ->where('user_id', auth()->id())
                        ->with(['user','boardroom','boardroom.location'])
                        ->firstOrFail();
        }

        return Inertia::render('Bookings/Boardrooms/ViewBoardroom', [
            'boardrooms' => $boardrooms,
        ]);
    }

    /**
     * Display a boardroom of the resource.
     */
    public function edit(Boardroom $bookedboardroom)
    {
        // dd($bookedboardroom);

        $locations = Location::select('id', 'name')->get();
        $amenities = Amenity::select('id', 'amenity_name')->get();
        $boardroom = $bookedboardroom->load(['location', 'amenities']);

        $closed = Booking::with('category')
                ->where('user_id', auth()->id())
                ->whereHas('category', fn($q) => 
                    $q->whereRaw("LOWER(name) IN ('closed office','closed offices')")
                )->first();

        $dedicated = Booking::with('category')
                ->where('user_id', auth()->id())
                ->whereHas('category', fn($q) => 
                    $q->whereRaw("LOWER(name) IN ('dedicated office','dedicated offices')")
                )->first();

        $hotdesk = HotDeskBooking::where('user_id', auth()->id())->first();

        $virtual = VirtualBooking::where('user_id', auth()->id())->first();

        
        $discountClosed = $closed ? Discount::with('office.bookings')->where('office_id', $closed->id)
                            ->where('office_type', 'closed')
                            ->first()
                        : null;

        $discountDedicated = $dedicated ? Discount::with('office.bookings')->where('office_id', $dedicated->id)
                            ->where('office_type', 'dedicated')
                            ->first()
                        : null;

        $discountHotdesk = $hotdesk ? Discount::with('hotdesk.hotdeskbookings')->where('help_desk_id', $hotdesk->id)
                            ->where('office_type', 'hotdesk')
                            ->first()
                        : null;

        $discountVirtual = $virtual ? Discount::with('virtuals.bookings')->where('virtual_office_id', $virtual->id)
                            ->where('office_type', 'virtuals')
                            ->first()
                        : null;

        $freeHours = FreeHours::where('user_id', auth()->id())->get();

        return Inertia::render('Bookings/Boardrooms/EditBoardroom', [
            'boardroom'         => $boardroom,
            'locations'         => $locations,
            'amenities'         => $amenities,
            'closedDiscount'    => $discountClosed,
            'dedicatedDiscount' => $discountDedicated,
            'hotdeskDiscount'   => $discountHotdesk,
            'virtualsDiscount'  => $discountVirtual,
            
        ]);
    }

    /**
     * Store offices the resource.
     */
    public function store(Request $request)
    {
       

        $validated = $request->validate([
           'boardroom_id'          => 'required|exists:boardrooms,id',
           'plan'                  => 'required|string|in:hourly,daily,monthly',

           'selected_dates' => 'required|array|min:1',
           'selected_dates.*' => 'date',

           'selected_times' => [
               'required_if:plan,hourly',
               'array',
           ],

           'months'                 => 'required|integer|min:1',
           'selected_price'         => 'required|numeric|min:0',

            'discounted_price'      => 'required|numeric|min:0',
            'discount_percentage'   => 'required|integer|min:0|max:100',


        ]);

        $office = Boardroom::findOrFail($validated['boardroom_id']);

        $conflict = BoardroomBooking::where('user_id', auth()->id())
                ->where('boardroom_id', $validated['boardroom_id'])
                ->where('plan', $validated['plan'])
                ->where(function ($query) use ($validated) {
                    if ($validated['plan'] === 'daily') {

                        foreach ($validated['selected_dates'] as $date) {
                            $query->orWhereJsonContains('selected_dates', $date);
                        }
                    }

                    if ($validated['plan'] === 'hourly') {
                        foreach ($validated['selected_dates'] as $date) {
                            $day = $date;
                            $timeSlots = $validated['selected_times'][$day] ?? [];

                            $query->orWhere(function ($q) use ($day, $timeSlots) {
                                $q->whereJsonContains('selected_dates', $day)
                                ->where(function ($timeCheck) use ($day, $timeSlots) {
                                    foreach ($timeSlots as $slot) {
                                        $timeCheck->orWhereJsonContains("selected_times->$day", $slot);
                                    }
                                });
                            });
                        }
                    }
                })
                ->exists();

        if ($conflict) {
            return back()->withErrors([
                'booking_conflict' => 'There is already a booking for the selected date(s) and time(s).',
            ])->withInput();
        }

        // dd($validated);

        $booking = BoardroomBooking::create([
            'user_id'         => auth()->id(),
            'boardroom_id'    => $office->id,
            'plan'            => $validated['plan'],
            'selected_dates'  => $validated['selected_dates'] ?? [],
            'selected_times'  => $validated['selected_times'] ?? null,
            'months'          => $validated['months'] ?? null,
            'selected_price'  => $validated['selected_price'],
            'discounted_price'    => $validated['discounted_price'],     
            'discount_percentage' => $validated['discount_percentage'],
            'status'          => 'pending',
        ]);

        // nortifications
        $bookingData = [
            'id' => $booking->id,
            'room_type' => $office->boardroom_name,
            'status' => 'pending',
            'user_name' => auth()->user()->name,
        ];

        auth()->user()->notify(new BoardroomBookingNotification($bookingData, 'created', 'user'));

        $admins = User::withRole('Admin')
            ->get()
            ->merge(User::withRole('Super Admin')->get())
            ->unique('id');


        $admins->each(fn ($user) => $user->notify(new BoardroomBookingNotification($bookingData, 'created', 'admin')));

        return back()->with('success', 'Booking created successfully!');
    }

    /**
     * View the booked HotDesks.
     */
    public function show(Request $request)
    {

        $user = auth()->user();
      
        $search = $request->input('search');

        $users = User::with('roles')
                ->whereHas('roles', function ($query) {
                    $query->whereIn(DB::raw('LOWER(name)'), ['user', 'users','admin','admins']);
                })->select('id', 'name')
                ->get();


        if ($user->hasRole('admin') || $user->hasRole('super admin')) {

            $bookings = BoardroomBooking::with(['user', 'boardroom.location'])
                        ->when($search, function ($query, $search) {
                            $query->whereHas('user', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('boardroom', function ($q) use ($search) {
                                $q->where('boardroom_name', 'like', "%{$search}%");
                            });
                        })
                        ->latest()
                        ->paginate(10);


        } else {
            $bookings = BoardroomBooking::with(['boardroom.location', 'user'])
                        ->where('user_id', $user->id)
                        ->when($search, function ($query, $search) {
                            $query->whereHas('user', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('boardroom', function ($q) use ($search) {
                                $q->where('boardroom_name', 'like', "%{$search}%");
                            });
                        })
                        ->latest()
                        ->paginate(10);

                        
        }

        $approvedBoardrooms = BoardroomBooking::with('boardroom.location')
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->get();

        return Inertia::render('Bookings/Boardrooms/ShowBoardrooms', [
            'bookings'              => $bookings,
            'approvedBoardrooms'    =>  $approvedBoardrooms,
            'users'                 => $users,
            'filters'               => [
                                        'search' => $search,
                                    ],
            
        ]);
    }

    public function paid(BoardroomBooking $booking)
    {
        $office = Boardroom::findOrFail($booking->boardroom_id);

        $booking->update([
            'status' => 'paid',
        ]);

        $bookingData = [
            'id' => $booking->id,
            'room_type' => $office->boardroom_name,
            'status' => 'paid',
            'user_name' => $booking->user->name,
        ];

        $booking->user->notify(new BoardroomBookingNotification($bookingData, 'paid', 'user'));

        User::withRole('Super Admin')->get()
            ->each(fn ($user) => $user->notify(new BoardroomBookingNotification($bookingData, 'paid', 'admin')));

        User::withRole('Admin')->get()
            ->each(fn ($user) => $user->notify(new BoardroomBookingNotification($bookingData, 'paid', 'admin')));

        return back()->with('success', 'Boardroom Status Marked Paid.');

       

    }

    public function approve(BoardroomBooking $booking)
    {
        $office = Boardroom::findOrFail($booking->boardroom_id);

        $booking->update([
            'status' => 'approved',
        ]);

        $bookingData = [
            'id' => $booking->id,
            'room_type' => $office->boardroom_name,
            'status' => 'approved',
            'user_name' => $booking->user->name,
        ];

        $booking->user->notify(new BoardroomBookingNotification($bookingData, 'approved', 'user'));

        User::withRole('Super Admin')->get()
            ->each(fn ($user) => $user->notify(new BoardroomBookingNotification($bookingData, 'approved', 'admin')));

        User::withRole('Admin')->get()
            ->each(fn ($user) => $user->notify(new BoardroomBookingNotification($bookingData, 'approved', 'admin')));

        return back()->with('success', 'Booking approved successfully.');

        return redirect()->back()->with([
            'success' => 'Booking approved successfully.',
            'type' => 'success',
        ]);

    }

    public function reject(Request $request, BoardroomBooking $booking)
    {
        $office = Boardroom::findOrFail($booking->boardroom_id);

        $booking->update([
            'status' => 'rejected',
        ]);

        $bookingData = [
            'id' => $booking->id,
            'room_type' => $office->boardroom_name,
            'status' => 'rejected',
            'user_name' => $booking->user->name,
        ];

        $booking->user->notify(new BoardroomBookingNotification($bookingData, 'rejected', 'user'));

        User::withRole('Super Admin')->get()
            ->each(fn ($user) => $user->notify(new BoardroomBookingNotification($bookingData, 'rejected', 'admin')));

        User::withRole('Admin')->get()
            ->each(fn ($user) => $user->notify(new BoardroomBookingNotification($bookingData, 'rejected', 'admin')));

        return redirect()->back()->with([
            'success' => 'Booking rejected successfully.',
            'type' => 'rejected',
        ]);

    }

    public function cancel(Request $request, BoardroomBooking $booking)
    {
        $office = Boardroom::findOrFail($booking->boardroom_id);

        $booking->update([
            'status' => 'cancelled',
        ]);

        $bookingData = [
            'id' => $booking->id,
            'room_type' => $office->boardroom_name,
            'status' => 'cancelled',
            'user_name' => $booking->user->name,
        ];

        $booking->user->notify(new BoardroomBookingNotification($bookingData, 'cancelled', 'user'));

        User::withRole('Super Admin')->get()
            ->each(fn ($user) => $user->notify(new BoardroomBookingNotification($bookingData, 'cancelled', 'admin')));

        User::withRole('Admin')->get()
            ->each(fn ($user) => $user->notify(new BoardroomBookingNotification($bookingData, 'cancelled', 'admin')));

        return redirect()->back()->with([
            'success' => 'Booking cancelled successfully.',
            'type' => 'cancelled',
        ]);

    }

}
