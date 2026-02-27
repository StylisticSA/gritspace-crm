<?php

namespace App\Http\Controllers;

use App\Models\FreeHours;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FreeHoursController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $hours = FreeHours::with(['user', 'boardroom'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();


        return Inertia::render('Hours/HoursIndex', [
            'hours' => $hours,
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(FreeHours $freeHours)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FreeHours $freeHours)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FreeHours $freeHours)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FreeHours $freeHours)
    {
        //
    }
}
