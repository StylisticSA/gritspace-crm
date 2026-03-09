<?php

namespace App\Http\Controllers;

use App\Models\AgrementUpload;
use App\Models\Boardroom;
use App\Models\BoardroomBooking;
use App\Models\Booking;
use App\Models\ClientInformation;
use App\Models\ClientRate;
use App\Models\DailyUsage;
use App\Models\FreeHours;
use App\Models\HotDeskBooking;
use App\Models\Invoice;
use App\Models\Location;
use App\Models\Note;
use App\Models\Printing;
use App\Models\User;
use App\Models\VirtualBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $admins = auth()->user()->hasRole(['Super Admin','Admin']);

        $users = User::select('id', 'name')->get();

        $locations = Location::select('id', 'name')->get();

        $agreement = AgrementUpload::select('id', 'user_id', 'agreement', 'status')
                    ->where('user_id', Auth()->id())
                    ->where('status', 'pending')
                    ->get();

        $client = ClientInformation::with('location', 'user')
                        ->where('user_id', auth()->id())
                        ->where('approved', 1)
                        ->first();

        $clientEmpty = ClientInformation::where('user_id', auth()->id())->exists();
        $agreementEmpty = AgrementUpload::where('user_id', auth()->id())->exists();

        // Plan User is on
        $closedOfficePlan = Booking::with(['office.location', 'category'])
                            ->where('user_id', auth()->id())
                              ->whereIn('status', ['approved', 'paid'])
                            ->whereHas('category', function ($query) {
                                $query->whereRaw("LOWER(name) IN ('closed office', 'closed offices')");
                            })
                            ->get();


        $dedicatedDeskPlan = Booking::with(['office.location', 'category'])
            ->where('user_id', auth()->id())
            ->whereIn('status', ['approved', 'paid'])
            ->whereHas('category', function ($query) {
                $query->whereRaw("LOWER(name) IN ('dedicated desk', 'dedicated desks')");
            })
            ->get();



        $boardroomPlan = BoardroomBooking::with('boardroom')
                        ->where('user_id', auth()->id())
                        ->whereIn('status', ['approved', 'paid'])
                        ->get();
  

        $hotDeskPlan = HotDeskBooking::with('HelpDesk.location')
                    ->where('user_id', auth()->id())
                     ->whereIn('status', ['approved', 'paid'])
                    ->get();



        $virtualPlan = VirtualBooking::with('virtualOffice.location')
                    ->where('user_id', auth()->id())
                    ->whereIn('status', ['approved', 'paid'])
                    ->get();


        // Counters for Coffee/Printing
        $startMonthDate = Carbon::now()->subMonth()->day(23)->startOfDay();
        $endMonthDate = Carbon::now()->endOfDay();


        $today = Carbon::today()->toDateString();

        // coffee
        $coffeeMonthly = DailyUsage::where('user_id', auth()->id())
                    ->where('type', 'coffee')
                    ->whereBetween('date', [$startMonthDate, $endMonthDate])
                    ->sum('amount');

        $coffeeToday = DailyUsage::where('user_id', auth()->id())
                                ->where('type', 'coffee')
                                ->where('date', $today)
                                ->sum('amount');

        // printing
        $printColorTotal = Printing::where('user_id', auth()->id())
                         ->where('type', 'printing')
                         ->whereBetween('date', [$startMonthDate, $endMonthDate])
                         ->sum('color_amount');


        $printBlackTotal = Printing::where('user_id', auth()->id())
                                ->where('type', 'printing')
                                ->whereBetween('date', [$startMonthDate, $endMonthDate])
                                ->sum('black_amount');


        $printColor = Printing::where('user_id', auth()->id())
                        ->where('type', 'printing')
                        ->where('date', $today)
                        ->sum('color_amount');

        $printBlack = Printing::where('user_id', auth()->id())
                             ->where('type', 'printing')
                             ->where('date', $today)
                             ->sum('black_amount');



        $notes = Note::where('user_id', Auth::id())
                    ->where('is_visible_to_user', 1)
                    ->latest('created_at')
                    ->take(6)
                    ->get()
                    ->reverse()
                    ->values();

        $clientRatePlan = ClientRate::where('user_id', auth()->id())->get();

        $invoices = Invoice::with('user:id,name')
                    ->select('id', 'user_id', 'invoice_number','issued_due_date','status')
                    ->where('user_id', auth()->id())
                    ->whereIn('status', ['pending', 'paid'])
                    ->limit(6)
                    ->get();

     
        return Inertia::render('Dashboard', [
            'notes'                 => $notes,
            'users'                 => $users,
            'user'                  => $client->user_id ?? '',
            'coffee'                => $coffeeToday,
            'printColor'            => $printColor,
            'printBlack'            => $printBlack,
            'coffeeMonthly'         => $coffeeMonthly,
            'printBlackTotal'       => $printBlackTotal,
            'printColorTotal'       => $printColorTotal,
            'location'              => $client->location_id ?? '',
            'boardroomPlan'         => $boardroomPlan,
            'hotDeskPlan'           => $hotDeskPlan,
            'closedOfficePlan'      => $closedOfficePlan,
            'dedicatedOfficePlan'   => $dedicatedDeskPlan,
            'clientRatePlan'        => $clientRatePlan,
            'virtualPlan'           => $virtualPlan,
            'locations'             => $locations,
            'agreement'             => $agreement,
            'clientAvail'           => $clientEmpty,
            'agreementAvail'        => $agreementEmpty,
            'invoices'              => $invoices,
        ]);

    }

    public function admin()
    {

        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['user', 'User']);
        })->with('roles')
        ->select('id', 'name')
        ->get();


        $locations = Location::select('id', 'name')->get();

        // Approved office bookings → group by category → attach to location
        $approvedBookings = Booking::query()
                ->with(['office.category', 'office.location'])
                ->whereIn('status', ['approved', 'paid'])
                ->whereHas('office') 
                ->get();

  

        // Group by location → then by category name
        $groupedByLocation = $approvedBookings
            ->filter(fn ($booking) => $booking->office && $booking->office->category && $booking->office->location)
            ->groupBy(fn ($booking) => $booking->office->location->id)
            ->map(function ($bookings) {
                return $bookings->groupBy(fn ($booking) => $booking->office->category->name)
                    ->map(function ($group) {
                        $first = $group->first();
                        return [
                            'category_name' => $first->office->category->name,
                            'count' => $group->count(),
                        ];
                    })->keyBy('category_name');
            });



        // Boardroom bookings → boardroom → location
        $boardroomCounts = BoardroomBooking::with('boardroom.location')
            ->whereIn('status', ['approved', 'paid'])
            ->whereHas('boardroom') 
            ->get()
            ->groupBy(fn ($b) => $b->boardroom->location_id)
            ->map(fn ($group) => $group->count());

        // Hotdesk bookings → helpdesk → location
        $hotdeskCounts = HotDeskBooking::with('helpdesk.location')
            ->whereIn('status', ['approved', 'paid'])
            ->whereHas('helpdesk') 
            ->get()
            ->groupBy(fn ($h) => $h->helpdesk->location_id)
            ->map(fn ($group) => $group->count());

        // Virtual bookings → virtualOffice → location
        $virtualCounts = VirtualBooking::with('virtualOffice.location')
            ->whereIn('status', ['approved', 'paid'])
            ->whereHas('virtualOffice') 
            ->get()
            ->groupBy(fn ($v) => $v->virtualOffice->location_id)
            ->map(fn ($group) => $group->count());

        // Merge into location structure
        $locationsWithStats = $locations->map(function ($location) use (
            $groupedByLocation,
            $boardroomCounts,
            $hotdeskCounts,
            $virtualCounts
        ) {
            $id = $location->id;
            $categoryCounts = $groupedByLocation[$id] ?? collect();

            return [
                'id' => $id,
                'name' => $location->name,
                'closedCount' => $categoryCounts['Closed Offices']['count'] ?? 0,
                'dedicatedCount' => $categoryCounts['Dedicated Desks']['count'] ?? 0,
                'boardroomCount' => $boardroomCounts[$id] ?? 0,
                'hotdeskCount' => $hotdeskCounts[$id] ?? 0,
                'virtualCount' => $virtualCounts[$id] ?? 0,
            ];
        });



        $client = ClientInformation::with('location:id,name', 'user')
                            ->select('id', 'user_id', 'location_id')
                             ->where('approved', 1)
                             ->first();


        // Counters for Coffee/Printing
        $startMonthDate = Carbon::now()->subMonth()->day(23)->startOfDay();
        $endMonthDate = Carbon::now()->endOfDay();

        $today = Carbon::today()->toDateString();

        $coffeeMonthly = DailyUsage::where('type', 'coffee')
                        ->whereBetween('date', [$startMonthDate, $endMonthDate])
                        ->sum('amount');

        $coffeeToday = DailyUsage::where('type', 'coffee')
                        ->where('date', $today)
                        ->sum('amount');


        // printing
        $printToday = DailyUsage::where('type', 'printing')
                    ->where('date', $today)
                    ->sum('amount');

        $printColorTotal = Printing::where('type', 'printing')
                         ->whereBetween('date', [$startMonthDate, $endMonthDate])
                         ->sum('color_amount');


        $printBlackTotal = Printing::where('type', 'printing')
                                ->whereBetween('date', [$startMonthDate, $endMonthDate])
                                ->sum('black_amount');


        $printColor = Printing::where('type', 'printing')
                        ->where('date', $today)
                        ->sum('color_amount');

        $printBlack = Printing::where('type', 'printing')
                             ->where('date', $today)
                             ->sum('black_amount');



        $notes = Note::with('user')->latest('created_at')->take(4)->get()->reverse()->values();

        $today = Carbon::today();

        $invoiceCounts = Invoice::where('issued_due_date', '>=', $today)
                            ->select('status', DB::raw('COUNT(*) as total'))
                            ->groupBy('status')
                            ->pluck('total', 'status');

        
        $statusCounts = DB::table('free_hours')
                        ->where('status', 'in_progress')
                        ->whereDate('created_at', Carbon::today())
                        ->groupBy('status')
                        ->select('status', DB::raw('COUNT(DISTINCT boardroom_id) as total'))
                        ->pluck('total', 'status');

        $statusBoardCount = DB::table('boardroom_hours')
                        ->where('status', 'in_progress')
                        ->whereDate('created_at', Carbon::today())
                        ->distinct('boardroom_id')
                        ->count('boardroom_id');


        $boardrooms = Boardroom::with('location')
                    ->orderBy('boardroom_name')
                    ->get(['id', 'boardroom_name', 'location_id']);


        return Inertia::render('Admin/AdminDashboard', [
            'notes'                 => $notes,
            'users'                 => $users,
            'user'                  => $client->user_id ?? '',
            'location'              => $client->location_id ?? '',
            'locations'             => $locations,

            'coffee'                => $coffeeToday,
            'printing'              => $printToday,

            'coffeeMonthly'         => $coffeeMonthly,

            'printColor'            => $printColor,
            'printBlack'            => $printBlack,
            'printBlackTotal'       => $printBlackTotal,
            'printColorTotal'       => $printColorTotal,

            'locationsWithStats'    => $locationsWithStats,
            'invoiceCounts'         => $invoiceCounts,

            'inProgressCount'       => $statusCounts['in_progress'] ?? 0,
            'normalCount'           => $statusBoardCount,
            'boardrooms'            => $boardrooms,

        ]);

    }
}
