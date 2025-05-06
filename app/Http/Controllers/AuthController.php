<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
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

        $login_type = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username_user';

        if (Auth::attempt([$login_type => $request->login, 'password' => $request->password])) {
            $request->session()->regenerate();

            // Redirect sesuai role
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'login' => 'Email/username atau password salah!!!',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/ulogin');
    }
}
