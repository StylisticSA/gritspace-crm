<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Notifications\NewUserRegistered;
use App\Traits\SearchFilter\HasSearchFilter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class ManagerController extends Controller
{
    use HasSearchFilter; 
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $search = $this->getSearch($request);

        $users = User::with('roles')
                ->when(!auth()->user()->hasRole('super admin'), function ($query) {
                    $query->whereDoesntHave('roles', function ($q) {
                        $q->where('name', 'super admin');
                    });
                })
                 ->when($search, function ($query, $search) {
                    $query->where(function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->orderBy('created_at')
                ->paginate(10)
                ->withQueryString();

        return Inertia::render('Manage/ManageUsers', [
            'users' => $users,
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
        $this->authorize('create', User::class);

        $roles = Role::with('permissions')
                ->select('id', 'name')
                ->when(!auth()->user()->hasRole('super admin'), function ($query) {
                    $query->where('name', '!=', 'Super Admin');
                })
                ->orderBy('name', 'asc')
                ->get();


        $permissions = Permission::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return Inertia::render('Manage/CreateUser', [
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => [
                        'required',
                        'confirmed',
                        Password::min(8)
                            ->mixedCase()
                            ->letters()
                            ->numbers(),
                        ],
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);


        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(), 
        ]);


         if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        if (!empty($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        }

        $admins = User::role(['Admin', 'Super Admin'])->get()->unique('id');

        $admins->each(fn ($admin) => $admin->notify(new NewUserRegistered($user)));

        return redirect(route('admin.manage.user'))->with('success', 'User has been created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('update', User::class);

        $user->load('roles', 'roles.permissions')->makeHidden(['password']);

        return Inertia::render('Manage/EditUser', [
               'user' => $user->only(['id', 'name', 'email']),
               'roles' => Role::with('permissions')
                   ->select('id', 'name')
                   ->when(!auth()->user()?->hasRole('super admin'), function ($query) {
                       $query->whereRaw('LOWER(name) != ?', ['super admin']);
                   })
                   ->orderBy('name')
                   ->get(),
               'permissions' => Permission::select('id', 'name')->orderBy('name')->get(),
               'selectedRoles' => $user->roles->pluck('name'),
               'selectedPermissions' => $user->roles
                   ->flatMap->permissions
                   ->pluck('name')
                   ->unique()
                   ->values(),
           ]);



    }

    /**
     * Update the user's password.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', User::class);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'      => ['nullable', 'confirmed', Password::min(8)->mixedCase()->letters()->numbers(),],
            'roles'         => ['array'],
            'roles.*'       => ['string', 'exists:roles,name'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        if (isset($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        }

        return redirect()->route('admin.manage.user')->with('success', 'User roles and permissions updated.');
    }


    /**
     * Remove the resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', User::class);

        $user->update(['is_active' => false]);

        $user->delete();

        $user->forgetCachedPermissions();

        return back()->with('success', 'User has been disabled successfully.');
    }

}
