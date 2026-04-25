<?php

namespace App\Http\Controllers\Modules;

use App\Models\MikroTikUsageLog;
use App\Services\MikroTikService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MikroTikController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('mikrotik', ['index'], [], [], [], []);
    }

    public function index()
    {
        $mikrotik = new MikroTikService();
        $data = $mikrotik->getDashboardData();

        return view('modules.bilgi_islem.mikrotik.index', compact('data'));
    }

    /**
     * AJAX: Sadece aktif hotspot oturumlarını yenile.
     */
    public function hotspotActive()
    {
        $mikrotik = new MikroTikService();
        if (!$mikrotik->connect()) {
            return response()->json(['error' => 'Bağlantı hatası'], 503);
        }
        $active = $mikrotik->getHotspotActive();
        $mikrotik->disconnect();

        return response()->json($active);
    }

    /**
     * AJAX: DHCP lease listesini yenile.
     */
    public function dhcpLeases()
    {
        $mikrotik = new MikroTikService();
        if (!$mikrotik->connect()) {
            return response()->json(['error' => 'Bağlantı hatası'], 503);
        }
        $leases = $mikrotik->getDhcpLeases();
        $mikrotik->disconnect();

        return response()->json($leases);
    }

    /**
     * AJAX: Sistem kaynakları (CPU/RAM) anlık.
     */
    public function resources()
    {
        $mikrotik = new MikroTikService();
        if (!$mikrotik->connect()) {
            return response()->json(['error' => 'Bağlantı hatası'], 503);
        }
        $res = $mikrotik->getSystemResources();
        $mikrotik->disconnect();

        return response()->json($res);
    }

    /**
     * AJAX: Dönemlere göre (günlük/haftalık/aylık/özel) toplu data kullanımı.
     * ?period=daily|weekly|monthly|custom  &start=YYYY-MM-DD  &end=YYYY-MM-DD
     */
    public function usageStats(Request $request)
    {
        $period = $request->get('period', 'daily');
        [$start, $end] = $this->resolvePeriod(
            $period,
            $request->get('start'),
            $request->get('end')
        );

        $perUser = MikroTikUsageLog::whereBetween('session_started_at', [$start, $end])
            ->selectRaw('username, SUM(bytes_in) as total_in, SUM(bytes_out) as total_out, COUNT(*) as session_count, MAX(last_seen_at) as last_seen')
            ->groupBy('username')
            ->orderByDesc('total_in')
            ->get()
            ->map(fn ($r) => [
                'username'      => $r->username,
                'total_in'      => (int)$r->total_in,
                'total_out'     => (int)$r->total_out,
                'total'         => (int)$r->total_in + (int)$r->total_out,
                'session_count' => (int)$r->session_count,
                'last_seen'     => $r->last_seen,
            ]);

        return response()->json([
            'period'      => $period,
            'start'       => $start->toDateString(),
            'end'         => $end->toDateString(),
            'total_in'    => $perUser->sum('total_in'),
            'total_out'   => $perUser->sum('total_out'),
            'user_count'  => $perUser->count(),
            'users'       => $perUser->values(),
        ]);
    }

    /**
     * AJAX: Belirli bir client'ın zaman aralıklı kullanımı.
     * ?username=xxx  &start=YYYY-MM-DD  &end=YYYY-MM-DD
     */
    public function clientUsage(Request $request)
    {
        $username = $request->get('username');
        if (!$username) {
            return response()->json(['error' => 'username parametresi gerekli'], 422);
        }

        $start = Carbon::parse($request->get('start', Carbon::now()->subDays(29)))->startOfDay();
        $end   = Carbon::parse($request->get('end',   Carbon::now()))->endOfDay();

        $daily = MikroTikUsageLog::where('username', $username)
            ->whereBetween('session_started_at', [$start, $end])
            ->selectRaw('DATE(session_started_at) as date, SUM(bytes_in) as total_in, SUM(bytes_out) as total_out, COUNT(*) as session_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($r) => [
                'date'          => $r->date,
                'total_in'      => (int)$r->total_in,
                'total_out'     => (int)$r->total_out,
                'total'         => (int)$r->total_in + (int)$r->total_out,
                'session_count' => (int)$r->session_count,
            ]);

        return response()->json([
            'username'  => $username,
            'start'     => $start->toDateString(),
            'end'       => $end->toDateString(),
            'total_in'  => $daily->sum('total_in'),
            'total_out' => $daily->sum('total_out'),
            'daily'     => $daily->values(),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Yardımcılar                                                         */
    /* ------------------------------------------------------------------ */

    /** Dönem adına göre başlangıç–bitiş Carbon çifti döner. */
    private function resolvePeriod(string $period, ?string $startRaw, ?string $endRaw): array
    {
        return match ($period) {
            'weekly'  => [Carbon::now()->startOfWeek(Carbon::MONDAY), Carbon::now()->endOfDay()],
            'monthly' => [Carbon::now()->startOfMonth(),              Carbon::now()->endOfDay()],
            'custom'  => [
                Carbon::parse($startRaw ?? 'today')->startOfDay(),
                Carbon::parse($endRaw   ?? 'today')->endOfDay(),
            ],
            default   => [Carbon::today()->startOfDay(), Carbon::now()->endOfDay()], // daily
        };
    }
}
