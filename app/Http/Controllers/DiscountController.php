<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\HelpDesk;
use App\Models\Office;
use App\Models\VirtualOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $discounts = Discount::when($search, function ($query, $search) {
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
        $closed = Office::with(['location','category'])
                    ->select('id','location_id','category_id', 'office_name')
                    ->whereHas('category', function ($q) {
                        $q->whereIn(DB::raw('LOWER(name)'), [
                            'closed office',
                            'closed offices',
                        ]);
                    })->get();

        $dedicated = Office::with(['location','category'])
                    ->select('id','location_id','category_id', 'office_name')
                    ->whereHas('category', function ($q) {
                        $q->whereIn(DB::raw('LOWER(name)'), [
                            'dedicated desk',
                            'dedicated desks',
                        ]);
                    })->get();

        $hotdesk = HelpDesk::with(['location'])->select('id','location_id','help_desk_name')->get();
        $virtuals = VirtualOffice::with(['location'])->select('id','location_id','virtualoffice_name')->get();

        return Inertia::render("Discounts/CreateDiscount", [
                    'closed'     => $closed,
                    'dedicated'  => $dedicated,
                    'hotdesk'    => $hotdesk,
                    'virtuals'   => $virtuals,
                ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);

        $validated = $request->validate([
            'office_id'             => 'nullable|required_without_all:help_desk_id,virtual_office_id',
            'help_desk_id'          => 'nullable|required_without_all:office_id,virtual_office_id',
            'virtual_office_id'     => 'nullable|required_without_all:office_id,help_desk_id',
            'discount'              => 'required|numeric|min:0|max:100',
            'name'                  => 'nullable',
            'selectedCategory'      => 'nullable',
        ], [
            'office_id.required_without_all'   => 'Please select at least one office type.',
            'help_desk_id.required_without_all' => 'Please select at least one office type.',
            'virtual_office_id.required_without_all'  => 'Please select at least one office type.',
        ]);

     
        $validated['office_type'] = $validated['selectedCategory'];
        unset($validated['selectedCategory']);

        $exists = Discount::where('office_id', $validated['office_id'] ?? null)
            ->where('help_desk_id', $validated['help_desk_id'] ?? null)
            ->where('virtual_office_id', $validated['virtual_office_id'] ?? null)
            ->exists();

        if ($exists) {
            $errors = [];

            if (!empty($validated['office_id'])) {
                $errors['office_id'] = 'A discount for this office already exists.';
            }
            if (!empty($validated['help_desk_id'])) {
                $errors['help_desk_id'] = 'A discount for this help desk already exists.';
            }
            if (!empty($validated['virtual_office_id'])) {
                $errors['virtual_office_id'] = 'A discount for this virtual office already exists.';
            }

            throw ValidationException::withMessages($errors);
        }

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
        // dd($discount);

        $closed = Office::with(['location','category'])
                    ->select('id','location_id','category_id', 'office_name')
                    ->whereHas('category', function ($q) {
                        $q->whereIn(DB::raw('LOWER(name)'), [
                            'closed office',
                            'closed offices',
                        ]);
                    })->get();

        $dedicated = Office::with(['location','category'])
                    ->select('id','location_id','category_id', 'office_name')
                    ->whereHas('category', function ($q) {
                        $q->whereIn(DB::raw('LOWER(name)'), [
                            'dedicated desk',
                            'dedicated desks',
                        ]);
                    })->get();

        $hotdesk = HelpDesk::with(['location'])->select('id','location_id','help_desk_name')->get();
        $virtuals = VirtualOffice::with(['location'])->select('id','location_id','virtualoffice_name')->get();


        return Inertia::render('Discounts/EditDiscount', [
            'discount' => $discount,
            'closed'     => $closed,
            'dedicated'  => $dedicated,
            'hotdesk'    => $hotdesk,
            'virtuals'   => $virtuals,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Discount $discount)
    {
        $validated = $request->validate([
            'office_id'             => 'nullable|required_without_all:help_desk_id,virtual_office_id',
            'help_desk_id'          => 'nullable|required_without_all:office_id,virtual_office_id',
            'virtual_office_id'     => 'nullable|required_without_all:office_id,help_desk_id',
            'discount'          => 'required|numeric|min:0|max:100',
            'name'              => 'nullable',
            'selectedCategory'  => 'nullable',
        ], [
            'office_id.required_without_all'   => 'Please select at least one office type.',
            'help_desk_id.required_without_all' => 'Please select at least one office type.',
            'virtual_office_id.required_without_all'  => 'Please select at least one office type.',
        ]);

     
        $validated['office_type'] = $validated['selectedCategory'];
        unset($validated['selectedCategory']);

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
