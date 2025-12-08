<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Extra;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExtraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $search = $request->input('search');

        $extra = Extra::with(['location'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('location', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Daily/Extra/ExtraIndex', [
            'extra' => $extra,
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

        return Inertia::render('Daily/Extra/ExtraCreate', [
                    'locations' => $locations,

                ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
                        'location_id' => ['required', 'exists:locations,id'],
                        'name' => [
                            'required',
                            Rule::unique('extras')->where(function ($query) use ($request) {
                                return $query->where('location_id', $request->location_id);
                            }),
                        ],
                        'code' => ['nullable'],
                        'price' => ['required', 'numeric'],
                    ]);


        $validated['code'] = strtolower($validated['name']);

        Extra::create($validated);

        return redirect()->route('admin.extra.index')->with('success', 'Settings has been saved successfully.');

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Extra $extra, $id)
    {

        $locations = Location::select('id', 'name')->get();

        $extras = Extra::findOrFail($id);


        return Inertia::render('Daily/Extra/ExtraEdit', [
                   'extra' => $extras,
                   'locations' => $locations
               ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $validated = $request->validate([
                              'location_id' => ['required', 'exists:locations,id'],
                               'name' => [
                                    'required',
                                    Rule::unique('extras')
                                        ->where(fn ($query) => $query->where('location_id', $request->location_id))
                                        ->ignore($id),
                                ],
                              'price' => ['required', 'numeric'],
                          ]);

        Extra::where('id', $id)->update($validated);

        return redirect()->route('admin.extra.index')->with('success', 'Settings has been updated successfully.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Extra $extra, $id)
    {

        $extras = Extra::findOrFail($id);

        $extras->delete();

        return redirect()->route('admin.extra.index')->with('success', 'Extra Setting has been deleted successfully.');

    }
}
