<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $discounts = Discount::with(['location', 'category'])
                    ->when($search, function ($query, $search) {
                        $query->whereHas('location', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('category', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->orderByDesc('created_at')
                    ->paginate(10)
                    ->withQueryString();

        return Inertia::render("Discounts/AdminDiscounts", [
            'discounts' => $discounts,
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
        
        $location = Location::get(['id', 'name']);
        $category = Category::get(['id', 'name']);

        return Inertia::render("Discounts/CreateDiscount", [
                    'locations'     => $location,
                    'categories'    => $category,
                ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'package'     => ['required', 'string', 'max:255'],

            'discount'    => ['required', 'integer', 'min:0'],

            'package' => [
                'required',
                'string',
                'max:255',
                Rule::unique('discounts')->where(function ($query) use ($request) {
                    return $query->where('location_id', $request->location_id)
                                ->where('category_id', $request->category_id);
                }),
            ],


        ]);

        Discount::create($validated);

        return redirect()->back()->with('success', 'Discount has been saved successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Discount $discount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Discount $discount)
    {
        
        $location = Location::get(['id', 'name']);
        $categories = Category::get(['id', 'name']);


        return Inertia::render("Discounts/EditDiscount", [
                    'locations'     => $location,
                    'discount'  => $discount->load(['location']),
                     'categories'    => $categories,
                ]);
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Discount $discount)
    {
        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'package'     => ['required', 'string', 'max:255'],
            'discount'    => ['required', 'integer', 'min:0'],

            'package' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('discounts')
                        ->where(function ($query) use ($request) {
                            return $query->where('location_id', $request->location_id)
                                        ->where('category_id', $request->category_id);
                        })
                        ->ignore($discount->id), 
                ],

        ]);

        $discount->update($validated);

        return redirect()->back()->with('success', 'Discount has been updated successfully.');
    }

    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discount $discount)
    {
        $discount->delete();

        return redirect()->back()->with('success', 'Discount has been deleted successfully.');
    }
}
