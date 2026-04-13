<?php

namespace App\Http\Controllers;

use App\Traits\SearchFilter\HasSearchFilter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use HasSearchFilter; 
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $this->getSearch($request);

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

        $role = Role::create( ['name' =>  strtolower($validated['name']) ]);

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


        $role->update(['name' =>  strtolower($validated['name']) ]);

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
