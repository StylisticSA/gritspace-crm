<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Inertia\Inertia;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $roles = Role::with('permissions') 
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Roles/AdminRoles', [
            'roles' => $roles,
            'filters' => [
                'search' => $search,
            ]
        ]);
    }
    /**
     * Show the form for creating the resource.
     */
    public function create()
    {
        $permissions = Permission::select('id','name')
                ->orderBy('name','asc')->get();

        return Inertia::render('Roles/CreateRole',[
            'permissions' => $permissions
        ]);
    }

    /**
     * Store the newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
        ]);

        $role = role::create($validated);

        return redirect()->route('admin.roles')->with('success', 'Role created successfully.');
    }

    /**
     * Display the resource.
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the resource.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::select('id','name')->orderBy('name','asc')->get();

        $role->load('permissions');

        return Inertia::render('Roles/EditRole', [
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update the resource in storage.
     */
    public function update(Request $request, Role $role)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);


        $role->update(['name' => $validated['name']]);

        $permissions = Permission::whereIn('name', $validated['permissions'] ?? [])->pluck('id');
        $role->permissions()->sync($permissions);

        return redirect()->route('admin.roles')->with('success', 'A role has been updated successfully.');
    }

    /**
     * Remove the resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return back()->with('success', 'A role has been deleted successfully.');
    }
}
