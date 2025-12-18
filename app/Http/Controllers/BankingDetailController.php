<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\BankingDetail;
use Illuminate\Validation\Rule;

class BankingDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        
        $search = $request->input('search');

        $banking = BankingDetail::when($search, function ($query, $search) {
            $query->where('banking_name', 'like', "%{$search}%");
        })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Banking/IndexBank', [
            'banking' => $banking,
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
        return Inertia::render('Banking/CreateBank');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $validated = $request->validate([
                        'account_number' => [
                            'required',
                            function ($attribute, $value, $fail) {
                                $digits = str_replace(' ', '', $value);
                                if (!ctype_digit($digits) || strlen($digits) !== 16) {
                                    $fail('The card number must be exactly 16 digits.');
                                }
                            },
                        ],
                        'bank_name'      => 'required|string|max:255',
                        'account_holder' => [
                            'required',
                            'string',
                            'max:255',
                            Rule::unique('banking_details')
                                ->where(fn ($query) => $query->where('bank_name', request('bank_name')))
                                ->ignore($banking->id ?? null),
                        ],
                        'branch_code'    => 'required|digits_between:3,6',
                        'swift_code'     => 'nullable|string|regex:/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/',
                        'iban'           => 'nullable|string|regex:/^[A-Z0-9]{15,34}$/',
                    ]);


        $banking = BankingDetail::create($validated);

        return redirect()->back()->with('success', 'Banking details saved succefully');
    }

    /**
     * Display the specified resource.
     */
    public function show(BankingDetail $bankingDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BankingDetail $banking)
    {   
        // dd($banking);
        return Inertia::render('Banking/EditBank',[
            'banking' => $banking
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankingDetail $banking)
    {
        $validated = $request->validate([
                        'account_number' => [
                            'required',
                            function ($attribute, $value, $fail) {
                                $digits = str_replace(' ', '', $value);
                                if (!ctype_digit($digits) || strlen($digits) !== 16) {
                                    $fail('The card number must be exactly 16 digits.');
                                }
                            },
                        ],
                        'bank_name'      => 'required|string|max:255',
                        'account_holder' => 'required|string|max:255',
                        'branch_code'    => 'required|digits_between:3,6',
                        'swift_code'     => 'nullable|string|regex:/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/',
                        'iban'           => 'nullable|string|regex:/^[A-Z0-9]{15,34}$/',
                    ]);

        $banking->update($validated);

        return redirect()->back()->with('success', 'Banking details updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankingDetail $banking)
    {
        $banking->delete();

        return redirect()->back()->with('success', 'Banking Detail Deleted Successfully.');
    }
}
