<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Office;
use App\Models\Amenity;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\OfficePricing;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DedicatedDeskController extends Controller
{
    /**
    * Display a listing of the resource.
    */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Office::class);

        $search = $request->input('search');

        $offices = Office::with(['location', 'pricing', 'category'])
                     ->when($search, function ($query, $search) {
                         $query->where(function ($q) use ($search) {
                             $q->where('office_name', 'like', "%{$search}%");

                         });
                     })
                    ->whereHas('category', function ($q) {
                        $q->whereIn(DB::raw('LOWER(name)'), [
                            'dedicated desk',
                            'dedicated desks',
                        ]);
                    })
                    ->orderByDesc('created_at')
                    ->paginate(10)
                    ->withQueryString();

        return Inertia::render('Dedicated/AdminDedicated', [
            'offices' => $offices,
            'filters' => [
                'search' => $search,
            ]
        ]);
    }

    /**
     * Create a new resource.
     */
    public function create()
    {
        $this->authorize('create', Office::class);

        $locations = Location::select('id', 'name')->get();


        $amenities = Amenity::all();


        $pricing = OfficePricing::select('id', 'category_name', 'pricing_type', 'rate')
                 ->whereIn(DB::raw('LOWER(category_name)'), [
                'dedicated desk',
                'dedicated desks',
            ])->get();



        $categories = Category::select('id', 'name')
                ->whereIn(DB::raw('LOWER(name)'), [
                'dedicated desk',
                'dedicated desks',
            ])->get();

        return Inertia::render('Dedicated/CreateDedicated', [
            'locations'     => $locations,
            'pricings'      => $pricing,
            'amenities'     => $amenities,
            'categories'    => $categories
        ]);
    }

    /**
     * View a resource.
     */
    public function view(Booking $dedicated)
    {

        $user = auth()->user();

        if ($user->hasRole('admin') || $user->hasRole('super admin')) {

            $office =  $dedicated->load([
                        'user',
                        'office',
                        'category',
                        'office.amenities',
                        'office.location'
                    ]);

        } else {
            $office->load([
                 'user',
                 'office',
                 'category',
                 'office.amenities',
                 'office.location',
             ]);

            $office->setRelation(
                'bookings',
                $office->bookings()
                    ->where('user_id', $user->id)
                    ->with(['category'])
                    ->get()
            );

        }

        return Inertia::render('Bookings/Dedicated/ViewDedicated', [
            'dedicated' => $office,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Office $Office)
    {
        $user = auth()->user();

        if ($user->hasRole('admin') || $user->hasRole('super admin')) {
            $bookings = Booking::with(['user', 'office.location', 'office.category'])
                ->whereHas('office.category', function ($query) {
                    $query->where('name', 'Dedicated Desk');
                })
                ->latest()
                ->paginate(10);

        } else {
            $bookings = Booking::with(['office.location', 'office.category'])
                ->whereHas('office.category', function ($query) {
                    $query->where('name', 'Dedicated Desk');
                })
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(10);
        }


        return Inertia::render('Bookings/Dedicated/ShowDedicated', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);

        $validated = $request->validate([
            'office_name'  => [
                'required',
                'string',
                'max:255',
                Rule::unique('offices')
                    ->ignore($request->id)
                    ->where(function ($query) use ($request) {
                        return $query
                            ->where('location_id', $request->location_id)
                            ->where('category_id', $request->category_id);
                    }),

            ],
            'category_id'           => ['required', 'exists:categories,id'],
            'location_id'           => ['required', 'exists:locations,id'],
            'monthly_rate'          => ['required', 'numeric', 'min:0'],
            'daily_rate'            => ['required', 'numeric', 'min:0'],
            'is_available'          => ['nullable'],
            'available_dates'       => ['nullable'],
            'free_boardroom_hours'  => ['nullable'],
            'amenities'             => ['array'],
            'amenities.*'           => ['exists:amenities,id'],
        ]);

       

        $office = Office::create([
            'office_name'           => $validated['office_name'],
            'category_id'           => $validated['category_id'],
            'location_id'           => $validated['location_id'],
            'monthly_rate'          => $validated['monthly_rate'],
            'daily_rate'            => $validated['daily_rate'],
            'free_boardroom_hours'  => $validated['free_boardroom_hours'] ?? null,
            'is_available'          => true,
            'available_dates'       => $validated['is_available'],
        ]);

        if (isset($validated['amenities']) && count($validated['amenities']) > 0) {
            $office->amenities()->attach($validated['amenities']);
        }

        return redirect()->back()->with('success', 'Dedicated Desk has been created successfully.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Office $Office)
    {
        $this->authorize('update', $Office);

        $locations = Location::select('id', 'name')->get();

        $amenities = Amenity::select('id', 'amenity_name')->get();


        $pricings = OfficePricing::select('id', 'category_name', 'pricing_type', 'rate')
                         ->whereIn(DB::raw('LOWER(category_name)'), [
                        'dedicated desk',
                        'dedicated desks',
                    ])->get();


        $categories = Category::select('id', 'name')
            ->whereIn(DB::raw('LOWER(name)'), [
                    'dedicated desk',
                    'dedicated desks',
            ])->get();

        return Inertia::render('Dedicated/EditDedicated', [
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
        // dd($Office);

        $this->authorize('update', $Office);

        $validated = $request->validate([
            'office_name'     => [
                'required',
                'string',
                'max:255',
                 Rule::unique('offices')
                    ->ignore($Office->id)
                    ->where(function ($query) use ($request) {
                        return $query
                            ->where('location_id', $request->location_id)
                            ->where('category_id', $request->category_id);
                    })
            ],
            'category_id'           => ['required', 'exists:categories,id'],
            'location_id'           => ['required', 'exists:locations,id'],
            'monthly_rate'          => ['required', 'numeric', 'min:0'],
            'daily_rate'            => ['required', 'numeric', 'min:0'],
            'amenities'             => ['array'],
            'amenities.*'           => ['exists:amenities,id'],
            'free_boardroom_hours'  => ['nullable'],
        ]);


        $Office->update([
            'office_name'           => $validated['office_name'],
            'category_id'           => $validated['category_id'],
            'location_id'           => $validated['location_id'],
            'monthly_rate'          => $validated['monthly_rate'],
            'daily_rate'            => $validated['daily_rate'],
            'free_boardroom_hours'  => $validated['free_boardroom_hours'] ?? null,
        
        ]);

        $Office->amenities()->sync($validated['amenities'] ?? []);

        return redirect()->back()->with('success', 'Dedicated Desk has been updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $Office)
    {
        $this->authorize('delete', $Office);

        $Office->amenities()->detach();

        $Office->delete();

        return redirect()->back()->with('success', 'Dedicated Desk has been Deleted successfully.');
    }

    public function availability(Request $request, Office $dedicated)
    {
       
        $validated = $request->validate([
              'is_available' => ['required', 'boolean'],
              'available_dates' => ['nullable', 'date'],
          ]);

        $dedicated->update($validated);

        return back()->with('success', 'Availability updated successfully.');

    }
}
