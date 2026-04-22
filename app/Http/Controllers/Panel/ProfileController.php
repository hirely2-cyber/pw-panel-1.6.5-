<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth('panel')->user()->loadMissing('role');
        return view('panel.profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth('panel')->user();
        $data = $request->validate([
            'email'        => ['required', 'email', 'max:190', "unique:gm_users,email,{$user->id}"],
            'display_name' => ['nullable', 'string', 'max:100'],
        ]);
        $user->update($data);
        return back()->with('ok', 'Profil diperbarui.');
    }

    public function changePassword(Request $request)
    {
        $user = auth('panel')->user();
        $data = $request->validate([
            'current'  => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        if (! Hash::check($data['current'], $user->password)) {
            return back()->withErrors(['current' => 'Password sekarang salah.']);
        }
        $user->update(['password' => Hash::make($data['password'])]);
        return back()->with('ok', 'Password berhasil diganti.');
    }
}
