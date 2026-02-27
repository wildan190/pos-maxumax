<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($credentials['email'] === 'admin@maxumax.my' && $credentials['password'] === 'Admin#1234') {
            // Log in the first user from the database directly bypassing the password check, 
            // since this is requested as a static login fallback.
            $user = \App\Models\User::firstOrCreate(
                ['email' => 'admin@maxumax.my'],
                ['name' => 'Admin Maxumax', 'password' => 'Admin#1234']
            );

            Auth::login($user);

            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
