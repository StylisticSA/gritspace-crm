<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Amenity;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\VirtualOffice;

class VirtualOfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $virtualOffices = VirtualOffice::when($search, function ($query, $search) {
            $query->where('virtualoffice_name', 'like', "%{$search}%");
        })
            ->with('location', 'amenities')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();


        return Inertia::render('VirtualOffices/AdminVirtualOffices', [
            'virtualoffices' => $virtualOffices,
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

        $amenities = Amenity::select('id', 'amenity_name')->get();

        return Inertia::render('VirtualOffices/CreateVirtualOffice', [
            'locations' => $locations,
            'amenities' => $amenities
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'virtualoffice_name'        => 'required|string|max:255',
            'location_id'               => 'required|numeric',
            'free_boardroom'            => 'nullable|numeric',
            'price'                     => 'required|numeric',
            'amenities'                 => ['array'],
            'amenities.*'               => ['exists:amenities,id'],

        ]);


        $exists = VirtualOffice::where('virtualoffice_name', $validated['virtualoffice_name'])
                    ->where('location_id', $validated['location_id'])
                    ->exists();

        if ($exists) {
            return back()->withErrors([
                'virtualoffice_name' => 'This virtual office name already exists at the selected location.',
            ])->withInput();
        }

        $virtualoffice = VirtualOffice::create([
            'virtualoffice_name'        => $validated['virtualoffice_name'],
            'location_id'               => $validated['location_id'],
           
            'price'                     => $validated['price'],

        ]);


        if (isset($validated['amenities']) && count($validated['amenities']) > 0) {
            $virtualoffice->amenities()->attach($validated['amenities']);
        }

        return redirect()->route('admin.virtual-offices')->with('success', 'A Virtual Office has been created successfully.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VirtualOffice $virtualoffice)
    {
        $locations = Location::select('id', 'name')->get();

        $amenities = Amenity::select('id', 'amenity_name')->get();

        $virtuals = $virtualoffice->load(['location','amenities']);

        return Inertia::render('VirtualOffices/EditVirtualOffice', [
            'virtualoffices'    => $virtuals,
            'locations'         => $locations,
            'amenities'         => $amenities
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VirtualOffice $virtualoffice)
    {
        $validated = $request->validate([
            'virtualoffice_name'  => 'required|string|max:255',
            'location_id'         => 'required|numeric',
            'free_boardroom'      => 'nullable|numeric',
            'price'               => 'required|numeric',
            'amenities'           => ['array'],
            'amenities.*'         => ['exists:amenities,id'],
        ]);


        $exists = VirtualOffice::where('virtualoffice_name', $validated['virtualoffice_name'])
            ->where('location_id', $validated['location_id'])
            ->where('id', '!=', $virtualoffice->id) 
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'virtualoffice_name' => 'This virtual office name already exists at the selected location.',
            ])->withInput();
        }

        $virtualoffice->update([
            'virtualoffice_name'        => $validated['virtualoffice_name'],
            'location_id'               => $validated['location_id'],

            'price'                     => $validated['price'],

        ]);


        // $virtualoffice->update($validated);

        if (isset($validated['amenities'])) {
            $virtualoffice->amenities()->sync($validated['amenities']);
        }

        return redirect()->route('admin.virtual-offices')->with('success', 'Virtual Office updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VirtualOffice $virtualoffice)
    {

        $virtualoffice->amenities()->detach();

        $virtualoffice->delete();

        return redirect()->route('admin.virtual-offices')->with('success', 'A Virtual Office has been deleted successfully.');
    }
}
