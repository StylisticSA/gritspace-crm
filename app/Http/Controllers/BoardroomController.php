<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\Boardroom;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

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
            'boardroom_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('boardrooms')
                    ->where(function ($query) use ($request) {
                        return $query->where('location_id', $request->location_id);
                    }),
            ],
            'location_id'     => 'required|exists:locations,id',
            'seats'           => 'required|numeric',
            'hourly_price'    => 'required|numeric|min:0',
            'daily_price'     => 'required|numeric|min:0',
            'is_available'    => ['nullable'],
            'available_dates' => ['nullable'],
        ], [
            'boardroom_name.unique' => 'A boardroom with this name already exists at the selected location.',
        ]);



        $amenities = $request->input('amenities', []);

        $validated['is_available'] = false;
        $validated['available_dates'] = null;

        $boardrooms = Boardroom::create($validated);


        if (!empty($amenities)) {
            $boardrooms->amenities()->sync($amenities);
        }

        return redirect()->back()->with('success', 'A boardroom has been created successfully.');

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
            'boardroom_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('boardrooms')
                    ->ignore($boardroom->id) 
                    ->where(function ($query) use ($request) {
                        return $query->where('location_id', $request->location_id);
                    }),
            ],
            'location_id'     => 'required|exists:locations,id',
            'seats'           => 'required|numeric',
            'hourly_price'    => 'required|numeric|min:0',
            'daily_price'     => 'required|numeric|min:0',
            'is_available'    => ['nullable'],
            'available_dates' => ['nullable'],
        ], [
            'boardroom_name.unique' => 'A boardroom with this name already exists at the selected location.',
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
