<?php

namespace App\Http\Controllers;

use App\Models\Boardroom;
use App\Models\Category;
use App\Models\Discount;
use App\Models\HelpDesk;
use App\Models\Location;
use App\Models\Office;
use App\Models\User;
use App\Models\VirtualOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $discounts = Discount::with(['location'])
            ->when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
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

        return Inertia::render("Discounts/CreateDiscount", [
                    'locations'     => $location,
                ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'package'     => ['required', 'string', 'max:255'],
            'discount'    => ['required', 'integer', 'min:0'],

            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('discounts')->where(function ($query) use ($request) {
                    return $query->where('location_id', $request->location_id)
                                ->where('package', $request->package);
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

        return Inertia::render("Discounts/EditDiscount", [
                    'locations'     => $location,
                    'discount'  => $discount->load(['location']),
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

            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('discounts')
                    ->where(function ($query) use ($request) {
                        return $query->where('location_id', $request->location_id)
                                    ->where('package', $request->package);
                    })
                    ->ignore($discount->id), // <-- important for update
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
