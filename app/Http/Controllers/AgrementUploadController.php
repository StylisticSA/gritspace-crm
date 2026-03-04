<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Location;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\AgrementUpload;
use App\Models\ClientInformation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AgrementUploadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $search = $request->input('search');

        $locations = Location::select('id', 'name')->get();

        $agreements = AgrementUpload::with(['user','location'])
                    ->when($search, function ($query, $search) {
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                             ->orWhereHas('location', fn ($l) => $l->where('name', 'like', "%{$search}%"));
                        });
                    })
                    ->orderByDesc('created_at')
                    ->paginate(10)
                    ->withQueryString();


        return Inertia::render('Clients/Agreements/AgreementsIndex', [
            'agreements' => $agreements,
            'locations' => $locations,
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
            'user_id'     => 'nullable',
            'location_id' => 'required|exists:locations,id',
            'agreement'   => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        $validated['user_id'] = Auth::id();

        $clientInfo = ClientInformation::where('user_id', $validated['user_id'])->first();

        if (!$clientInfo) {
            return back()->withErrors([
                'agreement' => 'There is no user in the system, please fill in your company information.',
            ]);
        }

        $existing = AgrementUpload::where('user_id', $validated['user_id'])->first();

        if ($existing?->agreement && $request->hasFile('agreement')) {
            return back()->withErrors([
                'agreement' => 'An agreement document already exists for this user.',
            ]);
        }

        $agreementPath = null;

        if ($request->hasFile('agreement') && empty($existing?->agreement_path)) {
            $agreementFile = $request->file('agreement');
            $originalName = pathinfo($agreementFile->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $agreementFile->getClientOriginalExtension();

            $user = User::select('id', 'name')->where('id', Auth::id())->first();
            $location = Location::select('id', 'name')->where('id', $validated['location_id'])->first();

            $fileName = Str::slug($user->name) . '_' .
                Str::slug($location->name) . '_' .
                Str::uuid() . '.' . $extension;

            $agreementPath = $agreementFile->storeAs('uploads/agreements', $fileName, 'public');

            if (!$agreementPath) {
                throw new \Exception('Failed to store agreement document.');
            }
        }

        AgrementUpload::create([
            'user_id'       => $validated['user_id'],
            'location_id'   => $validated['location_id'],
            'agreement'     => $agreementPath,
            'status'        => 'pending'
        ]);

        return redirect()->back()->with('success', 'The file uploaded Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(AgrementUpload $agrementUpload)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AgrementUpload $agrementUpload, $id)
    {

        $locations = Location::select('id', 'name')->get();

        $record = AgrementUpload::where('id', $id)->first();

        return Inertia::render('Clients/Agreements/AgreementEdit', [
                    'agreement' => $record,
                    'locations' => $locations,

                ]);


    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AgrementUpload $agreement)
    {
        $validated = $request->validate([
            'user_id' => 'required',
            'location_id' => 'required|exists:locations,id',
            'agreement'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $user = User::select('id', 'name')->where('id', $validated['user_id'])->first();
        $location = Location::select('id', 'name')->where('id', $validated['location_id'])->first();

        // dd($request->hasFile('agreement'));

        if ($request->hasFile('agreement')) {
            if ($agreement->agreement && Storage::disk('public')->exists($agreement->agreement)) {

                Storage::disk('public')->delete($agreement->agreement);
            }

            $agreementFile = $request->file('agreement');
            $originalName = pathinfo($agreementFile->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $agreementFile->getClientOriginalExtension();

            $fileName = Str::slug($user->name) . '_' .
                        Str::slug($location->name) . '_' .
                        Str::uuid() . '.' . $extension;

            $agreementPath = $agreementFile->storeAs('uploads/agreements', $fileName, 'public');

            if (!$agreementPath) {
                throw new \Exception('Failed to store agreement document.');
            }

            $agreement->agreement = $agreementPath;
        }

        $agreement->location_id = $validated['location_id'];
        $agreement->save();

        return redirect()->back()->with('success', 'Agreement updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AgrementUpload $agreement)
    {

        if ($agreement->status === 'approved') {

            return back()->withErrors([
                            'error' => 'You can not delete this file, put it to pending first.'
                        ]);

        }

        $agreement->delete();

        return redirect()->back()->with('success', 'Agreement file deleted Successfully');

    }

    /**
    * Approve a closed office.
    */
    public function approve(Request $request, AgrementUpload $agreement)
    {
        // dd($agreement);
        $agreement->update([
            'status' => 'approved',
        ]);

        return redirect()->back()->with('success', 'Agreement file Approved');
    }

    /**
    * Approve a closed office.
    */
    public function pending(Request $request, AgrementUpload $agreement)
    {
        // dd($agreement);
        $agreement->update([
            'status' => 'pending',
        ]);

        return redirect()->back()->with('pending', 'Agreement file is on Pending');
    }
}

