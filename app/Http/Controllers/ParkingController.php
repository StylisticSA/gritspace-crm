<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Parking;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParkingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $search = $request->input('search');

        $parking = Parking::with(['location'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('location', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();



        return Inertia::render('Parking/ParkingIndex', [
            'parking' => $parking,
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

        return Inertia::render('Parking/ParkingCreate', [
                    'locations' => $locations,

                ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);
        $validated = $request->validate([
                                'location_id' => ['required', 'exists:locations,id'],
                                'name' => [
                                    'required',
                                    Rule::unique('parkings')->where(function ($query) use ($request) {
                                        return $query->where('location_id', $request->location_id);
                                    }),
                                ],
                                'code' => ['nullable'],
                                'is_available'    => ['nullable'],
                                'available_dates' => ['nullable'],
                                'price' => ['required', 'numeric'],
                            ]);


        $validated['code'] = strtolower($validated['name']);

        $validated['is_available'] = 0;


        Parking::create($validated);

        return redirect()->back()->with('success', 'Parking has been saved successfully.');

    }

    /**
     * Display the specified resource.
     */
    public function show(Parking $parking)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Parking $parking)
    {

        $locations = Location::select('id', 'name')->get();

        return Inertia::render('Parking/ParkingEdit', [
                    'parking' => $parking,
                    'locations' => $locations,

                ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Parking $parking)
    {

        $validated = $request->validate([
                        'location_id' => ['required', 'exists:locations,id'],
                        'name' => [
                            'required',
                            Rule::unique('parkings')
                                ->where(function ($query) use ($request) {
                                    return $query->where('location_id', $request->location_id);
                                })
                                ->ignore($parking->id),
                        ],
                        'code' => ['nullable'],
                        'is_available'    => ['nullable'],
                        'available_dates' => ['nullable'],
                        'price' => ['required', 'numeric'],
                    ]);


        $validated['code'] = strtolower($validated['name']);

        $validated['is_available'] = 0;

        $parking->update($validated);

        return redirect()->back()->with('success', 'Parking has been updated successfully.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Parking $parking)
    {
        $parking->delete();

        return redirect()->back()->with('success', 'Parking has been deleted successfully.');

    }

    public function availability(Request $request, Parking $parking)
    {
        // dd($request);

        $validated = $request->validate([
              'is_available' => ['required', 'boolean'],
              'available_dates' => ['nullable', 'date'],
          ]);

        $parking->update($validated);

        return back()->with('success', 'Availability updated successfully.');

    }
}
