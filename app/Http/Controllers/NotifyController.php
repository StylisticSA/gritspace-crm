<?php

namespace App\Http\Controllers;

use App\Models\BoardroomBooking;
use App\Models\Booking;
use App\Models\HotDeskBooking;
use App\Models\VirtualBooking;
use Illuminate\Http\Request;

class NotifyController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notificationsSummary = ($user && !$user->hasRole('Admin') && !$user->hasRole('Super Admin')) ? [
            'closed'     => (int) Booking::whereHas('category', fn($q) =>
                                $q->whereRaw("LOWER(name) IN ('closed office','closed offices')")
                            )->where('user_id', $user->id)
                            ->where('status', 'approved')
                            ->count(),
            'dedicated'  => (int) Booking::whereHas('category', fn($q) =>
                                $q->whereRaw("LOWER(name) IN ('dedicated office','dedicated offices')")
                            )->where('user_id', $user->id)
                            ->where('status', 'approved')
                            ->count(),
            'boardroom'  => (int) BoardroomBooking::where('user_id', $user->id)
                            ->where('status', 'approved')
                            ->count(),
            'hotdesk'    => (int) HotDeskBooking::where('user_id', $user->id)
                            ->where('status', 'approved')
                            ->count(),
            'virtual'    => (int) VirtualBooking::where('user_id', $user->id)
                            ->where('status', 'approved')
                            ->count(),
        ] : [];

        $adminSummary = ($user && ($user->hasRole('Admin') || $user->hasRole('Super Admin'))) ? [
            'boardroom'  => (int) BoardroomBooking::where('status', 'pending')->count(),
            'hotdesk'    => (int) HotDeskBooking::where('status', 'pending')->count(),
            'virtual'    => (int) VirtualBooking::where('status', 'pending')->count(),
            'closed'     => (int) Booking::whereHas('category', fn($q) =>
                                $q->whereRaw("LOWER(name) IN ('closed office','closed offices')")
                            )->where('status','pending')->count(),
            'dedicated'  => (int) Booking::whereHas('category', fn($q) =>
                                $q->whereRaw("LOWER(name) IN ('dedicated office','dedicated offices')")
                            )->where('status','pending')->count(),
        ] : [];

        // dd($adminSummary);

        return response()->json([
            'notificationsSummary' => $notificationsSummary,
            'notificationsTotal'   => !empty($notificationsSummary) ? array_sum($notificationsSummary) : 0,
            'adminSummary'         => $adminSummary,
            'adminTotal'           => !empty($adminSummary) ? array_sum($adminSummary) : 0,
        ]);

    }

    
}

