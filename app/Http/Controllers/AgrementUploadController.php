<?php

namespace App\Http\Controllers;

use App\Models\AgrementUpload;
use App\Models\ClientInformation;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

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

        

        $agreements->getCollection()->transform(function ($agreement) {
          
            if ($agreement->agreement) {
                $disk = Storage::disk('google');
                
                $agreement->agreement = $disk->exists($agreement->agreement) 
                    ? $disk->url($agreement->agreement) 
                    : null;
            } else {
                $agreement->agreement = null;
            }

            return $agreement;
        });

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
        $users = User::whereDoesntHave('roles', function($query){
            $query->whereIn('name', ['admin', 'super admin']);
        })->get();

        $locations = Location::select(['id','name'])->get();
      
        return Inertia::render('Clients/Agreements/AgreementsCreate', [
            'users' => $users,
            'locations' => $locations,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */

    // public function store(Request $request)
    // {
    //     // dd($request);

    //     $validated = $request->validate([
    //         'user_id'     => 'nullable',
    //         'location_id' => 'required|exists:locations,id',
    //         'agreement'   => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048'
    //     ]);

    //     $validated['user_id'] = Auth::id();

    //     $clientInfo = ClientInformation::where('user_id', $validated['user_id'])->first();

    //     if (!$clientInfo) {
    //         return back()->withErrors([
    //             'agreement' => 'There is no user in the system, please fill in your company information.',
    //         ]);
    //     }

    //     $existing = AgrementUpload::where('user_id', $validated['user_id'])->first();

    //     if ($existing?->agreement && $request->hasFile('agreement')) {
    //         return back()->withErrors([
    //             'agreement' => 'An agreement document already exists for this user.',
    //         ]);
    //     }

    //     $agreementPath = null;

    //     if ($request->hasFile('agreement') && empty($existing?->agreement_path)) {
    //         $agreementFile = $request->file('agreement');
    //         $originalName = pathinfo($agreementFile->getClientOriginalName(), PATHINFO_FILENAME);
    //         $extension = $agreementFile->getClientOriginalExtension();

    //         $user = User::select('id', 'name')->where('id', Auth::id())->first();
    //         $location = Location::select('id', 'name')->where('id', $validated['location_id'])->first();

    //         $fileName = Str::slug($user->name) . '_' .
    //             Str::slug($location->name) . '_' .
    //             Str::uuid() . '.' . $extension;

    //         $agreementPath = $agreementFile->storeAs('agreements', $fileName, 'public');

    //         if (!$agreementPath) {
    //             throw new \Exception('Failed to store agreement document.');
    //         }
    //     }

    //     AgrementUpload::create([
    //         'user_id'       => $validated['user_id'],
    //         'location_id'   => $validated['location_id'],
    //         'agreement'     => $agreementPath,
    //         'status'        => 'pending'
    //     ]);

    //     return redirect()->back()->with('success', 'The file uploaded Successfully!');
    // }

    public function store(Request $request)
    {
        

        $validated = $request->validate([
            'user_id'     => 'nullable',
            'location_id' => 'required|exists:locations,id',
            'agreement'   => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        if($request->user_id){
            $clientInfo = ClientInformation::where('user_id', $request->user_id)->first();
        } else {

            $validated['user_id'] = Auth::id();
      
            $clientInfo = ClientInformation::where('user_id', $validated['user_id'])->first();
        }

        if (!$clientInfo) {
            return back()->withErrors([
                'agreement' => 'There is no user in the system, please fill in your company information.',
            ]);
        }

         if($request->user_id){
            $existing = AgrementUpload::where('user_id', $request->user_id)->first();
         } else {
            $existing = AgrementUpload::where('user_id', $validated['user_id'])->first();
         }

        if ($existing?->agreement && $request->hasFile('agreement')) {
            return back()->withErrors([
                'agreement' => 'An agreement document already exists for this user.',
            ]);
        }

        $agreementPath = null;

        DB::beginTransaction();

        try {
            if ($request->hasFile('agreement') && empty($existing?->agreement_path)) {
                $agreementFile = $request->file('agreement');
                $originalName = pathinfo($agreementFile->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $agreementFile->getClientOriginalExtension();

                if($request->user_id){  
                    $user = User::select('id', 'name')->where('id', $request->user_id)->first();
                } else {
                    $user = User::select('id', 'name')->where('id', Auth::id())->first();
                }

                $location = Location::select('id', 'name')->where('id', $validated['location_id'])->first();

                $fileName = Str::slug($user->name) . '_' .
                    Str::slug($location->name) . '_' .
                    Str::uuid() . '.' . $extension;

                $agreementPath = Storage::disk('google')->putFileAs('agreements', $agreementFile, $fileName);

                if (!$agreementPath) {
                    throw new \Exception('Failed to store agreement document.');
                }
            }

            AgrementUpload::create([
                'user_id'     => $validated['user_id'],
                'location_id' => $validated['location_id'],
                'agreement'   => $agreementPath,
                'status'      => 'pending'
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'The file uploaded Successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            if (!empty($agreementPath)) {
                Storage::disk('google')->delete($agreementPath);
            }

            return back()->withErrors(['error' => 'Failed to upload agreement: ' . $e->getMessage()]);
        }
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
        $users = User::whereDoesntHave('roles', function($query){
            $query->whereIn('name', ['admin', 'super admin']);
        })->get();

        $locations = Location::select('id', 'name')->get();

        $agreements = AgrementUpload::where('id', $id)->first();
        
        if ($agreements->agreement) {
       
            $disk = Storage::disk('google');
            
            $agreements->agreement = $disk->exists($agreements->agreement) 
                ? $disk->url($agreements->agreement) 
                : null;
        } else {
            $agreements->agreement = null;
        }

        return Inertia::render('Clients/Agreements/AgreementEdit', [
                    'agreement' => $agreements,
                    'locations' => $locations,
                    'users' => $users,

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

        if($request->user_id){

            $user = User::select('id', 'name')->where('id', $request->user_id)->first();
        } else {
            $user = User::select('id', 'name')->where('id', $validated['user_id'])->first();
        }

        $location = Location::select('id', 'name')->where('id', $validated['location_id'])->first();

        if ($request->hasFile('agreement')) {

            if ($agreement->agreement && Storage::disk('google')->exists($agreement->agreement)) {
                Storage::disk('google')->delete($agreement->agreement);
            }

            $agreementFile = $request->file('agreement');
            $extension = $agreementFile->getClientOriginalExtension();

            $fileName = Str::slug($user->name) . '_' .
                        Str::slug($location->name) . '_' .
                        Str::uuid() . '.' . $extension;

            $agreementPath = Storage::disk('google')->putFileAs('agreements', $agreementFile, $fileName);

            if (!$agreementPath) {
                return back()->withErrors(['error' => 'Failed to upload to Google Drive.']);
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
        
        // dd($agreement);
        if ($agreement->status === 'approved') {

            return back()->withErrors([
                            'error' => 'You can not delete this file, change the status to Pending first.'
                        ]);

        }

        DB::beginTransaction();

        try {

            if ($agreement->agreement && Storage::disk('google')->exists($agreement->agreement)) {
                Storage::disk('google')->delete($agreement->agreement);
            }

            $agreement->user()->detach(); 

            $agreement->delete();

            DB::commit();

            return back()->with('success', 'Agreement file deleted Successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Failed to delete the agreement file: ' . $e->getMessage()]);
        }


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

        return redirect()->back()->with('success');
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

