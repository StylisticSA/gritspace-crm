<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Location;
use App\Models\DailyUsage;
use Illuminate\Http\Request;
use App\Models\ClientInformation;

class CoffeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {


        $search = $request->input('search');


        $coffee = DailyUsage::with(['user', 'location'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('location', fn ($l) => $l->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();


        // dd($coffee);

        return Inertia::render('Daily/Coffee/CoffeeIndex', [
            'coffee' => $coffee,
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);
        $validated = $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
                'location_id' => ['nullable', 'integer', 'exists:locations,id'],
                'type' => ['required', 'string', 'in:coffee'],
                'amount' => ['required', 'numeric', 'min:0'],
                'date' => ['required', 'date', 'date_format:Y-m-d'],
            ]);


        if ($admins = auth()->user()->hasRole(['Super Admin','Admin'])) {
            $client = ClientInformation::with('location', 'user')
                                        ->where('user_id', $validated['user_id'])
                                        ->where('approved', 1)
                                        ->first();


            if (!$client) {

                return back()->withErrors([
                    'success' => 'The user client information should is not complete.',
                ]);

            }


            $validated['location_id'] = $client->location_id;

        }


        $costs = DailyUsage::calculateTotalCost(
            $validated['location_id'],
            $validated['amount'],
            'Coffee'
        );

        $validated['total_cost'] = $costs['total_cost'];

        $user = User::where('id', $validated['user_id'])->first();

        if ($user) {
            DailyUsage::create($validated);
        }

        return redirect()->back()->with('success', 'Coffee updated successfully');

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $coffee = DailyUsage::findOrFail($id);

        $users = User::select('id', 'name')->get();
        $locations = Location::select('id', 'name')->get();

        return Inertia::render('Daily/Coffee/CoffeeEdit', [
                   'coffee' => $coffee,
                   'users' => $users,
                   'locations' => $locations
               ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        // dd($request);
        $validated = $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
                'location_id' => ['nullable', 'integer', 'exists:locations,id'],
                'type' => ['required', 'string', 'in:coffee'],
                'amount' => ['required', 'numeric', 'min:0'],
                'date' => ['required', 'date', 'date_format:Y-m-d'],
            ]);

        $costs = DailyUsage::calculateTotalCost(
            $validated['location_id'],
            $validated['amount'],
            'Coffee'
        );

        $validated['total_cost'] = $costs['total_cost'];

        DailyUsage::where('id', $id)->update($validated);

        return redirect()->back()->with('success', 'Coffee updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $coffee = DailyUsage::where('id', $id)->first();

        $coffee->delete();

        return redirect()->back()->with('success', 'Coffee has been Deleted successfully');

    }
}
