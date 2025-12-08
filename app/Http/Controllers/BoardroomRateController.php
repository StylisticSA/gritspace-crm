<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BoardroomRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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


        $users = User::with('roles')
                          ->whereHas('roles', function ($query) {
                              $query->whereIn(DB::raw('LOWER(name)'), ['user', 'users']);
                          })->select('id', 'name')
                          ->get();

        $locations = Location::select('id', 'name', 'address', 'city')->get();


        // $categories = Category::select('id', 'name')->get();


        $boardrooms = Boardroom::all();





        return Inertia::render('Clients/ClientRates/Closed/CreateClosed', [
                    'closedoffices'     => $closedoffices,
                    'locations'         => $locations,
                    'users'             => $users,
                    // 'categories'        => $categories,

                    'boardrooms'        => $boardrooms,

                ]);

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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
