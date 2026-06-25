<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        // If already logged in, redirect to dashboard
        if (session('is_logged_in')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'mobile' => 'required'
        ]);

        if ($request->mobile === '9106798459') {
            session(['is_logged_in' => true]);
            return redirect()->route('dashboard')->with('success', 'Logged in successfully!');
        }

        return back()->with('error', 'Invalid Mobile Number. Access Denied.');
    }

    public function logout()
    {
        session()->forget('is_logged_in');
        return redirect()->route('login')->with('success', 'Logged out securely.');
    }
}
