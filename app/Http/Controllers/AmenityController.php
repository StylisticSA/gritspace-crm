<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Amenity;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $amenities = Amenity::when($search, function ($query, $search) {
            $query->where('amenity_name', 'like', "%{$search}%");
        })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Amenities/AdminAmenity', [
            'amenities' => $amenities,
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
        return Inertia::render('Amenities/CreateAmenity');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       

        $validated = $request->validate([
           'amenity_name'      => 'required|string|max:255',
           'description'       => 'nullable|string|max:255',
           'price'             => 'nullable|numeric|min:0',
        ]);

        $amenities = Amenity::create($validated);

        return redirect()->route('admin.amenities')->with('success', 'An amenity has been created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Amenity $amenity)
    {
        return Inertia::render('Amenities/EditAmenity', [
            'amenities' => $amenity,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Amenity $amenity)
    {

        $validated = $request->validate([
            'amenity_name'      => 'required|string|max:255',
            'description'       => 'nullable|string|max:255',
            'price'             => 'nullable|numeric'
        ]);

        $amenity->update($validated);

        return redirect()->route('admin.amenities')->with('success', 'Office Rates updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Amenity $amenity)
    {

        $amenity->delete();

        return redirect()->route('admin.amenities')->with('success', 'An Amenity has been deleted successfully.');
    }
}
