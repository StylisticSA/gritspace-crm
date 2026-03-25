<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Office;
use App\Models\HelpDesk;
use App\Models\Location;
use App\Models\Boardroom;
use App\Models\ClientRate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\VirtualOffice;
use App\Models\AgrementUpload;
use Illuminate\Validation\Rule;
use App\Models\ClientInformation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanyDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $search = $request->input('search');

        $clients = ClientInformation::with('location', 'rates')
                    ->where('user_id', auth()->id())
                    ->first();

        if (auth()->user()->hasRole(['super admin','admin'])) {
            return redirect()->route('admin.clientinfor.index');
        }

        // dd($clients);

        if (empty($clients)) {
            return redirect()->route('companydetail.create');
        } else {

            // dd($clients->identity_path);
            $clients->identity_path = $clients->identity_path
                ? Storage::disk('google')->url($clients->identity_path)
                : null;

            $clients->residency_path = $clients->residency_path
                ? Storage::disk('google')->url($clients->residency_path)
                : null;

            $clients->company_reg_path = $clients->company_reg_path
                ? Storage::disk('google')->url($clients->company_reg_path)
                : null;

            // dd($clients->location);
            $locations = Location::select('id', 'name')->get();


            $agreement = AgrementUpload::select('id', 'user_id', 'agreement', 'status')
                ->where('user_id', Auth()->id())
                ->first();

            if ($agreement) {
                $agreement->agreement = $agreement->agreement
                    ? Storage::disk('google')->url($agreement->agreement)
                    : null;
            }

            return Inertia::render('CompanyDetails/IndexCompany', [
                'clients'   => $clients,
                'agreement' => $agreement,
                'locations' => $locations
            ]);


        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $locations = Location::select('id', 'name', 'address', 'city')->get();

        $closed = Office::with('category')
                ->whereHas('category', function ($query) {
                    $query->whereIn(DB::raw('LOWER(name)'), ['closed office', 'closed offices']);
                })
                ->select('id', 'office_name', 'category_id')
                ->get();


        $dedicated = Office::with('category')
                  ->whereHas('category', function ($query) {
                      $query->whereIn(DB::raw('LOWER(name)'), ['dedicated desk', 'dedicated desks']);
                  })
                  ->select('id', 'office_name', 'category_id')
                  ->get();

        $hotdesks = HelpDesk::select('id', 'help_desk_name')->get();


        $virtuals = VirtualOffice::with('location')->get();

        $boardrooms = Boardroom::select('id', 'boardroom_name')->get();

        $userType = auth()->user()->user_type;

        return Inertia::render('CompanyDetails/CreateCompany', [
            'locations'     => $locations,
            'closedoffices' => $closed,
            'dedicated'     => $dedicated,
            'hotdesks'      => $hotdesks,
            'virtuals'      => $virtuals,
            'user_type'     => $userType,

        ]);

    }

    

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        // dd($request);
        
        $validated = $request->validate([
            'location_id'                   => 'nullable|exists:locations,id',
            'name'                          => 'required|string|max:255',
            'surname'                       => 'required|string|max:255',
            'cell_number'                   => 'required|string|max:20',
            'email_address'                 => 'required|email|max:255|unique:client_information,email_address',
            'company_name'                  => 'nullable|string|max:255',
            'company_registration_number'   => 'nullable|string|max:100',
            'identity_path'                 => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'residency_path'                => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'company_reg_path'              => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'agreement'                     => 'required'
        ]);

        $validated['user_id'] = auth()->id();

        $user = auth()->user();

        $existing = ClientInformation::where('user_id', auth()->id())->first();

        if ($existing?->identity_path && $request->hasFile('identity')) {
            return back()->withErrors([
                'identity' => 'An identity document already exists for this user.',
            ]);
        }

        if ($existing?->residency_path && $request->hasFile('residency')) {
            return back()->withErrors([
                'residency' => 'A residency document already exists for this user.',
            ]);
        }

        if ($existing?->company_reg_path && $request->hasFile('company_reg_path')) {
            return back()->withErrors([
                'company registration' => 'A company registration document already exists for this user.',
            ]);
        }

        DB::beginTransaction();

        try {
            $client = new ClientInformation($validated);

            $identityPath = null;
            $residencyPath = null;
            $companyRegPath = null;

            if ($request->hasFile('identity') && empty($existing?->identity_path)) {
                $identityFile = $request->file('identity');
                $identityName = 'identity_' . Str::slug($user->name) . '_' . Str::uuid() . '.' . $identityFile->getClientOriginalExtension();
                $identityPath = Storage::disk('google')->putFileAs('identity', $identityFile, $identityName);
                $client->identity_path = $identityPath;
            }

            if ($request->hasFile('residency') && empty($existing?->residency_path)) {
                $residencyFile = $request->file('residency');
                $residencyName = 'residency_' . Str::slug($user->name) . '_' . Str::uuid() . '.' . $residencyFile->getClientOriginalExtension();
                $residencyPath = Storage::disk('google')->putFileAs('residency', $residencyFile, $residencyName);
                $client->residency_path = $residencyPath;
            }

            if ($request->hasFile('company_reg_path') && empty($existing?->company_reg_path)) {
                $companyRegFile = $request->file('company_reg_path');
                $companyRegName = 'company_reg_' . Str::slug($user->name) . '_' . Str::uuid() . '.' . $companyRegFile->getClientOriginalExtension();
                $companyRegPath = Storage::disk('google')->putFileAs('company_reg', $companyRegFile, $companyRegName);
                $client->company_reg_path = $companyRegPath;
            }



            $client->save();

            $rows = collect([
                'closed'     => $request->closed_rows,
                'dedicated'  => $request->dedicated_rows,
                'boardroom'  => $request->boardroom_rows,
                'virtual'    => $request->virtual_rows,
                'hotdesk'    => $request->hotdesk_rows,
            ])->flatMap(function ($items, $type) use ($client) {
                return collect($items ?? [])->filter(fn ($item) => !empty($item['name']))
                    ->map(function ($item) use ($type, $client) {
                        return [
                            'user_id'               => $client->user_id,
                            'client_information_id' => $client->id,
                            'type'                  => $type,
                            'space_id'              => $item['id'] ?? null,
                            'office_name'           => $item['name'] ?? null,
                            'start_date'            => $item['start_date'] ?? null,
                            'end_date'              => $item['end_date'] ?? null,
                            'price'                 => $item['discount_price'] ?? null,
                            'created_at'            => now(),
                            'updated_at'            => now(),
                        ];
                    });
            });

            if ($rows->isNotEmpty()) {
                ClientRate::insert($rows->toArray());
            }


            DB::commit();

            return back()->with('success', 'Client information saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if (!empty($identityPath)) {
                Storage::disk('google')->delete($identityPath);
            }
            if (!empty($residencyPath)) {
                Storage::disk('google')->delete($residencyPath);
            }
            if (!empty($companyRegPath)) {
                Storage::disk('google')->delete($companyRegPath);
            }


            return back()->withErrors(['error' => 'Failed to save client information: ' . $e->getMessage()]);
        }
    }

    /**
    * Show the form for editing the specified resource.
    */
    public function edit(ClientInformation $client)
    {

        $locations = Location::select('id', 'name', 'address', 'city')->get();


        // Transform paths into Google Drive URLs
        $client->identity_path = $client->identity_path
            ? Storage::disk('google')->url($client->identity_path)
            : null;

        $client->residency_path = $client->residency_path
            ? Storage::disk('google')->url($client->residency_path)
            : null;

        $client->company_reg_path = $client->company_reg_path
            ? Storage::disk('google')->url($client->company_reg_path)
            : null;



        return Inertia::render('CompanyDetails/EditCompany', [
                'clients' => $client->load(['location']),
                'locations' => $locations,
             ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClientInformation $client)
    {

        $validated = $request->validate([
            'location_id' => 'nullable|exists:locations,id',
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'cell_number' => 'required|string|max:20',
            'email_address' => [
                'required',
                'email',
                'max:255',
                Rule::unique('client_information', 'email_address')->ignore($client->id),
            ],
            'company_name' => 'nullable|string|max:255',
            'company_registration_number' => 'nullable|string|max:100',
        ]);


        $validated['user_id'] = auth()->id();

        if ($request->hasFile('identity')) {
            if ($client->identity_path && Storage::disk('google')->exists($client->identity_path)) {
                Storage::disk('google')->delete($client->identity_path);
            }

            $identityFile = $request->file('identity');
            $identityName = 'identity_' . Str::slug(auth()->user()->name) . '_' . Str::uuid() . '.' . $identityFile->getClientOriginalExtension();
            $identityPath = Storage::disk('google')->putFileAs('identity', $identityFile, $identityName);
            $client->identity_path = $identityPath;
        }

        if ($request->hasFile('residency')) {
            if ($client->residency_path && Storage::disk('google')->exists($client->residency_path)) {
                Storage::disk('google')->delete($client->residency_path);
            }

            $residencyFile = $request->file('residency');
            $residencyName = 'residency_' . Str::slug(auth()->user()->name) . '_' . Str::uuid() . '.' . $residencyFile->getClientOriginalExtension();
            $residencyPath = Storage::disk('google')->putFileAs('residency', $residencyFile, $residencyName);
            $client->residency_path = $residencyPath;
        }

        if ($request->hasFile('company_reg_path')) {
            if ($client->company_reg_path && Storage::disk('google')->exists($client->company_reg_path)) {
                Storage::disk('google')->delete($client->company_reg_path);
            }

            $companyRegFile = $request->file('company_reg_path');
            $companyRegName = 'company_reg_path_' . Str::slug(auth()->user()->name) . '_' . Str::uuid() . '.' . $companyRegFile->getClientOriginalExtension();
            $companyRegPath = Storage::disk('google')->putFileAs('company_reg', $companyRegFile, $companyRegName);
            $client->company_reg_path = $companyRegPath;
        }


        $clients = $client->update($validated);


        return redirect()->route('companydetail.index')
            ->with('success', 'Company Details Updated successfully!');

    }

}
