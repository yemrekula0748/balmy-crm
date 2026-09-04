<?php

namespace App\Http\Controllers;

use App\Models\PhishingTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhishingSimulationPublicController extends Controller
{
    public function legacyRedirect(string $token)
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{64}$/', $token) === 1, 404);

        return redirect()
            ->route('phishing-simulation.show', $token, 301)
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function show(string $token)
    {
        $target = $this->findTarget($token);

        if (!$target->campaign->isOpen()) {
            return $this->privateResponse(view('public.phishing.expired'), 410);
        }

        return $this->privateResponse(view('public.phishing.login', compact('target')));
    }

    public function opened(Request $request, string $token)
    {
        $this->rejectUnexpectedFields($request);
        $target = $this->findTarget($token);

        if (!$target->campaign->isOpen()) {
            return response()->noContent(410);
        }

        DB::transaction(function () use ($target) {
            $locked = PhishingTarget::query()->lockForUpdate()->findOrFail($target->id);
            $locked->first_clicked_at ??= now();
            $locked->last_clicked_at = now();
            $locked->click_count++;
            $locked->save();
        });

        return response()->noContent()
            ->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function attempt(Request $request, string $token)
    {
        // Bu uç nokta yalnızca CSRF alanını kabul eder. Kullanıcı adı/parola gibi
        // bir alan yanlışlıkla eklenirse istek reddedilir ve hiçbir içerik kaydedilmez.
        $this->rejectUnexpectedFields($request);
        $target = $this->findTarget($token);

        if (!$target->campaign->isOpen()) {
            return $this->privateResponse(view('public.phishing.expired'), 410);
        }

        DB::transaction(function () use ($target) {
            $locked = PhishingTarget::query()->lockForUpdate()->findOrFail($target->id);
            $locked->first_credential_attempted_at ??= now();
            $locked->last_credential_attempted_at = now();
            $locked->credential_attempt_count++;
            $locked->save();
        });

        return $this->privateResponse(view('public.phishing.reveal', [
            'redirectUrl' => $target->campaign->redirect_url,
        ]));
    }

    private function findTarget(string $token): PhishingTarget
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{64}$/', $token) === 1, 404);

        return PhishingTarget::query()
            ->with('campaign')
            ->where('token', $token)
            ->firstOrFail();
    }

    private function rejectUnexpectedFields(Request $request): void
    {
        $unexpected = array_diff(array_keys($request->all()), ['_token']);
        abort_if($unexpected !== [], 422, 'Bu form bilgi içeriği kabul etmez.');
    }

    private function privateResponse($content, int $status = 200)
    {
        return response($content, $status)
            ->header('Cache-Control', 'no-store, private')
            ->header('Pragma', 'no-cache')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }
}
