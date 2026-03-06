<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\DoorLog;
use App\Models\Fault;
use App\Models\GuestLog;
use App\Models\StaffSurvey;
use App\Models\Survey;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user        = auth()->user();
        $branchIds   = $user->visibleBranchIds();
        $today       = now()->toDateString();

        $stats = [];

        /* ── ARIZA ─────────────────────────────────────────── */
        if ($user->hasPermission('faults', 'index')) {
            $baseQ = Fault::whereIn('branch_id', $branchIds);

            if ($user->isSuperAdmin() || $user->isBranchManager()) {
                // Şubenin tüm arızaları
                $stats['faults_open']       = (clone $baseQ)->where('status', 'open')->count();
                $stats['faults_inprogress'] = (clone $baseQ)->where('status', 'in_progress')->count();
                $stats['faults_resolved_today'] = (clone $baseQ)
                    ->where('status', 'resolved')
                    ->whereDate('updated_at', $today)
                    ->count();
                $stats['faults_total']      = (clone $baseQ)->count();

                $recentFaults = (clone $baseQ)
                    ->with(['reporter', 'branch', 'faultType'])
                    ->whereIn('status', ['open', 'in_progress'])
                    ->orderBy('created_at', 'desc')
                    ->limit(8)
                    ->get();

            } elseif ($user->department_id) {
                // Dept manager / dept personeli → departmana atanan arızalar
                $stats['faults_open']       = Fault::where('assigned_department_id', $user->department_id)->where('status', 'open')->count();
                $stats['faults_inprogress'] = Fault::where('assigned_department_id', $user->department_id)->where('status', 'in_progress')->count();
                $stats['faults_resolved_today'] = Fault::where('assigned_department_id', $user->department_id)->where('status', 'resolved')->whereDate('updated_at', $today)->count();
                $stats['faults_total']      = Fault::where('assigned_department_id', $user->department_id)->count();

                $recentFaults = Fault::with(['reporter', 'branch', 'faultType'])
                    ->where('assigned_department_id', $user->department_id)
                    ->whereIn('status', ['open', 'in_progress'])
                    ->orderBy('created_at', 'desc')
                    ->limit(8)
                    ->get();
            } else {
                $recentFaults = collect();
            }
        } else {
            $recentFaults = collect();
        }

        // Kullanıcının kendi bildirdiği arızalar (her türlü rolde)
        $myFaultsOpen  = Fault::where('reported_by', $user->id)->where('status', 'open')->count();
        $myFaultsTotal = Fault::where('reported_by', $user->id)->count();
        $myRecentFaults = Fault::with(['faultType', 'branch'])
            ->where('reported_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        /* ── KAPI GİRİŞ ─────────────────────────────────────── */
        $stats['door_today'] = null;
        if ($user->hasPermission('door_logs', 'index')) {
            $stats['door_today'] = DoorLog::whereIn('branch_id', $branchIds)
                ->whereDate('logged_at', $today)
                ->count();
        }

        /* ── MİSAFİR ─────────────────────────────────────────── */
        $stats['guest_today'] = null;
        if ($user->hasPermission('guest_logs', 'index')) {
            $stats['guest_today'] = GuestLog::whereIn('branch_id', $branchIds)
                ->whereDate('created_at', $today)
                ->count();
        }

        /* ── PERSONEL ─────────────────────────────────────────── */
        $stats['users_total'] = null;
        if ($user->hasPermission('users', 'index')) {
            $stats['users_total'] = User::whereIn('branch_id', $branchIds)->count();
        }

        /* ── ARAÇ ─────────────────────────────────────────────── */
        $stats['vehicles_total'] = null;
        if ($user->hasPermission('vehicles', 'index')) {
            $stats['vehicles_total'] = Vehicle::whereIn('branch_id', $branchIds)->count();
        }

        /* ── DEMİRBAŞ ─────────────────────────────────────────── */
        $stats['assets_total'] = null;
        if ($user->hasPermission('assets', 'index')) {
            $stats['assets_total'] = Asset::whereIn('branch_id', $branchIds)->count();
        }

        /* ── ANKET ─────────────────────────────────────────────── */
        $stats['surveys_total'] = null;
        if ($user->hasPermission('surveys', 'index')) {
            $stats['surveys_total'] = Survey::whereIn('branch_id', $branchIds)->count();
        }

        $page_title = 'Ana Sayfa';


        return view('dashboard.index', compact(
            'user', 'stats',
            'recentFaults', 'myFaultsOpen', 'myFaultsTotal', 'myRecentFaults',
            'page_title'
        ));
    }
}
