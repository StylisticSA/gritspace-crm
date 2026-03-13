<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\HotDeskBooking;
use App\Models\VirtualBooking;
use App\Models\BoardroomBooking;

class CalendarController extends Controller
{
    public function boardroom(Request $request)
    {
        $user         = auth()->user();
        $palette      = array_values(config('colors.across'));
        $paletteCount = count($palette);

        $boardrooms = BoardroomBooking::with(
                'user:id,name',
                'boardroom:id,boardroom_name,location_id',
                'boardroom.location:id,name'
            )
            ->whereIn('status', ['approved', 'paid'])
            ->get()
            ->flatMap(function ($vb) use ($user, $palette, $paletteCount) {
                // Deterministic color assignment
                $key   = $vb->boardroom->boardroom_name . '-' . $vb->plan . '-' . $vb->boardroom->location_id;
                $index = crc32($key) % $paletteCount;
                $color = $palette[$index];

                if ($vb->plan === 'daily') {
                    $dates = is_array($vb->selected_dates)
                        ? $vb->selected_dates
                        : json_decode($vb->selected_dates, true);

                    return collect($dates)->map(function ($date) use ($vb, $color) {
                        return [
                            // ✅ Unique ID per booking + date
                            'id' => "{$vb->id}-{$date}",
                            'title' => "{$vb->user->name}",
                            'start' => Carbon::parse($date)->format('Y-m-d'),
                            'backgroundColor' => $color['bg'],
                            'borderColor'     => $color['border'],
                            'textColor'       => $color['text'],
                            'extendedProps'   => [
                                'boardroom'    => $vb->boardroom->boardroom_name,
                                'boardroom_id' => $vb->boardroom_id,
                                'location_id'  => $vb->boardroom->location_id,
                                'location'     => $vb->boardroom->location->name,
                                'user'         => $vb->user->name,
                                'plan'         => 'daily',
                                'price'        => $vb->selected_price,
                                'times'        => [],
                                'isOwner'      => $user->id === $vb->user_id, // ✅ flag for frontend
                            ],
                        ];
                    });
                }

                if ($vb->plan === 'hourly') {
                    $times = is_array($vb->selected_times)
                        ? $vb->selected_times
                        : json_decode($vb->selected_times, true);

                    return collect($times)->flatMap(function ($timeSlots, $date) use ($vb, $color, $user) {
                        $parsedDate = Carbon::parse($date)->format('Y-m-d');

                        return collect($timeSlots)
                            ->sort()
                            ->map(function ($time) use ($vb, $color, $parsedDate, $user) {
                                $start = Carbon::parse("{$parsedDate} {$time}")->format('Y-m-d\TH:i:s');
                                $end   = Carbon::parse("{$parsedDate} {$time}")->addHour()->format('Y-m-d\TH:i:s');

                                return [
                                    // ✅ Unique ID per booking + date + timeslot
                                    'id' => "{$vb->id}-{$parsedDate}-{$time}",
                                    'title' => "{$vb->user->name}",
                                    'start' => $start,
                                    'end'   => $end,
                                    'backgroundColor' => $color['bg'],
                                    'borderColor'     => $color['border'],
                                    'textColor'       => $color['text'],
                                    'extendedProps'   => [
                                        'boardroom'    => $vb->boardroom->boardroom_name,
                                        'boardroom_id' => $vb->boardroom_id,
                                        'location_id'  => $vb->boardroom->location_id,
                                        'location'     => $vb->boardroom->location->name,
                                        'user'         => $vb->user->name,
                                        'plan'         => 'hourly',
                                        'price'        => $vb->selected_price,
                                        'timeslot'     => $time,
                                        'isOwner'      => $user->id === $vb->user_id, // ✅ flag for frontend
                                    ],
                                ];
                            });
                    });
                }

                return collect();
            });

        return Inertia::render('Calendars/BoardroomsFullCalendar', [
            'events' => $boardrooms->sortBy('start')->values(),
        ]);
    }

