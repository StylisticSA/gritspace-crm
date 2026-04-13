<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Location;
use App\Models\ClientRate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\AgrementUpload;
use Illuminate\Validation\Rule;
use App\Models\ClientInformation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ClientStatusNotification;

class ClientInformationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $search = $request->input('search');

        $clients = ClientInformation::with('rates', 'location')->when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();


        $users = User::with('roles')
                 ->whereHas('roles', function ($query) {
                     $query->whereIn(DB::raw('LOWER(name)'), ['User', 'Users','Admin','Admins','Pending User']);
                 })->select('id', 'name')
                 ->get();


        $clients->getCollection()->transform(function ($client) {
            $client->identity_path = $client->identity_path
                ? Storage::disk('google')->url($client->identity_path)
                : null;

            $client->residency_path = $client->residency_path
                ? Storage::disk('google')->url($client->residency_path)
                : null;

            $client->company_reg_path = $client->company_reg_path
                ? Storage::disk('google')->url($client->company_reg_path)
                : null;

            return $client;
        });

        return Inertia::render('Clients/ClientInfo/IndexClient', [
            'clients' => $clients,
            'users' => $users,
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

        $users = User::with('roles')
            ->whereHas('roles', function ($query) {
                $query->whereIn(DB::raw('LOWER(name)'), ['pending user', 'pending users']);
            })->select('id', 'name')
            ->get();


        $locations = Location::select('id', 'name', 'address', 'city')->get();

        return Inertia::render('Clients/ClientInfo/CreateClient', [
            'locations' => $locations,
            'users' => $users
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);
        $validated = $request->validate([
                    'user_id'                       => 'required|integer',
                    'location_id'                   => 'required|exists:locations,id',
                    'name'                          => 'required|string|max:255',
                    'surname'                       => 'required|string|max:255',
                    'cell_number'                   => 'required|string|max:20',
                    'email_address'                 => 'required|email|max:255|unique:client_information,email_address',
                    'company_name'                  => 'nullable|string|max:255',
                    'company_registration_number'   => 'nullable|string|max:100',
                    'identity_path'                 => 'required|file|mimes:jpg,jpeg,png,pdf|max:5000',
                    'residency_path'                => 'required|file|mimes:jpg,jpeg,png,pdf|max:5000',
                    'company_reg_path'              => 'required|file|mimes:jpg,jpeg,png,pdf|max:5000',
                    'agreement'                     => 'required'
                ]);

        

        $userId = $request->user_id;

        $user = User::where('id', $userId)->first();
        
        $userData = ClientInformation::with('user')->where('user_id', $userId)->first();

        if ($userData) {

            return back()->withErrors([
                        'available' => "{$userData->user->name} has Company Details already.",
                    ]);

        }

        if ($userData?->identity_path && $request->hasFile('identity_path_path')) {
            return back()->withErrors([
                'identity' => 'An identity document already exists for this user.',
            ]);
        }

        if ($userData?->residency_path && $request->hasFile('residency_path_path')) {
            return back()->withErrors([
                'residency' => 'A residency document already exists for this user.',
            ]);
        }

        if ($userData?->company_reg_path && $request->hasFile('company_reg_path')) {
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

            if ($request->hasFile('identity_path') && empty($userData?->identity_path)) {
                $identityFile = $request->file('identity_path');
                $identityName = 'identity_' . Str::slug($user->name) . '_' . Str::uuid() . '.' . $identityFile->getClientOriginalExtension();
                $identityPath = Storage::disk('google')->putFileAs('identity', $identityFile, $identityName);
                $client->identity_path = $identityPath;
            }

            if ($request->hasFile('residency_path') && empty($userData?->residency_path)) {
                $residencyFile = $request->file('residency_path');
                $residencyName = 'residency_' . Str::slug($user->name) . '_' . Str::uuid() . '.' . $residencyFile->getClientOriginalExtension();
                $residencyPath = Storage::disk('google')->putFileAs('residency', $residencyFile, $residencyName);
                $client->residency_path = $residencyPath;
            }

            if ($request->hasFile('company_reg_path') && empty($userData?->company_reg_path)) {
                $companyRegFile = $request->file('company_reg_path');
                $companyRegName = 'company_reg_' . Str::slug($user->name) . '_' . Str::uuid() . '.' . $companyRegFile->getClientOriginalExtension();
                $companyRegPath = Storage::disk('google')->putFileAs('company_reg', $companyRegFile, $companyRegName);
                $client->company_reg_path = $companyRegPath;
            }

            $client->save();

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

        $users = User::with('roles')
                ->whereHas('roles', function ($query) {
                    $query->whereIn(DB::raw('LOWER(name)'), ['pending user', 'pending users']);
                })->select('id', 'name')
                ->get();


        $locations = Location::select('id', 'name', 'address', 'city')->get();


        $client->identity_path = $client->identity_path
            ? Storage::disk('google')->url($client->identity_path)
            : null;

        $client->residency_path = $client->residency_path
            ? Storage::disk('google')->url($client->residency_path)
            : null;

        $client->company_reg_path = $client->company_reg_path
            ? Storage::disk('google')->url($client->company_reg_path)
            : null;

        // dd($client);

        return Inertia::render('Clients/ClientInfo/EditClient', [
                'clients' => $client->load(['location', 'user']),
                'locations' => $locations,
                'users' => $users,
             ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClientInformation $client)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
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
            'identity_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5000',
            'residency_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5000',
            'company_reg_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5000',
            'agreement' => 'nullable'
        ]);

        $user = User::findOrFail($client->user_id);

        DB::beginTransaction();

        try {

            $oldIdentity = $client->identity_path;
            $oldResidency = $client->residency_path;
            $oldCompanyReg = $client->company_reg_path;

            $client->fill($validated);

            $identityPath = null;
            $residencyPath = null;
            $companyRegPath = null;

            if ($request->hasFile('identity_path')) {
                if (!empty($oldIdentity)) {
                    Storage::disk('google')->delete($oldIdentity);
                }
                $identityFile = $request->file('identity_path');
                $identityName = 'identity_' . Str::slug($user->name) . '_' . Str::uuid() . '.' . $identityFile->getClientOriginalExtension();
                $identityPath = Storage::disk('google')->putFileAs('identity', $identityFile, $identityName);
                $client->identity_path = $identityPath;
            } else {
                $client->identity_path = $oldIdentity; 
            }

            if ($request->hasFile('residency_path')) {
                if (!empty($oldResidency)) {
                    Storage::disk('google')->delete($oldResidency);
                }
                $residencyFile = $request->file('residency_path');
                $residencyName = 'residency_' . Str::slug($user->name) . '_' . Str::uuid() . '.' . $residencyFile->getClientOriginalExtension();
                $residencyPath = Storage::disk('google')->putFileAs('residency', $residencyFile, $residencyName);
                $client->residency_path = $residencyPath;
            } else {
                $client->residency_path = $oldResidency;
            }

            if ($request->hasFile('company_reg_path')) {
                if (!empty($oldCompanyReg)) {
                    Storage::disk('google')->delete($oldCompanyReg);
                }
                $companyRegFile = $request->file('company_reg_path');
                $companyRegName = 'company_reg_' . Str::slug($user->name) . '_' . Str::uuid() . '.' . $companyRegFile->getClientOriginalExtension();
                $companyRegPath = Storage::disk('google')->putFileAs('company_reg', $companyRegFile, $companyRegName);
                $client->company_reg_path = $companyRegPath;
            } else {
                $client->company_reg_path = $oldCompanyReg;
            }

            $client->save();

            DB::commit();

            return back()->with('success', 'Client information updated successfully.');
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

            return back()->withErrors(['error' => 'Failed to update client information: ' . $e->getMessage()]);
        }
    }

    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClientInformation $client)
    {

        $userId = $client->user_id;

        $userData = ClientInformation::with('user')->where('user_id', $userId)->first();

        if ($client->approved === 1) {

            return back()->withErrors([
             'available' => "{$client->user->name} cannot be deactivated because they are already approved.",
            ]);

        }

        DB::beginTransaction();

        try {


            if ($client->identity_path) {
                Storage::disk('google')->delete($client->identity_path);
            }

            if ($client->residency_path) {
                Storage::disk('google')->delete($client->residency_path);
            }

            if ($client->company_reg_path) {
                Storage::disk('google')->delete($client->company_reg_path);
            }

            ClientRate::where('client_information_id', $client->id)->delete();

            $agreementUpload = AgrementUpload::where('user_id', $client->user_id)->first();
            if ($agreementUpload) {
                if ($agreementUpload->agreement) {
                    Storage::disk('google')->delete($agreementUpload->agreement);
                }
                $agreementUpload->delete();
            }
                    

            $client->delete();

            DB::commit();

            return back()->with('success', 'Client and related data deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to delete client: ' . $e->getMessage()]);
        }


    }

    /**
     * Restore a soft-deleted client.
     */
    public function restore($id)
    {
        $client = ClientInformation::withTrashed()->findOrFail($id);
        $client->restore();

        return back()->with('success', 'Client restored successfully.');
    }

    /**
     * Permanently delete a client.
     */
    public function forceDelete($id)
    {
        DB::beginTransaction();

        try {
            $client = ClientInformation::withTrashed()->findOrFail($id);

            if ($client->identity_path) {
                Storage::disk('public')->delete($client->identity_path);
            }

            if ($client->residency_path) {
                Storage::disk('public')->delete($client->residency_path);
            }

            ClientRate::where('client_information_id', $client->id)->delete();

            $client->forceDelete();

            DB::commit();

            return back()->with('success', 'Client permanently deleted.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to permanently delete client: ' . $e->getMessage()]);
        }
    }

    /**
     * Approve a client approved.
     */
    public function approve(Request $request, ClientInformation $client)
    {
        $agreement = AgrementUpload::select('id', 'user_id')
                    ->where('user_id', $client->user_id)
                    ->where('status', 'approved')
                    ->first();

        if (is_null($agreement)) {

            return back()->withErrors([
                    'available' => "You can not approve this user, They do not have Agreement file",
                ]);


        }

        $client->update([
            'approved' => 1,
        ]);

        $user = $client->user;

        $userRole = Role::where('name', 'User')->first();
        $pendingRole = Role::where('name', 'Pending User')->first();

        if ($userRole) {
            if ($pendingRole) {
                $user->roles()->detach($pendingRole->id);
            }

            $user->roles()->syncWithoutDetaching([$userRole->id]);
        }


        $clientData = ClientInformation::findOrFail($client->id);

        $categorySlug = Str::slug($clientData->name);

        $bookingData = [
            'id' => $client->id,
            'status' => 'approved',
            'user_name' => $client->user->name,
            'user_email' => $client->user->email
        ];

        $clientData->user->notify(new ClientStatusNotification($bookingData, 'approved', 'user'));

        $admins = User::select('id', 'email')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Super Admin', 'Admin']))
            ->distinct()
            ->get();

        Notification::send($admins, new ClientStatusNotification($bookingData, 'approved', 'admin'));


        return redirect()->back()->with(
            'success',
            "Client Approved Successfully.",
        );



    }

    /**
    * Approve a client deactivated.
    */
    public function deactive(Request $request, ClientInformation $client)
    {

        $client->update([
            'approved' => 0,
        ]);

        $user = $client->user;

        $userRole = Role::where('name', 'User')->first();
        $pendingRole = Role::firstOrCreate(['name' => 'Pending User']);

        if ($userRole) {
            $user->roles()->detach($userRole->id);
        }

        $user->roles()->syncWithoutDetaching([$pendingRole->id]);

        $clientData = ClientInformation::findOrFail($client->id);
        $categorySlug = Str::slug($clientData->name);

        $bookingData = [
            'id' => $client->id,
            'status' => 'deactivated',
             'user_name' => $client->user->name,
            'user_email' => $client->user->email
        ];

        $clientData->user->notify(new ClientStatusNotification($bookingData, 'deactivated', 'user'));


        $admins = User::select('id', 'email')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Super Admin', 'Admin']))
            ->distinct()
            ->get();

        Notification::send($admins, new ClientStatusNotification($bookingData, 'deactivated', 'admin'));



        return redirect()->back()->with(
            'success',
            "Client Deactivated Successfully.",
        );




    }



}
