<?php

namespace App\Http\Controllers\Modules;

use App\Models\Branch;
use App\Models\Department;
use App\Models\DoorLog;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class HrReportController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'hr_reports',
            ['index', 'pdf', 'staffByBranch'],
            [], [], [], []
        );
    }

    // ─── Shift tespiti ────────────────────────────────────────────
    // 3 shift: 09-17 | 16-24 | 00-08
    // Döner: ['name'=>'09-17', 'late'=>bool]
    private function detectShift(Carbon $entry): array
    {
        $hm = $entry->hour * 60 + $entry->minute;

        // 06:00–12:59 → 09-17 shift; geç: 09:30 sonrası
        if ($hm >= 360 && $hm < 780) {
            return ['name' => '09-17', 'late' => $hm > 570];
        }
        // 13:00–20:59 → 16-24 shift; geç: 16:30 sonrası
        if ($hm >= 780 && $hm < 1260) {
            return ['name' => '16-24', 'late' => $hm > 990];
        }
        // 21:00–05:59 → 00-08 shift
        // 21:00-23:59 erken geliş → geç değil; 00:31-05:59 → geç
        $late = ($hm < 360) && ($hm > 30);
        return ['name' => '00-08', 'late' => $late];
    }

    // ─── AJAX: şubeye göre dept_manager listesi ──────────────────
    public function staffByBranch(Request $request)
    {
        $branchId = $request->input('branch_id');
        $users = User::where('role', 'dept_manager')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($users);
    }

    // ─── PDF görünümü ─────────────────────────────────────────────
    public function pdf(Request $request)
    {
        $viewData = $this->buildReportData($request);
        return view('modules.door_logs.hr_report_pdf', $viewData);
    }

    // ─── Ana sayfa ────────────────────────────────────────────────
    public function index(Request $request)
    {
        return view('modules.door_logs.hr_report', $this->buildReportData($request));
    }

    // ─── Ortak veri hazırlama ─────────────────────────────────────
    private function buildReportData(Request $request): array
    {
        $user     = auth()->user();
        $dateFrom = $request->input('date_from', today()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->input('date_to',   today()->format('Y-m-d'));
        $branchId = $request->input('branch_id');
        $userId   = $request->input('user_id');

        // hr_reports yetkisi: TÜM şubeler görünür
        $branches = Branch::orderBy('name')->get();

        // Kişi dropdown: dept_manager'lar (şubeye göre veya tümü)
        $deptManagers = User::where('role', 'dept_manager')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get();

        // Ana sorgu
        $query = DoorLog::with(['user.branch', 'user.department'])
            ->whereBetween('logged_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($userId,   fn($q) => $q->where('user_id', $userId))
            ->orderBy('user_id')
            ->orderBy('logged_at');

        $logs = $query->get();

        // ── Personel bazlı istatistik
        $userStats = [];
        $dailyMap  = [];

        foreach ($logs->groupBy('user_id') as $uid => $userLogs) {
            $u         = $userLogs->first()->user;
            $entrances = $userLogs->where('type', 'giris')->sortBy('logged_at')->values();
            $exits     = $userLogs->where('type', 'cikis')->sortBy('logged_at')->values();

            $totalMinutes = 0;
            $overtimeMin  = 0;
            $lateEntries  = 0;
            $shiftCounts  = ['09-17' => 0, '16-24' => 0, '00-08' => 0];
            $ei = 0;

            foreach ($entrances as $entry) {
                $entryCarbon = Carbon::parse($entry->logged_at);
                $shift = $this->detectShift($entryCarbon);
                $shiftCounts[$shift['name']]++;
                if ($shift['late']) $lateEntries++;

                while ($ei < count($exits) && $exits[$ei]->logged_at <= $entry->logged_at) {
                    $ei++;
                }
                if ($ei < count($exits)) {
                    $diff = $entryCarbon->diffInMinutes(Carbon::parse($exits[$ei]->logged_at));
                    if ($diff <= 960) {
                        $totalMinutes += $diff;
                        $day = $entryCarbon->format('Y-m-d');
                        $dailyMap[$day] = ($dailyMap[$day] ?? 0) + $diff;
                        if ($diff > 480) $overtimeMin += ($diff - 480);
                    }
                    $ei++;
                }
            }

            $workedDays = $entrances
                ->map(fn($l) => Carbon::parse($l->logged_at)->format('Y-m-d'))
                ->unique()->count();

            // Baskın shift
            arsort($shiftCounts);
            $dominantShift = array_key_first($shiftCounts);

            $firstEntry = $entrances->first()?->logged_at;
            $lastExit   = $exits->last()?->logged_at;

            $userStats[$uid] = [
                'user'           => $u,
                'branch'         => optional($u)?->branch?->name ?? '-',
                'department'     => optional($u)?->department?->name ?? '-',
                'entry_count'    => $entrances->count(),
                'exit_count'     => $exits->count(),
                'worked_days'    => $workedDays,
                'total_minutes'  => $totalMinutes,
                'total_hours'    => round($totalMinutes / 60, 1),
                'overtime_min'   => $overtimeMin,
                'overtime_hrs'   => round($overtimeMin / 60, 1),
                'late_entries'   => $lateEntries,
                'dominant_shift' => $dominantShift,
                'avg_hours'      => $workedDays > 0 ? round(($totalMinutes / 60) / $workedDays, 1) : 0,
                'first_entry'    => $firstEntry ? Carbon::parse($firstEntry)->format('H:i') : '-',
                'last_exit'      => $lastExit ? Carbon::parse($lastExit)->format('H:i') : '-',
            ];
        }

        usort($userStats, fn($a, $b) => $b['total_minutes'] <=> $a['total_minutes']);

        // ── Grup Müdürleri: aynı ad + aynı departman, farklı şubelerdeki kullanıcılar
        $groupManagers = collect($userStats)
            ->groupBy(fn($s) => (mb_strtolower(optional($s['user'])->name ?? '')) . '||' . (optional(optional($s['user'])->department)->id ?? 0))
            ->filter(function ($group) {
                $branchIds = $group->map(fn($s) => optional($s['user'])->branch_id)->unique()->filter()->values();
                return $branchIds->count() > 1;
            })
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'name'         => optional($first['user'])->name ?? '-',
                    'department'   => $first['department'],
                    'branches_str' => $group->pluck('branch')->filter()->implode(', '),
                    'total_hours'  => round($group->sum('total_minutes') / 60, 1),
                    'overtime_hrs' => round($group->sum('overtime_min') / 60, 1),
                    'late_entries' => $group->sum('late_entries'),
                    'worked_days'  => $group->sum('worked_days'),
                    'entry_count'  => $group->sum('entry_count'),
                    'exit_count'   => $group->sum('exit_count'),
                    'members'      => $group->values(),
                ];
            })->values();

        // ── Devam takvimi
        $period     = CarbonPeriod::create($dateFrom, $dateTo);
        $calDays    = collect($period)->map(fn($d) => $d->format('Y-m-d'))->toArray();
        $attendance = [];

        foreach ($logs->groupBy('user_id') as $uid => $userLogs) {
            $byDay = $userLogs->groupBy(fn($l) => Carbon::parse($l->logged_at)->format('Y-m-d'));
            foreach ($calDays as $day) {
                $dayLogs = $byDay[$day] ?? collect();
                if ($dayLogs->isEmpty()) {
                    $attendance[$uid][$day] = null;
                } elseif ($dayLogs->contains('type', 'giris') && $dayLogs->contains('type', 'cikis')) {
                    $attendance[$uid][$day] = 'both';
                } elseif ($dayLogs->contains('type', 'giris')) {
                    $attendance[$uid][$day] = 'entry';
                } else {
                    $attendance[$uid][$day] = 'exit';
                }
            }
        }

        // ── Departman özeti
        $deptSummary = collect($userStats)
            ->groupBy(fn($s) => $s['department'])
            ->map(fn($group, $dept) => [
                'department'   => $dept,
                'count'        => $group->count(),
                'total_hours'  => round($group->sum('total_minutes') / 60, 1),
                'avg_hours'    => $group->count() > 0 ? round(($group->sum('total_minutes') / 60) / $group->count(), 1) : 0,
                'overtime_hrs' => round($group->sum('overtime_min') / 60, 1),
                'late_entries' => $group->sum('late_entries'),
            ])->sortByDesc('total_hours')->values();

        // ── KPI özeti
        $summary = [
            'total_staff'    => count($userStats),
            'total_entries'  => $logs->where('type', 'giris')->count(),
            'total_exits'    => $logs->where('type', 'cikis')->count(),
            'total_hours'    => round(collect($userStats)->sum('total_minutes') / 60, 1),
            'total_overtime' => round(collect($userStats)->sum('overtime_min') / 60, 1),
            'total_late'     => collect($userStats)->sum('late_entries'),
            'avg_daily'      => count($calDays) > 0 && count($userStats) > 0
                ? round(collect($userStats)->sum('total_minutes') / 60 / count($calDays), 1)
                : 0,
        ];

        // ── Günlük totaller (grafik)
        $dailyLabels = [];
        $dailyHours  = [];
        foreach ($calDays as $day) {
            $dailyLabels[] = Carbon::parse($day)->locale('tr')->isoFormat('D MMM');
            $dailyHours[]  = round(($dailyMap[$day] ?? 0) / 60, 1);
        }

        $filters = [
            'dateFrom' => $dateFrom,
            'dateTo'   => $dateTo,
            'branchId' => $branchId,
            'userId'   => $userId,
        ];

        return compact(
            'userStats', 'summary', 'deptSummary',
            'attendance', 'calDays',
            'dailyLabels', 'dailyHours',
            'filters', 'branches', 'deptManagers',
            'groupManagers'
        );
    }
}
