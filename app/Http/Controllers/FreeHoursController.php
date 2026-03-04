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
                        ->where('status','in_progress')
                        ->sum('hours_used');

        $newTotal = $hoursUsedTotal + (int)$validated['hours_used'];


        if ($newTotal > 15) {
            return back()->withErrors([
                'hours_used' => 'User cannot exceed 15 free hours. Currently used: ' . $hoursUsedTotal,
            ]);
        }

        $freeHours = FreeHours::create($validated);

        if ($newTotal === 15) {
            $freeHours->update([
                'status'    => 'closed',
                'closed_at' => now(),
            ]);
        } else {
            $freeHours->update([
                'status' => 'in_progress',
            ]);
        }

        return redirect()->back()->with('success', 'Boardroom Hours updated successfully');
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FreeHours $freeHours)
    {
        //
    }

    public function search(Request $request)
    {
        $userId = $request->input('user_id');

        $hours = FreeHours::with(['user', 'boardroom', 'user.bookings'])
                ->where('user_id', $userId)
                ->where('status', 'in_progress')
                ->get();

        $hoursUsedTotal = FreeHours::where('user_id', $userId)
                ->where('status', 'in_progress')
                ->sum('hours_used');

        $remainingHours = 15 - $hoursUsedTotal;

        $bookings = Booking::with(['office','office.location', 'category'])
                ->whereHas('category', function ($query) {
                    $query->whereRaw("LOWER(name) IN ('closed office', 'closed offices')");
                })
                ->where('user_id', $userId)
                ->where('status', 'paid')
                ->get();

        $boardrooms = Boardroom::with('location')
                    ->select('id', 'location_id','boardroom_name')->get();

        return response()->json([
            'hours' => $hours,
            'boardrooms' => $boardrooms,
            'bookings' => $bookings,
            'hours_used' => $hoursUsedTotal,
            'remaining_hours' => $remainingHours
        ]);

    }
}
