<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\OfficePricing;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class OfficePricingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $officesRates = OfficePricing::when($search, function ($query, $search) {
            $query->where('pricing_type', 'like', "%{$search}%");
        })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Rates/IndexRates', [
            'officesRates' => $officesRates,
            'filters' => [
                'search' => $search,
            ]
        ]);


    }

    /**
     * Display a listing of the resource.
     */
    public function indexAdmin()
    {
        $pricing = OfficePricing::all();
        return response()->json($pricing);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::select('id', 'name')
                    ->whereIn(DB::raw('LOWER(name)'), [
                        'dedicated desk',
                        'dedicated desks',
                    ])->get();

        return Inertia::render('Rates/CreateRate', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name'    => 'required|string|max:2555',
            'pricing_type'  => ['required', 'string', 'max:255',
                                Rule::unique('office_pricings')->where(function ($query) use ($request) {
                                    return $query->where('category_name', $request->category_name);
                                }),
                            ],
            'rate'             => 'required|numeric|min:0|max:999999',
        ]);

        $officePricing = OfficePricing::create($validated);

        return redirect()->route('admin.offices_rates')->with('success', 'Office Rates created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(OfficePricing $officePricing)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $officePricing = OfficePricing::findOrFail($id);

        $categories = Category::select('id', 'name')->get();

        return Inertia::render('Rates/EditRates', [
            'office_rates' => $officePricing,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OfficePricing $officePricing)
    {
        // dd($officePricing->id);
        $validated = $request->validate([
           'category_name'     => 'required','string','max:255',
            'pricing_type'  => ['required', 'string', 'max:255',
                                Rule::unique('office_pricings')
                                    ->where(function ($query) use ($request) {
                                        return $query->where('category_name', $request->category_name);
                                    })
                                    ->ignore($officePricing->id),
                            ],

            'rate'             => 'nullable|numeric|min:0|max:999999',
        ]);

        $officePricing->update($validated);

        return redirect()->route('admin.offices_rates')->with('success', 'Office Rates updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OfficePricing $officePricing)
    {

        $officePricing->delete();

        return back()->with('success', 'Pricing deleted successfully.');
    }
}
