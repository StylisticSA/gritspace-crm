<?php

namespace App\Http\Middleware;

use App\Models\Booking;
use Inertia\Middleware;
use Illuminate\Http\Request;
use App\Models\HotDeskBooking;
use App\Models\VirtualBooking;
use App\Models\BoardroomBooking;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $notificationsSummary = $user ? [

            'closed'     => Booking::whereHas('category', fn($q) =>
                                $q->whereRaw("LOWER(name) IN ('closed office','closed offices')")
                            )->where('user_id', $user->id)
                            ->where('status', 'approved')
                            ->count(),

            'dedicated'  => Booking::whereHas('category', fn($q) =>
                                $q->whereRaw("LOWER(name) IN ('dedicated office','dedicated offices')")
                            )->where('user_id', $user->id)
                            ->where('status', 'approved')
                            ->count(),

            'boardroom'  => BoardroomBooking::where('user_id', $user->id)
                                            ->where('status', 'approved')
                                            ->count(),

            'hotdesk'    => HotDeskBooking::where('user_id', $user->id)
                                        ->where('status', 'approved')
                                        ->count(),

            'virtual'    => VirtualBooking::where('user_id', $user->id)
                                        ->where('status', 'approved')
                                        ->count(),

            
        ] : [];

        $adminSummary = ($user && ($user->hasRole('Admin') || $user->hasRole('Super Admin'))) ? [
            'boardroom'  => BoardroomBooking::where('status', 'pending')->count(),
            'hotdesk'    => HotDeskBooking::where('status', 'pending')->count(),
            'virtual'    => VirtualBooking::where('status', 'pending')->count(),
            'closed'     => Booking::whereHas('category', fn($q) =>
                                $q->whereRaw("LOWER(name) IN ('closed office','closed offices')")
                            )->where('status','pending')
                            ->count(),
            'dedicated'  => Booking::whereHas('category', fn($q) =>
                                $q->whereRaw("LOWER(name) IN ('dedicated office','dedicated offices')")
                            )->where('status','pending')
                            ->count(),
        ] : [];

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user,
            ],
            'can' => $user ? $user->roles
                ->load('permissions')
                ->flatMap->permissions
                ->pluck('name')
                ->merge($user->permissions->pluck('name'))
                ->unique()
                ->mapWithKeys(fn ($name) => [strtolower(trim($name)) => true])
            : [],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'notificationsSummary' => $notificationsSummary,
            'notificationsTotal'   => array_sum($notificationsSummary),   
            'adminSummary'         => $adminSummary,
            'adminTotal'           => array_sum($adminSummary),       
        ]);
    }
}
