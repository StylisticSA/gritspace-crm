<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Office;
use App\Models\Category;
use App\Models\HelpDesk;
use App\Models\Location;
use App\Models\Boardroom;
use App\Models\ClientRate;
use Illuminate\Http\Request;
use App\Models\VirtualOffice;
use App\Models\ClientInformation;
use Illuminate\Support\Facades\DB;

class ClientRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $search = $request->input('search');

        $clientsRates = ClientRate::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();



        return Inertia::render('Clients/ClientRates/IndexRates', [
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
                        ->orderBy('created_at')
                        ->get();

        $dedicateddesks = Office::with(['location', 'pricing', 'category'])
                                ->whereHas('category', function ($q) {
                                    $q->whereIn(DB::raw('LOWER(name)'), [
                                        'dedicated desks',
                                        'dedicated desk',
                                    ]);
                                })
                                ->orderBy('created_at')
                                ->get();


        $users = User::with('roles')
                          ->whereHas('roles', function ($query) {
                              $query->whereIn(DB::raw('LOWER(name)'), ['user', 'users']);
                          })->select('id', 'name')
                          ->get();

        $locations = Location::select('id', 'name', 'address', 'city')->get();


        $categories = Category::select('id', 'name')->get();

        $virtuals = VirtualOffice::all();

        $boardrooms = Boardroom::all();

        $hotdesks = HelpDesk::all();

        return Inertia::render('Clients/ClientRates/CreateRates', [
                    'closedoffices'     => $closedoffices,
                    'locations'         => $locations,
                    'users'             => $users,
                    'categories'        => $categories,
                    'dedicateddesks'    => $dedicateddesks,
                    'boardrooms'        => $boardrooms,
                    'virtuals'          => $virtuals,
                    'hotdesks'          => $hotdesks,

                ]);


    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'user_id'     => 'required|integer|exists:users,id',
            'location_id' => 'required|integer|exists:locations,id',

            'closed_rows'                   => 'nullable|array',
            'closed_rows.*.start_date'      => 'nullable|date',
            'closed_rows.*.end_date'        => 'nullable|date|after:closed_rows.*.start_date',
            'closed_rows.*.discount_price'  => 'nullable|numeric|min:0',

            'dedicated_rows'                => 'nullable|array',
            'dedicated_rows.*.start_date'   => 'nullable|date',
            'dedicated_rows.*.end_date'     => 'nullable|date|after:dedicated_rows.*.start_date',
            'dedicated_rows.*.discount_price' => 'nullable|numeric|min:0',

            'virtual_rows'                  => 'nullable|array',
            'virtual_rows.*.start_date'     => 'nullable|date',
            'virtual_rows.*.end_date'       => 'nullable|date|after:virtual_rows.*.start_date',
            'virtual_rows.*.discount_price' => 'nullable|numeric|min:0',

            'hotdesk_rows'                  => 'nullable|array',
            'hotdesk_rows.*.start_date'     => 'nullable|date',
            'hotdesk_rows.*.end_date'       => 'nullable|date|after:hotdesk_rows.*.start_date',
            'hotdesk_rows.*.discount_price' => 'nullable|numeric|min:0',
        ]);

        $userId = $validated['user_id'];
        $client = ClientInformation::where('user_id', $userId)->firstOrFail();

        $incomingRows = collect([
            'closed'    => $request->closed_rows,
            'dedicated' => $request->dedicated_rows,
            'virtual'   => $request->virtual_rows,
            'hotdesk'   => $request->hotdesk_rows,
        ]);

        $incomingTypes = $incomingRows->filter(fn ($rows) => !empty($rows))->keys()->toArray();



        $existingTypes = ClientRate::where('client_information_id', $client->id)
            ->pluck('type')
            ->unique()
            ->toArray();

        $conflicts = array_intersect($existingTypes, $incomingTypes);

        if (count($conflicts) === count($incomingTypes)) {
            return back()->withErrors([
                'client_rates' => 'Client already has rates for: ' . implode(', ', $conflicts),
            ]);
        }

        $filteredRows = $incomingRows->filter(fn ($rows, $type) => !in_array($type, $conflicts));


        $rows = $filteredRows->flatMap(function ($items, $type) use ($client) {
            return collect($items)
                ->filter(function ($item) {
                    return !empty($item['start_date']) || !empty($item['end_date']) || !empty($item['discount_price']);
                })
                ->map(function ($item) use ($type, $client) {
                    return [
                        'user_id'               => $client->user_id,
                        'client_information_id' => $client->id,
                        'type'                  => $type,
                        'space_id'              => $item['id'] ?? null,
                        'office_name'           => $item['name'] ?? null,
                        'start_date'            => $item['start_date'],
                        'end_date'              => $item['end_date'],
                        'price'                 => $item['discount_price'],
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ];
                });
        });


        if ($rows->isNotEmpty()) {
            ClientRate::insert($rows->toArray());
        }

        return redirect()->route('admin.clientrates.index')
            ->with('success', 'Client Rates has been Saved successfully.');
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClientRate $clientRate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClientRate $clientRate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClientRate $client)
    {
        $client->delete();

        return redirect()->back()->with('success', 'Client Rate has been Deleted Successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editCompany(ClientRate $clientRate)
    {
        // dd($clientRate->type);

        $closed = Office::with('category')
                    ->whereHas('category', function ($query) {
                        $query->whereIn(DB::raw('LOWER(name)'), ['closed office', 'closed offices']);
                    })
                    ->select('id', 'office_name', 'category_id')
                    ->get();


        $dedicated = Office::with(['category'])
                            ->whereHas('category', function ($q) {
                                $q->whereIn(DB::raw('LOWER(name)'), [
                                    'dedicated desks',
                                    'dedicated desk',
                                ]);
                            })
                            ->select('id', 'office_name', 'category_id')
                            ->get();



        $virtuals = VirtualOffice::select('id', 'virtualoffice_name')->get();

        $boardrooms = Boardroom::select('id', 'boardroom_name')->get();

        $hotdesks = HelpDesk::select('id', 'help_desk_name')->get();


        return Inertia::render('Clients/ClientRates/UserRates/EditClosed', [
            'closedoffices' => $closed,
            'clientRate' => $clientRate,
            'boardrooms' => $boardrooms,
            'virtuals'  => $virtuals,
            'hotdesks'  => $hotdesks,
            'dedicated' => $dedicated,

        ]);

    }

    public function updateCompany(Request $request, ClientRate $clientRate)
    {

        $validated = $request->validate([
            'office_name' => 'nullable',
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_active' => ['nullable', 'boolean'],
        ]);

        $validated['user_id'] = auth()->id();

        $clients = $clientRate->update($validated);


        if (auth()->user()->hasRole(['Admin', 'Super Admin'])) {
            return redirect()->route('admin.clientrates.index')
                ->with('success', 'Company Details Updated successfully!');
        } else {
            return redirect()->route('clientrates.editCompany', $clientRate)
                ->with('success', 'Company Details Updated successfully!');
        }



    }
}