    public function dedicated(Request $request)
    {
        $user         = auth()->user();
        $palette      = array_values(config('colors.across'));
        $paletteCount = count($palette);

        $offices = Booking::with([
                'user:id,name',
                'office:id,office_name,location_id',
                'office.location:id,name'
            ])
            ->whereIn('status', ['approved', 'paid'])
            ->get()
            ->map(function ($booking) use ($user, $palette, $paletteCount) {
                $isOwner = $user->id === $booking->user_id;
                $isAdmin = $user->hasRole('admin') || $user->hasRole('super admin');

                $key   = $booking->office->office_name . '-' . $booking->plan . '-' . $booking->office->location_id;
                $index = crc32($key) % $paletteCount;
                $color = $palette[$index];

                return [
                    'start'   => $booking->start_date,
                    'end'     => $booking->end_date,
                    'title'   => $isAdmin ? $booking->user->name : ($isOwner ? $booking->user->name : 'Booked'),
                    'content' => $booking->office->office_name,
                    'class'   => 'plan-' . $booking->plan,
                    'backgroundColor' => $color['bg'],
                    'borderColor'     => $color['border'],
                    'textColor'       => $color['text'],
                    'extendedProps'   => [
                        'plan'        => $booking->plan,
                        'office_id'   => $booking->office_id,
                        'office'      => $booking->office->office_name,
                        'location_id' => $booking->office->location_id,
                        'location'    => optional($booking->office->location)->name
                                         ?? 'Location #' . $booking->office->location_id,
                    ],
                ];
            });

        $events = $offices->sortBy('start')->values();

        return Inertia::render('Calendars/DedicatedCalendar', [
            'events' => $events,
        ]);
    }

    public function closed(Request $request)
    {
        $user         = auth()->user();
        $palette      = array_values(config('colors.across'));
        $paletteCount = count($palette);

        $offices = Booking::with([
                'user:id,name',
                'office:id,office_name,location_id',
                'office.location:id,name'
            ])
            ->whereIn('status', ['approved', 'paid'])
            ->get()
            ->map(function ($booking) use ($user, $palette, $paletteCount) {
                $isOwner = $user->id === $booking->user_id;
                $isAdmin = $user->hasRole('admin') || $user->hasRole('super admin');

                $key   = $booking->office->office_name . '-' . $booking->plan;
                $index = crc32($key) % $paletteCount;
                $color = $palette[$index];

                return [
                    'start'   => $booking->start_date,
                    'end'     => $booking->end_date,
                    'title'   => $isAdmin ? $booking->user->name : ($isOwner ? $booking->user->name : 'Booked'),
                    'class'   => 'plan-' . $booking->plan,
                    'backgroundColor' => $color['bg'],
                    'borderColor'     => $color['border'],
                    'textColor'       => $color['text'],
                    'extendedProps'   => [
                        'plan'        => $booking->plan,
                        'office_id'   => $booking->office_id,
                        'office'      => $booking->office->office_name,
                        'location_id' => $booking->office->location_id,
                        'location'    => optional($booking->office->location)->name,
                    ],
                ];
            });

        $events = $offices->sortBy('start')->values();

        return Inertia::render('Calendars/ClosedFullCalendar', [
            'events' => $events,
        ]);
    }

