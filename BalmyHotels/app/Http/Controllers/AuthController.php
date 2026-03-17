<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginForm()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('koki.pages.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            LoginLog::create([
                'email'      => $credentials['email'],
                'user_id'    => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status'     => 'success',
            ]);

            return redirect()->intended('/');
        }

        $user = User::where('email', $credentials['email'])->first();
        LoginLog::create([
            'email'          => $credentials['email'],
            'user_id'        => $user?->id,
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
            'status'         => 'failed',
            'failure_reason' => $user ? 'Yanlış şifre' : 'E-posta bulunamadı',
        ]);

        return back()->withErrors([
            'email' => 'E-posta veya şifre hatalı.',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
