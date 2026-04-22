<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Panel\GmRole;
use App\Models\Panel\GmUser;
use App\Models\Panel\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GmUserController extends Controller
{
    public function index()
    {
        $this->authorize_('panel.users');
        $users = GmUser::with('role')->orderBy('username')->paginate(25);
        $roles = GmRole::orderBy('name')->get();
        return view('panel.gm.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $this->authorize_('panel.users');
        $data = $request->validate([
            'username'     => ['required', 'string', 'max:64', 'unique:gm_users,username'],
            'email'        => ['required', 'email', 'max:190', 'unique:gm_users,email'],
            'display_name' => ['nullable', 'string', 'max:100'],
            'password'     => ['required', 'string', 'min:8'],
            'gm_role_id'   => ['required', 'exists:gm_roles,id'],
            'is_active'    => ['sometimes', 'boolean'],
        ]);

        $data['password']  = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);
        GmUser::create($data);

        return back()->with('ok', 'GM user dibuat.');
    }

    public function update(Request $request, GmUser $user)
    {
        $this->authorize_('panel.users');
        $data = $request->validate([
            'email'        => ['required', 'email', 'max:190', "unique:gm_users,email,{$user->id}"],
            'display_name' => ['nullable', 'string', 'max:100'],
            'gm_role_id'   => ['required', 'exists:gm_roles,id'],
            'is_active'    => ['sometimes', 'boolean'],
            'password'     => ['nullable', 'string', 'min:8'],
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $data['is_active'] = $request->boolean('is_active', false);

        $user->update($data);

        return back()->with('ok', 'GM user diperbarui.');
    }

    public function destroy(GmUser $user)
    {
        $this->authorize_('panel.users');
        if ($user->id === auth('panel')->id()) {
            return back()->withErrors(['general' => 'Tidak bisa hapus akun sendiri.']);
        }
        $user->delete();
        return back()->with('ok', 'GM user dihapus.');
    }

    public function roles()
    {
        $this->authorize_('panel.users');
        $roles = GmRole::withCount('users')->orderBy('name')->get();
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        return view('panel.gm.roles', compact('roles', 'permissions'));
    }

    public function updateRole(Request $request, GmRole $role)
    {
        $this->authorize_('panel.users');
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100', "unique:gm_roles,name,{$role->id}"],
            'color'    => ['required', 'string', 'max:16'],
            'is_super' => ['sometimes', 'boolean'],
            'permissions' => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);
        $role->update([
            'name'     => $data['name'],
            'color'    => $data['color'],
            'is_super' => $request->boolean('is_super'),
        ]);
        $role->permissions()->sync($data['permissions'] ?? []);
        return back()->with('ok', 'Role diperbarui.');
    }
}
