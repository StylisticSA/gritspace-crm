<?php

namespace App\Http\Controllers;

use App\Models\Boardroom;
use App\Models\Booking;
use App\Models\FreeHours;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FreeHoursController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['user', 'User']);
                })->with('roles')
                ->select('id', 'name')
                ->get();

        $hours = FreeHours::with(['user', 'boardroom'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Hours/HoursIndex', [
            'hours' => $hours,
            'users' => $users,
            'filters' => [
                'search' => $search,
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'      => ['required', 'exists:users,id'],
            'boardroom_id' => ['required', 'exists:boardrooms,id'],
            'office_id'    => ['required', 'exists:offices,id'],
            'hours_used'   => ['required', 'numeric', 'min:1'],
           
        ]);

        $hoursUsedTotal = FreeHours::where('user_id', $validated['user_id'])
                        ->where('office_id', $validated['office_id'])
                        ->whereIn('status', ['in_progress', 'closed'])
                        ->sum('hours_used');

        $newTotalCurrent = $hoursUsedTotal + $validated['hours_used'];

        if ($hoursUsedTotal >= 15 || $newTotalCurrent > 15) {
            return back()->withErrors([
                'hours_used' => 'User cannot exceed 15 free hours for this office. Currently used: ' . $hoursUsedTotal,
            ]);
        }

        $sumTotal = FreeHours::where('user_id', $validated['user_id'])
                    ->where('office_id', $validated['office_id'])
                    ->sum('hours_used');

        if ($sumTotal == 15) {
            return back()->withErrors([
                'hours_used' => 'Sorry, this user has no free hours left for this office.',
            ]);
        }

        if (empty($validated['status'])){
            $validated['status'] = "in_progress";
        }

        if (empty($validated['start_at'])) {
            $validated['start_at'] = Carbon::now('Africa/Johannesburg');
        }


        FreeHours::create($validated);

        return redirect()->back()->with('success', 'Boardroom Free Hours Saved successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(FreeHours $freeHours)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FreeHours $freeHours)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FreeHours $freeHours)
    {
       
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FreeHours $freeHours)
    {
        //
    }

    /**
     * Calculate progress for a user.
     */
    public function searchProgress(Request $request)
    {
        $userId = $request->input('user_id');

        $hours = FreeHours::with(['user', 'boardroom','office'])
                ->where('user_id', $userId)
                ->where('status', 'in_progress')
                ->get();

                
        $booked = Booking::with(['office', 'office.location', 'category'])
                ->where('user_id', $userId)
                ->whereHas('category', function ($query) {
                    $query->whereIn(DB::raw('LOWER(name)'), [
                        'closed office',
                        'closed offices',
                        'dedicated desk',
                        'dedicated desks',
                    ]);
                })
                ->whereIn('plan', ['monthly', 'premium'])
                ->where('status', 'paid')
                ->get();

        $boardrooms = Boardroom::with('location')
                    ->orderBy('boardroom_name')
                    ->get(['id', 'boardroom_name', 'location_id']);
  

        $hoursByOffice = $hours->groupBy('office_id')->map(function ($records) {
            $hoursUsed = $records->sum('hours_used');
            return [
                'hours_used' => $hoursUsed,
                'remaining_hours' => 15 - $hoursUsed
            ];
        });

        return response()->json([
            'hours' => $hours,
            'booked' => $booked,
            'boardrooms' => $boardrooms,
            'hours_by_office' => $hoursByOffice,
        ]);
    }

 
    /**
     * update the hours.
     */
    public function searchClosed(Request $request)
    {
        
        // $boardrooms = FreeHours::with(['user', 'boardroom'])
        //             ->where('status', 'in_progress')
        //             ->whereDate('created_at', now()->today())
        //             ->get();

        $boardrooms = FreeHours::query()->with([
                        'user:id,name', 
                        'boardroom:id,boardroom_name'
                    ])->where('status', 'in_progress')
                    ->whereDate('created_at', today())
                    ->select('id', 'user_id', 'boardroom_id', 'status', 'created_at')
                    ->get();

        return response()->json([
            'boardrooms' => $boardrooms,
        ]);

    }

    public function freeClose(Request $request, FreeHours $freeHours)
    {
        // dd($freeHours);
        $validated = $request->validate([
              'status' => ['nullable'],
              'closed_at' => ['nullable'],
              'user_closed' => ['nullable']
          
        ]);

        if(empty($validated['user_closed'])){
            $validated['user_closed'] = auth()->id();
        }

        if(empty($validated['status'])){
            $validated['status'] = "closed";
        }

        if (empty($validated['closed_at'])) {
            $validated['closed_at'] = Carbon::now('Africa/Johannesburg');
        }

        // dd($validated);

        $freeHours->update($validated);

        return back()->with('success', 'Free Boardroom has been Closed successfully.');
        
    }
}
