<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('users.index', [
            'users'     => $users,
            'pageTitle' => 'User',
        ]);
    }

    public function create()
    {
        return view('users.create', [
            'pageTitle' => 'Tambah User',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_user' => 'required|string|max:255',
            'wa_user'   => 'nullable|string|max:20',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:3',
            'role'      => 'required|in:admin,kasir',
        ]);

        User::create([
            'nama_user' => $request->nama_user,
            'wa_user'   => $request->wa_user,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('users.edit', [
            'user'      => $user,
            'pageTitle' => 'Edit User',
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama_user' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id_user . ',id_user',
            'wa_user'   => 'nullable|string|max:20',
            'role'      => 'required|in:admin,kasir',
        ]);

        $user->update([
            'nama_user' => $request->nama_user,
            'email'     => $request->email,
            'wa_user'   => $request->wa_user,
            'role'      => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
