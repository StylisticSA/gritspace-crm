<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Amenity;
use App\Models\Location;
use App\Models\Boardroom;
use Illuminate\Http\Request;

class BoardroomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Boardroom::class);

        $search = $request->input('search');

        $boardrooms = Boardroom::when($search, function ($query, $search) {
            if (!empty($search)) {
                $query->where('boardroom_name', 'like', "%{$search}%");
            }
        })
            ->with('location', 'amenities')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Boardrooms/AdminBoardrooms', [
            'boardrooms' => $boardrooms,
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
        $this->authorize('create', Boardroom::class);

        $locations = Location::select('id', 'name')->get();
        $amenities = Amenity::select('id', 'amenity_name')->get();

        return Inertia::render('Boardrooms/CreateBoardroom', [
            'locations' => $locations,
            'amenities' => $amenities
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $this->authorize('create', Boardroom::class);

        $validated = $request->validate([
            'boardroom_name'        => 'required|string|max:255',
            'location_id'           => 'required',
            'seats'                 => 'required|numeric',
            'hourly_price'          => 'required|numeric|min:0',
            'daily_price'           => 'required|numeric|min:0',
            // 'amenities'             => ['array'],
            // 'amenities.*'           => ['exists:amenities,id'],

            'is_available'    => ['nullable'],
            'available_dates' => ['nullable'],

        ]);


        $amenities = $request->input('amenities', []);

        $validated['is_available'] = false;
        $validated['available_dates'] = null;
        // dd($validated);

        $boardrooms = Boardroom::create($validated);


        if (!empty($amenities)) {
            $boardrooms->amenities()->sync($amenities);
        }


        return redirect()->route('admin.boardrooms')->with('success', 'A boardroom has been created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Boardroom $boardroom)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $boardroom = Boardroom::findOrFail($id);

        $this->authorize('update', $boardroom);

        $locations = Location::select('id', 'name')->get();
        $amenities = Amenity::select('id', 'amenity_name')->get();
        $boardrooms = $boardroom->load(['amenities']);

        return Inertia::render('Boardrooms/EditBoardroom', [
            'boardrooms' => $boardrooms,
            'locations' => $locations,
            'amenities' => $amenities
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Boardroom $boardroom)
    {
        $this->authorize('update', $boardroom);

        $validated = $request->validate([
            'boardroom_name'        => 'required|string|max:255',
            'location_id'           => 'required',
            'seats'                 => 'required|numeric',
            'hourly_price'          => 'required|numeric|min:0',
            'daily_price'           => 'required|numeric|min:0',
            // 'amenities'             => ['array'],
            // 'amenities.*'           => ['exists:amenities,id'],

        ]);


        $amenities = $request->input('amenities', []);


        $boardroom->update($validated);

        if (!empty($amenities)) {
            $boardroom->amenities()->sync($amenities);
        }

        return redirect()->route('admin.boardrooms')->with('success', 'Office Rates updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Boardroom $boardroom)
    {
        $this->authorize('delete', $boardroom);

        $boardroom->delete();

        return redirect()->route('admin.boardrooms')->with('success', 'A Boardroom has been deleted successfully.');
    }

    public function availability(Request $request, Boardroom $boardroom)
    {
        
        $validated = $request->validate([
              'is_available' => ['required', 'boolean'],
              'available_dates' => ['nullable', 'date'],
          ]);

        $boardroom->update($validated);

        return back()->with('success', 'Availability updated successfully.');

    }
}
