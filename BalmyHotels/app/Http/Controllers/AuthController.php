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
                'ip_address' => $this->resolveClientIp($request),
                'user_agent' => $request->userAgent(),
                'status'     => 'success',
            ]);

            return redirect()->intended('/');
        }

        $user = User::where('email', $credentials['email'])->first();
        LoginLog::create([
            'email'          => $credentials['email'],
            'user_id'        => $user?->id,
            'ip_address'     => $this->resolveClientIp($request),
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

    private function resolveClientIp(Request $request): ?string
    {
        foreach (['CF-Connecting-IP', 'True-Client-IP', 'X-Real-IP'] as $header) {
            $ip = $this->normalizeIp($request->headers->get($header));

            if ($ip) {
                return $ip;
            }
        }

        $forwardedFor = $request->headers->get('X-Forwarded-For');
        if ($forwardedFor) {
            foreach (explode(',', $forwardedFor) as $candidate) {
                $ip = $this->normalizeIp($candidate);

                if ($ip) {
                    return $ip;
                }
            }
        }

        return $this->normalizeIp($request->ip()) ?? $request->ip();
    }

    private function normalizeIp(?string $ip): ?string
    {
        $ip = trim((string) $ip);

        if ($ip === '') {
            return null;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        if (preg_match('/^(\d{1,3}(?:\.\d{1,3}){3}):\d+$/', $ip, $matches)
            && filter_var($matches[1], FILTER_VALIDATE_IP)) {
            return $matches[1];
        }

        return null;
    }
}