    public function hotdesk(Request $request)
    {
        $user         = auth()->user();
        $palette      = array_values(config('colors.across'));
        $paletteCount = count($palette);

        $bookings = HotDeskBooking::with([
                'user:id,name',
                'helpdesk:id,help_desk_name,location_id',
                'helpdesk.location:id,name'
            ])
            ->whereIn('status', ['approved', 'paid'])
            ->get()
            ->flatMap(function ($hd) use ($user, $palette, $paletteCount) {
                $dates = is_array($hd->selected_dates)
                    ? $hd->selected_dates
                    : json_decode($hd->selected_dates, true) ?? [];

                $isOwner = $user->id === $hd->user_id;

                $isAdmin = $user->hasRole('admin') || $user->hasRole('super admin');


                return collect($dates)->map(function ($date) use ($hd, $isOwner, $isAdmin, $palette, $paletteCount) {
                    // Deterministic color based on helpdesk + plan + location
                    $key   = ($hd->helpdesk->help_desk_name ?? 'helpdesk') . '-' . $hd->plan . '-' . $hd->helpdesk->location_id;
                    $index = crc32($key) % $paletteCount;
                    $color = $palette[$index];

                    return [
                        'start'   => Carbon::parse($date)->format('Y-m-d'),
                        'end'     => Carbon::parse($date)->format('Y-m-d'),
                        'title'   => $isAdmin ? $hd->user->name : ($isOwner ? $hd->user->name : 'Booked'),
                        'content' => $hd->helpdesk->help_desk_name ?? 'Hotdesk Booking',
                        'class'   => 'plan-' . $hd->plan,
                        'backgroundColor' => $color['bg'],
                        'borderColor'     => $color['border'],
                        'textColor'       => $color['text'],
                        'extendedProps'   => [
                            'plan'        => $hd->plan,
                            'helpdesk_id' => $hd->helpdesk_id,
                            'helpdesk'    => $hd->helpdesk->help_desk_name,
                            'location_id' => $hd->helpdesk->location_id,
                            'location'    => optional($hd->helpdesk->location)->name
                                             ?? 'Location #' . $hd->helpdesk->location_id,
                        ],
                    ];
                });
            });

        $halfDayBookings = HotDeskBooking::with([
                'user:id,name',
                'helpdesk:id,help_desk_name,location_id',
                'helpdesk.location:id,name'
            ])
            ->whereIn('status', ['approved', 'paid'])
            ->where('is_half_day', true)
            ->get()
            ->flatMap(function ($hd) use ($user, $palette, $paletteCount) {
                $isOwner = $user->id === $hd->user_id;
                $isAdmin = $user->is_admin;

                return collect($hd->time_slots)
                    ->map(function ($slot, $date) use ($hd, $isOwner, $isAdmin, $palette, $paletteCount) {
                        $block = $slot['block'] ?? null;

                        $start = match ($block) {
                            'morning'   => $date . ' 08:00:00',
                            'afternoon' => $date . ' 13:00:00',
                            default     => $date . ' 09:00:00',
                        };
                        $end = match ($block) {
                            'morning'   => $date . ' 12:00:00',
                            'afternoon' => $date . ' 17:00:00',
                            default     => $date . ' 17:00:00',
                        };

                        // Deterministic color for half-day plan
                        $key   = ($hd->helpdesk->help_desk_name ?? 'helpdesk') . '-half-day-' . $hd->helpdesk->location_id;
                        $index = crc32($key) % $paletteCount;
                        $color = $palette[$index];

                        return [
                            'start'   => Carbon::parse($start)->format('Y-m-d H:i:s'),
                            'end'     => Carbon::parse($end)->format('Y-m-d H:i:s'),
                            'title'   => $isAdmin ? $hd->user->name : ($isOwner ? $hd->user->name : 'Booked'),
                            'content' => $hd->helpdesk->help_desk_name ?? 'Hotdesk Booking',
                            'class'   => 'plan-half-day',
                            'backgroundColor' => $color['bg'],
                            'borderColor'     => $color['border'],
                            'textColor'       => $color['text'],
                            'extendedProps'   => [
                                'plan'        => 'half day',
                                'helpdesk_id' => $hd->helpdesk_id,
                                'helpdesk'    => $hd->helpdesk->help_desk_name,
                                'location_id' => $hd->helpdesk->location_id,
                                'location'    => optional($hd->helpdesk->location)->name
                                                 ?? 'Location #' . $hd->helpdesk->location_id,
                            ],
                        ];
                    })->values();
            });

        $events = collect()
            ->merge($bookings)
            ->merge($halfDayBookings)
            ->sortBy('start')
            ->values();

        return Inertia::render('Calendars/HotDeskCalendar', [
            'events' => $events,
        ]);
    }

    public function virtual(Request $request)
    {
        $palette      = array_values(config('colors.across')); // single source of truth
        $paletteCount = count($palette);

        $virtuals = VirtualBooking::with([
                'user:id,name',
                'virtualOffice:id,virtualoffice_name,location_id',
                'virtualOffice.location:id,name'
            ])
            ->whereIn('status', ['approved', 'paid'])
            ->get()
            ->flatMap(function ($vb) use ($palette, $paletteCount) {
                $dates = is_array($vb->selected_dates)
                    ? $vb->selected_dates
                    : json_decode($vb->selected_dates, true) ?? [];

                return collect($dates)->map(function ($date) use ($vb, $palette, $paletteCount) {
                    // Deterministic color assignment based on virtualOffice + plan + location
                    $key   = ($vb->virtualOffice->virtualoffice_name ?? 'virtual') . '-' . $vb->plan . '-' . $vb->virtualOffice->location_id;
                    $index = crc32($key) % $paletteCount;
                    $color = $palette[$index];

                    return [
                        'start'   => Carbon::parse($date)->format('Y-m-d'),
                        'end'     => Carbon::parse($date)->format('Y-m-d'),
                        'title'   => $vb->user->name,
                        'content' => $vb->virtualOffice->virtualoffice_name ?? 'Virtual Booking',
                        'class'   => 'plan-' . $vb->plan, // plan is the decider (premium or standard)
                        'backgroundColor' => $color['bg'],
                        'borderColor'     => $color['border'],
                        'textColor'       => $color['text'],
                        'extendedProps'   => [
                            'plan'              => $vb->plan,
                            'virtual_office_id' => $vb->virtual_office_id,
                            'virtual_office'    => $vb->virtualOffice->virtualoffice_name,
                            'location_id'       => $vb->virtualOffice->location_id,
                            'location' => optional($vb->virtualOffice->location)->name
                                                   ?? 'Location #' . $vb->virtualOffice->location_id,
                        ],
                    ];
                });
            });

        $events = collect($virtuals)
            ->sortBy('start')
            ->values();

        return Inertia::render('Calendars/VirtualCalendar', [
            'events' => $events,
        ]);
    }


}
