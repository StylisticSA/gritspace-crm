<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Extra;
use App\Models\Location;
use App\Models\Printing;
use Illuminate\Http\Request;
use App\Models\ClientInformation;

class PrintingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $search = $request->input('search');

        $printing = Printing::with(['user','location'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                     ->orWhereHas('location', fn ($l) => $l->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();


        return Inertia::render('Daily/Printing/PrintIndex', [
            'printing' => $printing,
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
                        'type' => ['required', 'string', 'in:printing'],
                        'black_amount' => ['required', 'numeric', 'min:0'],
                        'color_amount' => ['required', 'numeric', 'min:0'],
                        'date' => ['required', 'date', 'date_format:Y-m-d'],
                    ]);


        if ($admins = auth()->user()->hasRole(['Super Admin','Admin'])) {
            $client = ClientInformation::with('location', 'user')
                                        ->where('user_id', $validated['user_id'])
                                        ->where('approved', 1)
                                        ->first();

            $validated['location_id'] = $client->location_id;

        }

        $costs = Printing::calculateTotalCost(
            $validated['location_id'],
            $validated['black_amount'],
            $validated['color_amount']
        );

        $validated['black_total_cost'] = $costs['black_total_cost'];
        $validated['color_total_cost'] = $costs['color_total_cost'];

        $user = User::where('id', $validated['user_id'])->first();

        if ($user) {

            Printing::create($validated);
        }

        return redirect()->back()->with('success', 'Printed has been updated Successfully.');


    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $print = Printing::findOrFail($id);

        $users = User::select('id', 'name')->get();
        $locations = Location::select('id', 'name')->get();

        return Inertia::render('Daily/Printing/PrintEdit', [
                   'printing' => $print,
                   'users' => $users,
                   'locations' => $locations
               ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $validated = $request->validate([
                               'user_id' => ['required', 'integer', 'exists:users,id'],
                               'location_id' => ['nullable', 'integer', 'exists:locations,id'],
                               'type' => ['required', 'string', 'in:printing'],
                               'black_amount' => ['nullable', 'numeric', 'min:0'],
                               'color_amount' => ['nullable', 'numeric', 'min:0'],
                               'date' => ['required', 'date', 'date_format:Y-m-d'],
                               'black_total_cost' => 'nullable',
                               'color_total_cost' => 'nullable'
                           ]);



        $costs = Printing::calculateTotalCost(
            $validated['location_id'],
            $validated['black_amount'],
            $validated['color_amount']
        );

        $validated['black_total_cost'] = $costs['black_total_cost'];
        $validated['color_total_cost'] = $costs['color_total_cost'];


        Printing::where('id', $id)->update($validated);

        return redirect()->back()->with('success', 'Printed pages updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $print = Printing::where('id', $id)->first();

        $print->delete();

        return redirect()->back()->with('success', 'Printed has been Deleted successfully');

    }
}
