<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.ulogin');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $login_type = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email_user' : 'username_user';

        if (Auth::attempt([$login_type => $request->login, 'password' => $request->password])) {
            return redirect()->intended('/dashboard'); // sesuaikan path dashboard
        }

        return back()->withErrors([
            'login' => 'Login gagal! Email/Username atau password salah.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('ulogin');
    }
}
