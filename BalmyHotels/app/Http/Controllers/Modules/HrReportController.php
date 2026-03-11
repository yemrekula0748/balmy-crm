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
            ['index'],  // index
            [],          // show
            [],          // create
            [],          // edit
            []           // delete
        );
    }

    public function index(Request $request)
    {
        $user     = auth()->user();
        $dateFrom = $request->input('date_from', today()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->input('date_to',   today()->format('Y-m-d'));
        $branchId = $request->input('branch_id');
        $deptId   = $request->input('department_id');

        // hr_reports yetkisine sahip kullanıcılar TÜM şubeleri görebilir
        $branches    = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        // Temel sorgu — hr_reports yetkisi varsa şube kısıtı yok
        $query = DoorLog::with(['user.branch', 'user.department'])
            ->whereBetween('logged_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('user_id')
            ->orderBy('logged_at');

        $logs = $query->get();

        // Departman filtresi (ilişki üzerinden)
        if ($deptId) {
            $logs = $logs->filter(fn($l) => optional($l->user)->department_id == $deptId);
        }

        // ── Personel bazlı istatistik
        $userStats = [];
        $dailyMap  = [];  // 'Y-m-d' => toplam dakika

        foreach ($logs->groupBy('user_id') as $uid => $userLogs) {
            $u         = $userLogs->first()->user;
            $entrances = $userLogs->where('type', 'giris')->sortBy('logged_at')->values();
            $exits     = $userLogs->where('type', 'cikis')->sortBy('logged_at')->values();

            $totalMinutes  = 0;
            $overtimeMin   = 0;
            $lateEntries   = 0;
            $shiftMinutes  = 480; // normal mesai 8 saat
            $lateThreshold = '09:00';
            $ei = 0;

            foreach ($entrances as $entry) {
                while ($ei < count($exits) && $exits[$ei]->logged_at <= $entry->logged_at) {
                    $ei++;
                }
                if ($ei < count($exits)) {
                    $diff = Carbon::parse($entry->logged_at)
                        ->diffInMinutes(Carbon::parse($exits[$ei]->logged_at));
                    if ($diff <= 960) {
                        $totalMinutes += $diff;
                        $day = Carbon::parse($entry->logged_at)->format('Y-m-d');
                        $dailyMap[$day] = ($dailyMap[$day] ?? 0) + $diff;

                        if ($diff > $shiftMinutes) {
                            $overtimeMin += ($diff - $shiftMinutes);
                        }
                    }
                    $ei++;
                }
                // Geç giriş kontrolü
                if (Carbon::parse($entry->logged_at)->format('H:i') > $lateThreshold) {
                    $lateEntries++;
                }
            }

            $workedDays = $entrances
                ->map(fn($l) => Carbon::parse($l->logged_at)->format('Y-m-d'))
                ->unique()->count();

            // İlk ve son giriş saati
            $firstEntry = $entrances->first()?->logged_at;
            $lastExit   = $exits->last()?->logged_at;

            $userStats[$uid] = [
                'user'          => $u,
                'branch'        => optional($u)?->branch?->name ?? '-',
                'department'    => optional($u)?->department?->name ?? '-',
                'entry_count'   => $entrances->count(),
                'exit_count'    => $exits->count(),
                'worked_days'   => $workedDays,
                'total_minutes' => $totalMinutes,
                'total_hours'   => round($totalMinutes / 60, 1),
                'overtime_min'  => $overtimeMin,
                'overtime_hrs'  => round($overtimeMin / 60, 1),
                'late_entries'  => $lateEntries,
                'avg_hours'     => $workedDays > 0 ? round(($totalMinutes / 60) / $workedDays, 1) : 0,
                'first_entry'   => $firstEntry ? Carbon::parse($firstEntry)->format('H:i') : '-',
                'last_exit'     => $lastExit ? Carbon::parse($lastExit)->format('H:i') : '-',
            ];
        }

        // Sıralama: çalışma saati azalan
        usort($userStats, fn($a, $b) => $b['total_minutes'] <=> $a['total_minutes']);

        // ── Devam tablosu: takvim günleri x personel (attendance grid)
        $period     = CarbonPeriod::create($dateFrom, $dateTo);
        $calDays    = collect($period)->map(fn($d) => $d->format('Y-m-d'))->toArray();
        $attendance = [];  // uid => ['Y-m-d' => 'giris'|'cikis'|'her_ikisi'|null]

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
                'department'    => $dept,
                'count'         => $group->count(),
                'total_hours'   => round($group->sum('total_minutes') / 60, 1),
                'avg_hours'     => $group->count() > 0 ? round(($group->sum('total_minutes') / 60) / $group->count(), 1) : 0,
                'overtime_hrs'  => round($group->sum('overtime_min') / 60, 1),
                'late_entries'  => $group->sum('late_entries'),
            ])->sortByDesc('total_hours')->values();

        // ── Özet KPI
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

        // ── Günlük toplamlar (grafik)
        $dailyLabels  = [];
        $dailyHours   = [];
        foreach ($calDays as $day) {
            $dailyLabels[] = Carbon::parse($day)->locale('tr')->isoFormat('D MMM');
            $dailyHours[]  = round(($dailyMap[$day] ?? 0) / 60, 1);
        }

        $filters = [
            'dateFrom' => $dateFrom,
            'dateTo'   => $dateTo,
            'branchId' => $branchId,
            'deptId'   => $deptId,
        ];

        return view('modules.door_logs.hr_report', compact(
            'userStats', 'summary', 'deptSummary',
            'attendance', 'calDays',
            'dailyLabels', 'dailyHours',
            'filters', 'branches', 'departments'
        ));
    }
}
