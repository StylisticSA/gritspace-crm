<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
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

        $role = Role::create($validated);

        return redirect()->route('admin.roles')->with('success', 'Role created successfully.');
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

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('admin.roles')->with('success', 'A role has been updated successfully.');
    }

    /**
     * Remove the resource from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return back()->with('error', 'The Super Admin role cannot be deleted.');
        }

        $role->delete();

        return back()->with('success', 'A role has been deleted successfully.');
    }
}
