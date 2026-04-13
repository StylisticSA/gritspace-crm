<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Traits\SearchFilter\HasSearchFilter;

class PermissionController extends Controller
{
    use HasSearchFilter; 
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $this->getSearch($request);

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

        $permission = Permission::create(['name' => strtolower($validated['name']) ] );

        $superAdmin = Role::where('name', 'super admin')->first();
    
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permission);
        }

        return redirect()->route('admin.permissions')->with('success', 'Permissions created successfully.');
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

        $permission->update(['name' =>  strtolower($validated['name'])]);

        PermissionRegistrar::class->forgetCachedPermissions();

        return redirect()->route('admin.permissions')->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        PermissionRegistrar::class->forgetCachedPermissions();
        
        return back()->with('success', 'A permission has been deleted successfully.');
    }
}
