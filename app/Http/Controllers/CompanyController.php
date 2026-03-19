<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $companies = Company::when($search, function ($query, $search) {
            $query->where('company_name', 'like', "%{$search}%");
        })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();
            
        return Inertia::render('Company/IndexCompany',[
            'companies' => $companies,
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
         return Inertia::render('Company/CreateCompany');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
                    'company_name' => 'required|string|max:255',
                    'vat_no'       => 'required|string|max:50',
                    'reg_no'       => 'nullable|string|max:50',
                    'address'      => 'required|string|max:255',
                    'email'        => 'required|email|max:255|unique:companies,email',
                    'phone'        => 'required|string|regex:/^\+?[0-9]{7,15}$/'
                ]);

         Company::create($validated);

         return redirect()->back()->with('success', 'Company has been saved successfully.');


    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        return Inertia::render('Company/EditCompany', [
            'companies' => $company
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'vat_no'       => 'nullable|string|max:50',
            'reg_no'       => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:255',
            'email'        => [
                'required',
                'email',
                'max:255',
                Rule::unique('companies', 'email')->ignore($company->id),
            ],
            'phone' => 'required|string|regex:/^\+?[0-9]{7,15}$/'
        ]);

        $company->update($validated);

        return redirect()->back()->with('success', 'Company has been updated successfully.');


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->back()->with('success', 'Company has been deleted successfully.');

    }
}
