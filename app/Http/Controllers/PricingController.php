<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Pricing;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Pricing/IndexPricing');
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Pricing $pricing)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pricing $pricing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pricing $pricing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pricing $pricing)
    {
        //
    }
}
