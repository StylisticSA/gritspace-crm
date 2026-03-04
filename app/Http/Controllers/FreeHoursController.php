<?php

namespace App\Http\Controllers;

use App\Models\Boardroom;
use App\Models\Booking;
use App\Models\FreeHours;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FreeHoursController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

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
            'hours_used'   => ['required', 'numeric', 'min:1'],
            'status'       => ['nullable', 'in:in_progress,closed,none'],
            'start_at'     => ['required_if:status,in_progress', 'nullable', 'date'],
            'closed_at'    => ['required_if:status,closed', 'nullable', 'date'],
        ]);

        $hoursUsedTotal = FreeHours::where('user_id', $validated['user_id'])
                        ->whereIn('status',['in_progress','closed'])
                        ->sum('hours_used');

        $newTotalCurrent = $hoursUsedTotal + $validated['hours_used'];
        

        if ($hoursUsedTotal >= 15 || $newTotalCurrent > 15) {
            return back()->withErrors([
                'hours_used' => 'User cannot exceed 15 free hours. Currently used: ' . $hoursUsedTotal,
            ]);
        }

      
        $sumTotal = FreeHours::where('user_id', $validated['user_id'])
                    ->sum('hours_used');
                     
        if ($sumTotal == 15) {
            return back()->withErrors([
                'hours_used' => 'Sorry, this user has no free boardroom hours.'
            ]);
        }

        $newRecord = FreeHours::create($validated);


        return redirect()->back()->with('success', 'Boardroom Hours Saved successfully');
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
        // dd($request);

        $request->validate([
            'ids' => 'required|array',
            'status' => 'required'
        ]);

        if ($request->status === 'closed') {
            $alreadyClosed = FreeHours::whereIn('id', $request->ids)
                            ->where('status', 'closed')
                            ->exists();

            if ($alreadyClosed) {
                return back()->withErrors([
                    'status' => 'One or more selected records are already closed and cannot be modified.'
                ]);
            }
        }

        if ($request->has('ids')) {
            FreeHours::whereIn('id', $request->ids)
            ->where('status', 'in_progress')
            ->update([
                'status'    => $request->status, 
                'closed_at' => $request->status === 'closed' ? now()->today() : null,
            ]);
        }

        $updatedCount = FreeHours::whereIn('id', $request->ids)
            ->where('status', 'in_progress')
            ->update([
                'status'    => $request->status,
                'closed_at' => $request->status === 'closed' ? now()->today() : null,
            ]);

        // if ($updatedCount === 0) {
        //     return back()->withErrors(['status' => 'No active records were found to update.']);
        // }

        return redirect()->back()->with('success', 'Boardroom Hours updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FreeHours $freeHours)
    {
        //
    }

    public function searchProgress(Request $request)
    {
        $userId = $request->input('user_id');

        $hours = FreeHours::with(['user', 'boardroom', 'user.bookings'])
                ->where('user_id', $userId)
                ->where('status', 'in_progress')
                ->get();

        $hoursUsedTotal = FreeHours::where('user_id', $userId)
                ->where('status', 'in_progress')
                ->whereRelation('boardroom', 'id', $request->boardroom_id) 
                ->sum('hours_used');

        $sumTotal = FreeHours::where('user_id', $userId)
                  ->sum('hours_used');

        $remainingHours = 15 - (int)$hoursUsedTotal;

        $bookings = Booking::with(['office','office.location', 'category'])
                ->whereHas('category', function ($query) {
                    $query->whereRaw("LOWER(name) IN ('closed office', 'closed offices')");
                })
                ->where('user_id', $userId)
                ->where('status', 'paid')
                ->get();

        $boardrooms = Boardroom::with(['location', 'BoardroomBookings' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])->whereHas('BoardroomBookings', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();

        return response()->json([
            'hours' => $hours,
            'boardrooms' => $boardrooms,
            'bookings' => $bookings,
            'hours_used' => $hoursUsedTotal,
            'remaining_hours' => $remainingHours,
            'sum_hours_used' => $sumTotal
        ]);

    }

    public function searchClosed(Request $request)
    {
        $userId = $request->input('user_id');

        $allHours = FreeHours::with(['user', 'boardroom', 'user.bookings'])
                    ->where('user_id', $userId)
                    ->whereIn('status', ['in_progress', 'closed'])
                    ->whereDate('created_at', now()->today())
                    ->get();

        $hours = $allHours->where('status', 'in_progress')->values();
        $hoursClosed = $allHours->where('status', 'closed')->values();
            

        $hoursUsedTotal = FreeHours::where('user_id', $userId)
                ->where('status', 'in_progress')
                ->sum('hours_used');

        $sumTotal = FreeHours::where('user_id', $userId)
                    ->where('status', 'closed')
                  ->sum('hours_used');

        $remainingHours = 15 - (int)$hoursUsedTotal;

        $bookings = Booking::with(['office','office.location', 'category'])
                ->whereHas('category', function ($query) {
                    $query->whereRaw("LOWER(name) IN ('closed office', 'closed offices')");
                })
                ->where('user_id', $userId)
                ->where('status', 'paid')
                ->get();

        $boardrooms = Boardroom::with(['location', 'BoardroomBookings' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])->whereHas('BoardroomBookings', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();

        return response()->json([
            'hours' => $hours,
            'boardrooms' => $boardrooms,
            'bookings' => $bookings,
            'hours_used' => $hoursUsedTotal,
            'sum_hours_used' => $sumTotal,
            'remaining_hours' => $remainingHours,
            'hours_closed' => $hoursClosed
        ]);

    }
}
