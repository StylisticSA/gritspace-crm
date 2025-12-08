<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Office;
use App\Models\Amenity;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\OfficePricing;
use Illuminate\Validation\Rule;

class OfficeController extends Controller
{
    /**
    * Display a listing of the resource.
    */
    public function adminIndex(Request $request)
    {
        $this->authorize('viewAny', Office::class);

        $search = $request->input('search');

        $offices = Office::with(['location', 'pricing', 'category'])
            ->when($search, function ($query, $search) {
                $query->where('office_name', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Offices/AdminOffice', [
            'offices' => $offices,
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
        $this->authorize('create', Office::class);

        $locations = Location::select('id', 'name')->get();
        $pricing = OfficePricing::select('id', 'category_name', 'pricing_type', 'rate')
                 ->where('category_name', 'Dedicated Desk')->get();
        $amenities = Amenity::all();
        $categories = Category::select('id', 'name')->where('name', '!=', 'Virtual Office')->get();

        return Inertia::render('Offices/CreateOffice', [
            'locations'     => $locations,
            'pricings'      => $pricing,
            'amenities'     => $amenities,
            'categories'    => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Office::class);

        $validated = $request->validate([
            'office_name'     => ['required', 'string', 'max:255'],
            'category_id'     => ['required', 'exists:categories,id'],
            'location_id'     => ['required', 'exists:locations,id'],
            'seats'           => ['required', 'integer', 'min:1'],
            'monthly_rate'    => ['required', 'numeric', 'min:0'],
            'daily_rate'      => ['nullable', 'numeric', 'min:0'],
            'pricing_type'    => ['nullable', 'array', 'min:2'],
            'pricing_type.*'  => ['nullable', 'numeric',],
            'pricing_type'    => function ($attribute, $value, $fail) {
                if ($value && (count($value) < 2 || !isset($value[0]) || !isset($value[1]))) {
                    return $fail('Both price fields must be filled for Dedicated Desk.');
                }
            },
            'amenities'       => ['array'],
            'amenities.*'     => ['exists:amenities,id'],
        ]);

        $price_premium = isset($validated['pricing_type'][0]) ? $validated['pricing_type'][0] : null;
        $price_standard = isset($validated['pricing_type'][1]) ? $validated['pricing_type'][1] : null;

        $office = Office::create([
            'office_name'       => $validated['office_name'],
            'category_id'       => $validated['category_id'],
            'location_id'       => $validated['location_id'],
            'seats'             => $validated['seats'],
            'monthly_rate'      => $validated['monthly_rate'],
            'daily_rate'        => $validated['daily_rate'] ?? null,
            'price_premium'     => $price_premium,
            'price_standard'    => $price_standard,
        ]);

        if (isset($validated['amenities']) && count($validated['amenities']) > 0) {
            $office->amenities()->attach($validated['amenities']);
        }

        return redirect()->route('admin.offices')->with('success', 'Office created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Office $Office)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Office $Office)
    {
        $this->authorize('update', $Office);

        $locations = Location::select('id', 'name')->get();
        $pricings = OfficePricing::select('id', 'category_name', 'pricing_type', 'rate')
                 ->where('category_name', 'Dedicated Desk')->get();
        $amenities = Amenity::select('id', 'amenity_name')->get();
        $categories = Category::select('id', 'name')->where('name', '!=', 'Virtual Office')->get();

        return Inertia::render('Offices/EditOffice', [
            'office' => $Office->load(['location', 'pricing', 'amenities']),
            'locations' => $locations,
            'pricings' => $pricings,
            'amenities' => $amenities,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Office $Office)
    {
        $this->authorize('update', $Office);

        $validated = $request->validate([
            'office_name' => [ 'required', 'string', 'max:255', Rule::unique('offices', 'office_name')
                ->ignore($Office->id),
            ],
            'category_id'     => ['required', 'exists:categories,id'],
            'location_id'     => ['required', 'exists:locations,id'],
            'seats'           => ['required', 'integer', 'min:1'],
            'monthly_rate'    => ['nullable', 'numeric', 'min:0'],
            'daily_rate'      => ['nullable', 'numeric', 'min:0'],
            'pricing_type'    => ['nullable', 'array'],
            'pricing_type.*'  => ['nullable', 'numeric'],
            'amenities'       => ['array'],
            'amenities.*'     => ['exists:amenities,id'],
        ]);




        $category = Category::find($validated['category_id']);
        $categoryName = strtolower($category?->name ?? '');


        if ($categoryName === 'closed office') {
            if (is_null($validated['monthly_rate']) || is_null($validated['daily_rate'])) {
                return back()->withErrors([
                    'monthly_rate' => 'Monthly rate is required for Closed Office.',
                    'daily_rate'   => 'Daily rate is required for Closed Office.',
                ])->withInput();
            }
        }

        if ($categoryName === 'dedicated desk') {
            if (
                empty($validated['pricing_type']) ||
                count($validated['pricing_type']) < 2 ||
                !isset($validated['pricing_type'][0]) ||
                !isset($validated['pricing_type'][1])
            ) {
                return back()->withErrors([
                    'pricing_type' => 'Both price fields must be filled for Dedicated Desk.',
                ])->withInput();
            }
        }


        $isDedicatedDesk = $categoryName === 'dedicated desk';

        $price_premium  = $isDedicatedDesk ? ($validated['pricing_type'][0] ?? null) : null;
        $price_standard = $isDedicatedDesk ? ($validated['pricing_type'][1] ?? null) : null;

        $monthly_rate = $isDedicatedDesk ? null : $validated['monthly_rate'];
        $daily_rate   = $isDedicatedDesk ? null : $validated['daily_rate'];

        $Office->update([
            'office_name'     => $validated['office_name'],
            'category_id'     => $validated['category_id'],
            'location_id'     => $validated['location_id'],
            'seats'           => $validated['seats'],
            'monthly_rate'    => $monthly_rate,
            'daily_rate'      => $daily_rate,
            'price_premium'   => $price_premium,
            'price_standard'  => $price_standard,
        ]);

        $Office->amenities()->sync($validated['amenities'] ?? []);

        return redirect()->route('admin.offices')
            ->with('success', 'Office has been updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $Office)
    {
        $this->authorize('delete', $Office);

        $Office->delete();

        return redirect()->back()->with('success', 'Office deleted successfully.');
    }





}
