<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Amenity;
use App\Models\HelpDesk;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HelpDeskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $helpDesks = HelpDesk::when($search, function ($query, $search) {
            $query->where('help_desk_name', 'like', "%{$search}%");
        })
            ->with('location')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('HelpDesk/AdminHelpDesk', [
            'helpDesks' => $helpDesks,
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
        $locations = Location::select('id', 'name')->get();

        $amenities = Amenity::all();

        return Inertia::render('HelpDesk/CreateHelpDesk', [
            'locations' => $locations,
            'amenities' => $amenities
,        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'help_desk_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('help_desks')
                    ->ignore($request->id)
                    ->where(function ($query) use ($request) {
                        return $query->where('location_id', $request->location_id);
                    }),
            ],
            'location_id'     => 'required',
            'price'           => 'required|numeric',
            'duration'        => 'required|numeric',
            'desks'           => 'nullable|numeric',
            'discount'        => 'nullable|numeric',
            'is_available'    => ['nullable'],
            'available_dates' => ['nullable'],

        ]);

        $amenities = $request->input('amenities', []);

        $validated['is_available']    = false;
        $validated['available_dates'] = null;

        $helpDesk = HelpDesk::create($validated);

        if (!empty($amenities)) {
            $helpDesk->amenities()->sync($amenities);
        }

     
        return back()->with('success', 'Hot Desk Created successfully..');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HelpDesk $helpDesk)
    {
        $locations = Location::select('id', 'name')->get();

        $amenities = Amenity::select('id', 'amenity_name')->get();

        $helpDesks = $helpDesk->load(['amenities']);

        return Inertia::render('HelpDesk/EditHelpDesk', [
            'helpDesks' => $helpDesks,
            'locations' => $locations,
            'amenities' => $amenities,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HelpDesk $helpDesk)
    {


        $validated = $request->validate([
           'help_desk_name'    => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('help_desks')
                            ->ignore($helpDesk->id)
                            ->where(function ($query) use ($request) {
                                return $query
                                    ->where('location_id', $request->location_id);
                            })
            ],
           'location_id'       => 'required',
           'price'             => 'required|numeric',
           'duration'          => 'required|numeric',
           'desks'             => 'nullable|numeric',
           'discount'          => 'nullable|numeric',
           
           'is_available'    => ['nullable'],
            'available_dates' => ['nullable'],
        ]);

        $amenities = $request->input('amenities', []);

        $validated['is_available']    = false;
        $validated['available_dates'] = null;

        $helpDesk->update($validated);

        if (!empty($amenities)) {
            $helpDesk->amenities()->sync($amenities);
        }

        return back()->with('success', 'Hot Desk updated successfully..');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HelpDesk $helpDesk)
    {

        $helpDesk->amenities()->detach();

        $helpDesk->delete();

        return back()->with('success', 'Help Desk deleted successfully.');
    }

    public function availability(Request $request, HelpDesk $hotdesk)
    {
        
        $validated = $request->validate([
              'is_available' => ['required', 'boolean'],
              'available_dates' => ['nullable', 'date'],
          ]);

        $hotdesk->update($validated);

        return back()->with('success', 'Availability updated successfully.');

    }
}
