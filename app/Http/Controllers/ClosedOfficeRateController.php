<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Office;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\ClosedOfficeRate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ClosedOfficeRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $search = $request->input('search');
        $user = auth()->user();

        if ($user->hasRole('Admin') || $user->hasRole('Super Admin')) {
            $clientsRates = ClosedOfficeRate::with(['user', 'office'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })->orWhereHas('office', function ($sub) use ($search) {
                        $sub->where('office_name', 'like', "%{$search}%");
                    });
                });
            })
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();


        } else {
            $clientsRates = ClosedOfficeRate::where('user_id', $userId)
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->whereHas('user', function ($sub) use ($search) {
                            $sub->where('name', 'like', "%{$search}%");
                        })->orWhereHas('office', function ($sub) use ($search) {
                            $sub->where('office_name', 'like', "%{$search}%");
                        });
                    });
                })
                ->orderByDesc('created_at')
                ->paginate(10)
                ->withQueryString();
        }

        return Inertia::render('Clients/ClientRates/Closed/IndexClosed', [
            'clients' => $clientsRates,
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

        $closedoffices = Office::with(['location', 'pricing', 'category'])
                                ->whereHas('category', function ($q) {
                                    $q->whereIn(DB::raw('LOWER(name)'), [
                                        'closed office',
                                        'closed offices',
                                    ]);
                                })
                                ->orderBy('created_at', 'asc')
                                ->get();


        $users = User::with('roles')
                          ->whereHas('roles', function ($query) {
                              $query->whereIn(DB::raw('LOWER(name)'), ['user', 'users']);
                          })->select('id', 'name')
                          ->get();

        $locations = Location::select('id', 'name', 'address', 'city')->get();


        // $categories = Category::select('id', 'name')->get();


        return Inertia::render('Clients/ClientRates/Closed/CreateClosed', [
                    'closedoffices'     => $closedoffices,
                    'locations'         => $locations,
                    'users'             => $users,
                    // 'categories'        => $categories,

                ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'location_id' => 'required|exists:locations,id',
            'closed' => 'array',
            'closed.*' => 'exists:offices,id',
            'monthlyOffers' => 'array',
            'dailyOffers' => 'array',
        ]);

        $conflicts = [];

        foreach ($validated['closed'] as $officeId) {
            $exists = ClosedOfficeRate::where('user_id', $validated['user_id'])
                ->where('office_id', $officeId)
                ->whereNotNull('effective_to')
                ->whereNotNull('effective_from')
                ->exists();

            if ($exists) {
                $conflicts[] = $officeId;
            }
        }


        if ($conflicts) {



            $officeNames = Office::whereIn('id', $conflicts)
                ->pluck('office_name', 'id')
                ->toArray();


            return $this->create()->with([
                'booking_conflict' => 'These are already in the Database already.',
                'conflicting_offices' => $officeNames,
            ]);
        }




        DB::transaction(function () use ($validated) {
            foreach ($validated['closed'] as $officeId) {
                ClosedOfficeRate::create([
                    'user_id' => $validated['user_id'],
                    'office_id' => $officeId,
                    'monthly_rate' => $validated['monthlyOffers'][$officeId] ?? null,
                    'daily_rate' => $validated['dailyOffers'][$officeId] ?? null,
                    'effective_from' => null,
                    'effective_to' => null,
                    'status' => 'pending',
                ]);
            }
        });

        return redirect()->route('admin.closedrate.index')
            ->with('success', 'Closed Office Rates Saved Successfully!');



    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClosedOfficeRate $closedOfficeRate)
    {


        $user = auth()->user();
        $isAdmin = $user->hasRole('Admin') || $user->hasRole('Super Admin');

        // Restrict access if not admin and not owner
        if (!$isAdmin && $closedOfficeRate->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this rate.');
        }

        $closedoffices = Office::with(['location', 'category'])
            ->whereHas('category', function ($q) {
                $q->whereIn(DB::raw('LOWER(name)'), ['closed office', 'closed offices']);
            })
            ->orderBy('created_at')
            ->get();

        $locations = Location::select('id', 'name', 'address', 'city')->get();

        $users = $isAdmin
            ? User::with('roles')
                ->whereHas('roles', function ($query) {
                    $query->whereIn(DB::raw('LOWER(name)'), ['user', 'users']);
                })
                ->select('id', 'name')
                ->get()
            : collect([['id' => $user->id, 'name' => $user->name]]);

        return Inertia::render('Clients/ClientRates/Closed/EditClosed', [
            'closedoffices'     => $closedoffices,
            'locations'         => $locations,
            'users'             => $users,
            'rate'              => $closedOfficeRate,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request);

        $validated = $request->validate([
            'user_id' => 'required',
            'location_id' => 'required',
            'closed' => 'array',
            'closed.*' => 'exists:offices,id',
            'monthlyOffers' => 'array',
            'dailyOffers' => 'array',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['closed'] as $officeId) {
                ClosedOfficeRate::updateOrCreate(
                    [
                        'user_id' => $validated['user_id'],
                        'office_id' => $officeId,
                    ],
                    [
                        'monthly_rate' => $validated['monthlyOffers'][$officeId] ?? null,
                        'daily_rate' => $validated['dailyOffers'][$officeId] ?? null,
                        'status' => 'pending',
                    ]
                );
            }
        });

        return redirect()->route('admin.closedrate.index')
            ->with('success', 'Closed Office Rates updated successfully!');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
