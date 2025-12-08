<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use App\Notifications\NewUserRegistered;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class ManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $search = $request->input('search');

        $users = User::with('roles')
                ->when(!auth()->user()->hasRole('super admin'), function ($query) {
                    $query->whereDoesntHave('roles', function ($q) {
                        $q->where('name', 'super admin');
                    });
                })
                ->when($search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->orderByDesc('created_at')
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
        ]);


        $roles = Role::whereIn('name', $validated['roles'] ?? [])->get();
        $user->roles()->sync($roles->pluck('id'));

        $permissions = Permission::whereIn('name', $validated['permissions'] ?? [])->pluck('id');
        $user->permissions()->sync($permissions);


        $admins = User::withRole('Admin')
                        ->get()
                        ->merge(User::withRole('Super Admin')->get())
                        ->unique('id');

        $admins->each(fn ($admin) => $admin->notify(new NewUserRegistered($user)));

        return redirect(route('admin.manage.user'))->with('success', 'User has been created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);


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


        $this->authorize('update', $user);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'      => ['nullable', 'confirmed', Rules\Password::defaults()],
            'roles'         => ['array'],
            'roles.*'       => ['string', 'exists:roles,name'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $roles = Role::whereIn('name', $validated['roles'] ?? [])->get();
        $user->roles()->sync($roles->pluck('id'));

        return redirect()->route('admin.manage.user')->with('success', 'User roles and permissions updated.');
    }


    /**
     * Remove the resource from storage.
     */

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->roles()->detach();
        $user->permissions()->detach();

        $user->update(['is_active' => false]);

        $user->delete();

        return back()->with('success', 'User has been disabled successfully.');
    }

}
