<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

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
        $loginType = $request->input('login_type');
        if (!in_array($loginType, ['personnel', 'email'], true)) {
            $loginType = $request->filled('email') ? 'email' : 'personnel';
        }

        return $loginType === 'email'
            ? $this->loginWithEmail($request)
            : $this->loginWithIdentityAndPhone($request);
    }

    private function loginWithEmail(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $rateLimitKey = $this->rateLimitKey($request, 'email', strtolower($credentials['email']));
        if ($response = $this->rateLimitResponse($rateLimitKey, 'email')) {
            return $response;
        }

        $remember = $request->boolean('remember');
        $credentials['is_active'] = true;

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            RateLimiter::clear($rateLimitKey);

            LoginLog::create([
                'email'      => strtolower($credentials['email']),
                'user_id'    => Auth::id(),
                'ip_address' => $this->resolveClientIp($request),
                'user_agent' => $request->userAgent(),
                'status'     => 'success',
            ]);

            return redirect()->intended('/');
        }

        RateLimiter::hit($rateLimitKey, 60);
        $user = User::where('email', $credentials['email'])->first();
        LoginLog::create([
            'email'          => strtolower($credentials['email']),
            'user_id'        => $user?->id,
            'ip_address'     => $this->resolveClientIp($request),
            'user_agent'     => $request->userAgent(),
            'status'         => 'failed',
            'failure_reason' => 'E-posta, şifre veya hesap durumu geçersiz',
        ]);

        return back()->withErrors([
            'email' => 'E-posta veya şifre hatalı.',
        ])->withInput(['email' => $request->email, 'login_type' => 'email']);
    }

    private function loginWithIdentityAndPhone(Request $request)
    {
        $credentials = $request->validate([
            'identity_no' => 'required|string|max:30',
            'phone' => 'required|string|max:30',
        ]);

        $identityNumber = User::normalizeIdentityNumber($credentials['identity_no']);
        $phone = User::normalizeTurkishPhone($credentials['phone']);
        $rateLimitKey = $this->rateLimitKey(
            $request,
            'personnel',
            preg_replace('/\D+/', '', $credentials['identity_no'])
        );

        if ($response = $this->rateLimitResponse($rateLimitKey, 'identity_no')) {
            return $response;
        }

        if (!$identityNumber || !$phone) {
            RateLimiter::hit($rateLimitKey, 60);

            return back()->withErrors([
                'identity_no' => 'TC kimlik numarası veya telefon numarası hatalı.',
            ])->withInput([
                'identity_no' => $request->identity_no,
                'phone' => $request->phone,
                'login_type' => 'personnel',
            ]);
        }

        $user = User::where('identity_no_hash', User::identityHash($identityNumber))->first();
        $authenticated = $user
            && $user->is_active
            && $user->phone_normalized
            && hash_equals((string) $user->phone_normalized, $phone);

        $logIdentity = 'personel:' . str_repeat('*', 7) . substr($identityNumber, -4);

        if ($authenticated) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            RateLimiter::clear($rateLimitKey);

            LoginLog::create([
                'email' => $logIdentity,
                'user_id' => $user->id,
                'ip_address' => $this->resolveClientIp($request),
                'user_agent' => $request->userAgent(),
                'status' => 'success',
            ]);

            return redirect()->intended('/');
        }

        RateLimiter::hit($rateLimitKey, 60);
        LoginLog::create([
            'email' => $logIdentity,
            'user_id' => $user?->id,
            'ip_address' => $this->resolveClientIp($request),
            'user_agent' => $request->userAgent(),
            'status' => 'failed',
            'failure_reason' => 'TC, telefon veya hesap durumu geçersiz',
        ]);

        return back()->withErrors([
            'identity_no' => 'TC kimlik numarası veya telefon numarası hatalı.',
        ])->withInput([
            'identity_no' => $request->identity_no,
            'phone' => $request->phone,
            'login_type' => 'personnel',
        ]);
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

    private function rateLimitKey(Request $request, string $type, string $identifier): string
    {
        return 'login:' . hash('sha256', $type . '|' . strtolower($identifier) . '|' . $request->ip());
    }

    private function rateLimitResponse(string $key, string $errorField)
    {
        if (!RateLimiter::tooManyAttempts($key, 5)) {
            return null;
        }

        $seconds = RateLimiter::availableIn($key);

        return back()->withErrors([
            $errorField => "Çok fazla başarısız deneme yapıldı. {$seconds} saniye sonra tekrar deneyin.",
        ])->withInput(request()->except('password'));
    }
}
