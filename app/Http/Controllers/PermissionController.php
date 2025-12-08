<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Inertia\Inertia;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $permissions = Permission::when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString(); 


        return Inertia::render('Permissions/AdminPermissions', [
            'permissions' => $permissions,
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
         return Inertia::render('Permissions/CreatePermission');
    }

    /**
     * Store the newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        $permission = Permission::create(['name' => $validated['name']]);

        $superAdmin = Role::where('name', 'Super Admin')->first();
        $superAdmin?->permissions()->syncWithoutDetaching([$permission->id]);

        return redirect()->route('admin.permissions')->with('success', 'Permissions created successfully.');
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
    public function edit(Permission $permission)
    {
        return Inertia::render('Permissions/EditPermission',[
             'permissions' => $permission,
        ]);
    }

    /**
     * Update the resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update($validated);

        return redirect()->route('admin.permissions')->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return back()->with('success', 'A permission has been deleted successfully.');
    }
}
