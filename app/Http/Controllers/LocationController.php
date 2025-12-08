<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $locations = Location::when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString(); 

        return Inertia::render('Locations/AdminLocations', [
            'locations' => $locations,
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
        return Inertia::render('Locations/CreateLocation');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'address'  => 'required|string|max:255',
            'city'     => 'required|string'
        ]);

        $Location = Location::create($validated);

        return redirect()->route('admin.locations')->with('success', 'A Location has been created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location)
    {
         return Inertia::render('Locations/EditLocation', [
            'locations' => $location,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
          $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'address'  => 'required|string|max:255',
            'city'     => 'required|string'
        ]);

        $location->update($validated);

        return redirect()->route('admin.locations')->with('success', 'A location updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {

        $location->delete();

        return redirect()->route('admin.locations')->with('success', 'A Location has been deleted successfully.');
    }
}
