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

class ClosedOfficeController extends Controller
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
                $query->where('office_name', 'like', "%{$search}%");
            })
            ->whereHas('category', function ($q) {
                $q->whereIn(DB::raw('LOWER(name)'), [
                    'closed office',
                    'closed offices',
                ]);
            })

            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Closed/AdminClosed', [
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


        $amenities = Amenity::all();

        $categories = Category::select('id', 'name')
            ->whereIn(DB::raw('LOWER(name)'), [
                'closed office',
                'closed offices',
            ])->get();


        return Inertia::render('Closed/CreateOffice', [
            'locations'     => $locations,
            'amenities'     => $amenities,
            'categories'    => $categories
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
                    })

            ],
            'category_id'           => ['required', 'exists:categories,id'],
            'location_id'           => ['required', 'exists:locations,id'],
            'seats'                 => ['required', 'integer', 'min:1'],
            'monthly_rate'          => ['required', 'numeric', 'min:0'],
            'daily_rate'            => ['nullable', 'numeric', 'min:0'],
            'is_available'          => ['nullable'],
            'available_dates'       => ['nullable'],
            'free_boardroom_hours'  => ['nullable'],
            'amenities'             => ['array'],
            'amenities.*'           => ['exists:amenities,id'],
        ],[
            'office_name.unique' => 'This office name already exists for the given location and category.',
        ]);


        $office = Office::create([
            'office_name'           => $validated['office_name'],
            'category_id'           => $validated['category_id'],
            'location_id'           => $validated['location_id'],
            'seats'                 => $validated['seats'],
            'monthly_rate'          => $validated['monthly_rate'],
            'daily_rate'            => $validated['daily_rate'] ?? null,
            'free_boardroom_hours'  => $validated['free_boardroom_hours'] ?? null,
            'is_available'          => false,
            'available_dates'       => null,
        ]);

        if (isset($validated['amenities']) && count($validated['amenities']) > 0) {
            $office->amenities()->attach($validated['amenities']);
        }

        return redirect()->back()->with('success', 'Closed Offices has been created successfully.');
        
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
                    $query->where('name', 'Closed Office');
                })
                ->latest()
                ->paginate(10);
        } else {
            $bookings = Booking::with(['office.location', 'office.category'])
                ->whereHas('office.category', function ($query) {
                    $query->where('name', 'Closed Office');
                })
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(10);
        }

        return Inertia::render('Bookings/Closed/ShowClosed', [
            'bookings' => $bookings,
        ]);
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

        $categories = Category::select('id', 'name')
            ->whereIn(DB::raw('LOWER(name)'), [
                'closed office',
                'closed offices',
            ])->get();


        return Inertia::render('Closed/EditOffice', [
            'office' => $Office->load(['location', 'pricing', 'amenities']),
            'locations' => $locations,
            'pricings' => $pricings,
            'amenities' => $amenities,
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function view(Booking $closed)
    {

        $user = auth()->user();

        if ($user->hasRole('admin') || $user->hasRole('super admin')) {

            $office =  $closed->load([
                       'user',
                       'office',
                       'category',
                       'office.amenities',
                       'office.location'
                   ]);
            // dd($office);

        } else {
            $office = $closed->load([
                'user',
                'office',
                'category',
                'office.amenities',
                'office.location',
            ]);

            $office = $closed->setRelation(
                'bookings',
                $closed->bookings()
                    ->where('user_id', $user->id)
                    ->with(['category'])
                    ->get()
            );
        }


        return Inertia::render('Bookings/Closed/ViewClosed', [
            'closed' => $office,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Office $Office)
    {

        $this->authorize('update', $Office);

        $validated = $request->validate([
             'office_name'  => [
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
            'category_id'     => ['required', 'exists:categories,id'],
            'location_id'     => ['required', 'exists:locations,id'],
            'seats'           => ['required', 'integer', 'min:1'],
            'monthly_rate'    => ['required', 'numeric', 'min:0'],
            'daily_rate'      => ['required', 'numeric', 'min:0'],
            'free_boardroom_hours' => ['nullable'],
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

        $monthly_rate = $isDedicatedDesk ? null : $validated['monthly_rate'];
        $daily_rate   = $isDedicatedDesk ? null : $validated['daily_rate'];

        $Office->update([
            'office_name'     => $validated['office_name'],
            'category_id'     => $validated['category_id'],
            'location_id'     => $validated['location_id'],
            'seats'           => $validated['seats'],
            'monthly_rate'    => $monthly_rate,
            'daily_rate'      => $daily_rate,
            'free_boardroom_hours'  => $validated['free_boardroom_hours'] ?? null,
        ]);

        $Office->amenities()->sync($validated['amenities'] ?? []);


        return redirect()->back()->with('success', 'Closed Office has been updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $Office)
    {
        $this->authorize('delete', $Office);

        $Office->delete();

        return back()->with('success', 'Closed Office has been Deleted successfully.');
    }


    public function availability(Request $request, Office $closed)
    {
        // dd($closed);
        $validated = $request->validate([
              'is_available' => ['required', 'boolean'],
              'available_dates' => ['nullable', 'date'],
          ]);

        $closed->update($validated);

        return back()->with('success', 'Availability updated successfully.');
        

    }

}
