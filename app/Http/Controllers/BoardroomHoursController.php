<?php

namespace App\Http\Controllers;

use App\Models\Boardroom;
use App\Models\BoardroomHours;
use App\Models\Booking;
use App\Models\HotDeskBooking;
use App\Models\User;
use App\Models\VirtualBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BoardroomHoursController extends Controller
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

        $boardhours = BoardroomHours::with(['user', 'boardroom'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('BoardroomHours/BoardIndex', [
            'boardhours' => $boardhours,
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
            'user_type'       => ['required', 'in:existing,in_office'],
            'user_id'         => ['nullable', 'required_if:user_type,existing', 'exists:users,id'],
            'user_in_office'  => ['nullable', 'required_if:user_type,in_office', 'string', 'max:255'],
            'boardroom_id'    => ['required', 'exists:boardrooms,id'],
            'hours_used'      => ['required', 'integer', 'min:1'],

            'start_at'        => ['nullable', 'date'],
            'closed_at'       => ['nullable', 'date'],
        ]);

        // dd($request);

        if(empty($validated['user_id'])){
            $validated['user_id'] = auth()->id();
        }

        if(empty(empty($validated['status']))){
            $validated['status'] = "in_progress";
        }

        if (empty($validated['start_at'])) {
            $validated['start_at'] = Carbon::now('Africa/Johannesburg');
        }


        $boardroomHour = BoardroomHours::create($validated);

        return redirect()->back()->with('success', 'Boardroom Hours Saved successfully');

    }

    /**
     * Display the specified resource.
     */
    public function show(BoardroomHours $boardroomHours)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BoardroomHours $boardroomHours)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BoardroomHours $boardroomHours)
    {
        $validated = $request->validate([
            'user_type'       => ['nullable', 'in:existing,in_office'],
            'user_id'         => ['required', 'required_if:user_type,existing', 'exists:users,id'],
            'user_in_office'  => ['nullable', 'required_if:user_type,in_office', 'string', 'max:255'],
            'boardroom_id'    => ['required', 'exists:boardrooms,id'],
            'hours_used'      => ['required', 'integer', 'min:1'],
            'status'          => ['nullable', 'in:in_progress,closed,none'],
            'start_at'        => ['nullable', 'date'],
            'closed_at'       => ['nullable', 'date'],
        ]);

        $boardroomHours->update($validated);

        return redirect()->back()->with('success', 'Boardroom Hours Updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BoardroomHours $boardroomHours)
    {
        //
    }

    
    /**
     * Calculate progress for a user.
     */
    public function searchProgress(Request $request)
    {
        $userId = $request->input('user_id');

        $hours = BoardroomHours::with(['user', 'boardroom','office'])
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
                ->whereIn('plan', ['daily', 'standard'])
                ->where('status', 'paid')
                ->get();

        $hotdesks = HotDeskBooking::with(['helpdesk', 'user'])
                ->where('user_id', $userId)
                ->where('status', 'paid')
                ->get();

        $virtuals = VirtualBooking::with(['virtualOffice', 'user'])
                ->where('user_id', $userId)
                ->whereIn('plan', ['standard', 'premium'])
                ->where('status', 'paid')
                ->get();

        $boardrooms = Boardroom::with('location')
                    ->orderBy('boardroom_name')
                    ->get(['id', 'boardroom_name', 'location_id']);
  

        return response()->json([
            'hours'         => $hours,
            'booked'        => $booked,
            'boardrooms'    => $boardrooms,
            'virtuals'      => $virtuals,
            'hotdesks'      => $hotdesks
            
        ]);
    }

 
    /**
     * update the hours.
     */
    public function searchClosed(Request $request)
    {

        $boardrooms = BoardroomHours::query()->with([
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

    public function normalClose(Request $request, BoardroomHours $boardroomHours)
    {
        // dd($boardroomHours);
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

        $boardroomHours->update($validated);

        return back()->with('success', 'Boardroom has been Closed successfully.');
        

    }
}
