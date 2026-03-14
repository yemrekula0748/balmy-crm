<?php

namespace App\Http\Controllers\Modules;

use App\Models\Branch;
use App\Models\Department;
use App\Models\DoorLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DoorLogReportController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'door_log_reports',
            ['index', 'pdf'],  // index izni
            [],                 // show
            [],                 // create
            [],                 // edit
            []                  // delete
        );
    }

    // ──────────────────────────────────────────────────────────────
    //  Rapor ana sayfa
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        [$data, $filters, $branches, $departments, $page_title] = $this->buildReport($request);

        return view('modules.door_logs.report', compact(
            'data', 'filters', 'branches', 'departments', 'page_title'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    //  PDF (yazdırılabilir sayfa — browser print)
    // ──────────────────────────────────────────────────────────────
    public function pdf(Request $request)
    {
        [$data, $filters, $branches, $departments, $page_title] = $this->buildReport($request);

        return view('modules.door_logs.report_pdf', compact(
            'data', 'filters', 'branches', 'departments', 'page_title'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    //  Ortak veri hazırlama
    // ──────────────────────────────────────────────────────────────
    private function buildReport(Request $request): array
    {
        $user     = auth()->user();
        $dateFrom = $request->input('date_from', today()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->input('date_to',   today()->format('Y-m-d'));

        // Şube kısıtı: super_admin her şubeyi görür, diğerleri sadece kendi şubesini
        $allowedBranchIds = $user->visibleBranchIds();

        // İnsan Kaynakları departmanı kontrolü
        $isHR = str_contains(mb_strtolower($user->department?->name ?? ''), 'kaynaklar');

        // Filtreler
        $branchId    = $request->input('branch_id');
        $deptId      = $request->input('department_id');
        $userId      = $request->input('user_id');

        // Ana sorgu
        $query = DoorLog::with(['user.branch', 'user.department'])
            ->whereBetween('logged_at', [
                $dateFrom . ' 00:00:00',
                $dateTo   . ' 23:59:59',
            ])
            ->when(!$user->isSuperAdmin() && !$isHR, fn($q) => $q->whereIn('branch_id', $allowedBranchIds))
            ->when($branchId,  fn($q) => $q->where('branch_id', $branchId))
            ->when($userId,    fn($q) => $q->where('user_id', $userId))
            ->orderBy('user_id')
            ->orderBy('logged_at');

        $logs = $query->get();

        // ── Departman filtresi (ilişki üzerinden)
        if ($deptId) {
            $logs = $logs->filter(fn($l) => optional($l->user)->department_id == $deptId);
        }

        // ── Kullanıcı bazlı istatistik hesapla
        $userStats = [];
        $dailyMap  = [];   // 'Y-m-d' => toplam dakika

        foreach ($logs->groupBy('user_id') as $uid => $userLogs) {
            $u          = $userLogs->first()->user;
            $entrances  = $userLogs->where('type', 'giris')->sortBy('logged_at')->values();
            $exits      = $userLogs->where('type', 'cikis')->sortBy('logged_at')->values();

            // Giriş-çıkış eşleştirerek süre hesapla
            $totalMinutes = 0;
            $ei = 0; // exit pointer
            foreach ($entrances as $entry) {
                // Bu girişten sonraki ilk çıkışı bul
                while ($ei < count($exits) && $exits[$ei]->logged_at <= $entry->logged_at) {
                    $ei++;
                }
                if ($ei < count($exits)) {
                    $diff = Carbon::parse($entry->logged_at)
                        ->diffInMinutes(Carbon::parse($exits[$ei]->logged_at));
                    // Makul bir shift sınırı (16 saat)
                    if ($diff <= 960) {
                        $totalMinutes += $diff;

                        // Günlük harita için
                        $day = Carbon::parse($entry->logged_at)->format('Y-m-d');
                        $dailyMap[$day] = ($dailyMap[$day] ?? 0) + $diff;
                    }
                    $ei++;
                }
            }

            // Çalışılan günler (giriş yapılan unique gün sayısı)
            $workedDays = $entrances
                ->map(fn($l) => Carbon::parse($l->logged_at)->format('Y-m-d'))
                ->unique()
                ->count();

            $userStats[$uid] = [
                'user'          => $u,
                'entry_count'   => $entrances->count(),
                'exit_count'    => $exits->count(),
                'worked_days'   => $workedDays,
                'total_minutes' => $totalMinutes,
                'total_hours'   => round($totalMinutes / 60, 1),
                'branch'        => optional($u)->branch?->name  ?? '-',
                'department'    => optional($u)->department?->name ?? '-',
            ];
        }

        // Sıralama: toplam saat (azalan)
        usort($userStats, fn($a, $b) => $b['total_minutes'] <=> $a['total_minutes']);

        // ── Özet değerler
        $summary = [
            'total_users'   => count($userStats),
            'total_entries' => $logs->where('type', 'giris')->count(),
            'total_exits'   => $logs->where('type', 'cikis')->count(),
            'total_hours'   => round(collect($userStats)->sum('total_minutes') / 60, 1),
        ];

        // ── Günlük aktivite (grafik için) — tarih aralığındaki her gün
        $period = Carbon::parse($dateFrom)->toPeriod($dateTo, '1 day');
        $dailyLabels  = [];
        $dailyMinutes = [];
        foreach ($period as $day) {
            $key = $day->format('Y-m-d');
            $dailyLabels[]  = $day->locale('tr')->isoFormat('D MMM');
            $dailyMinutes[] = round(($dailyMap[$key] ?? 0) / 60, 1);
        }

        // ── Departman bazlı giriş dağılımı (grafik için)
        $deptDist = $logs->where('type', 'giris')
            ->groupBy(fn($l) => optional($l->user?->department)->name ?? 'Belirsiz')
            ->map->count()
            ->sortDesc();

        // Filtrelerde kullanılacak listeler
        $branchList = ($user->isSuperAdmin() || $isHR)
            ? Branch::orderBy('name')->get()
            : Branch::whereIn('id', $allowedBranchIds)->orderBy('name')->get();

        $deptList   = Department::orderBy('name')->get();

        $filters = compact('dateFrom', 'dateTo', 'branchId', 'deptId', 'userId');

        $data = compact('userStats', 'summary', 'dailyLabels', 'dailyMinutes', 'deptDist');

        return [$data, $filters, $branchList, $deptList, 'Kapı Giriş/Çıkış Raporu'];
    }
}
