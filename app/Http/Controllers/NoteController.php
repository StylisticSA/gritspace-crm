<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $this->authorize('viewAny', User::class);

        $search = $request->input('search');

        $notes = Note::with('user')
                ->orderByDesc('created_at')
                ->paginate(10)
                ->withQueryString();

        return Inertia::render('Notes/IndexNotes', [
            'notes' => $notes,
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
                        $query->whereIn(DB::raw('LOWER(name)'), ['user', 'users','admin','admins']);
                    })->select('id', 'name')
                    ->get();


        return Inertia::render('Notes/CreateNotes', [
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
               'user_id' => 'nullable|exists:users,id',
               'office_name' => 'required|string',
               'content' => [
                    'required',
                    'string',
                    Rule::unique('notes')->where(
                        fn ($query) =>
                        $query->where('office_name', $request->office_name)
                    ),
                ],
               'is_visible_to_user' => 'boolean',
           ]);


        $validated['created_by'] = Auth::user()->name;

        Note::create($validated);

        return redirect()->back()->with('success', 'Note saved successfully.');

    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Note $note)
    {

        $users = User::with('roles')
                           ->whereHas('roles', function ($query) {
                               $query->whereIn(DB::raw('LOWER(name)'), ['user', 'users','admin','admins']);
                           })->select('id', 'name')
                           ->get();


        return Inertia::render('Notes/EditNotes', [
            'users' => $users,
            'note'  => $note->load(['user'])
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Note $note)
    {

        $validated = $request->validate([
                'user_id' => 'nullable|exists:users,id',
                'office_name' => 'required|string',
                'content' => [
                    'required',
                    'string',
                    Rule::unique('notes')
                        ->where(fn ($query) => $query->where('office_name', $request->office_name))
                        ->ignore($note->id),
                ],
                'is_visible_to_user' => 'boolean',
            ]);

        $validated['created_by'] = Auth::user()->name;

        $note->update($validated);

        return redirect()->back()->with('success', 'Note updated successfully.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        //
    }
}
