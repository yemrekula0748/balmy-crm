<?php

namespace App\Http\Controllers\Modules;

use App\Models\Branch;
use App\Models\Department;
use App\Models\PdksAttendanceRecord;
use App\Models\PdksBreakRecord;
use App\Models\PdksBreakType;
use App\Models\PdksDevicePolicy;
use App\Models\PdksEmployee;
use App\Models\PdksLeaveRequest;
use App\Models\PdksLeaveType;
use App\Models\PdksNotification;
use App\Models\PdksOvertimeRequest;
use App\Models\PdksShiftAssignment;
use App\Models\PdksShiftChangeRequest;
use App\Models\PdksShiftType;
use App\Models\PdksSyncLog;
use App\Services\Pdks\ElektraPdksService;
use App\Services\Pdks\PdksVerificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PdksController extends BaseModuleController
{
    public function __construct()
    {
        $this->middleware('perm:pdks_dashboard,index')->only(['index']);
        $this->middleware('perm:pdks_dashboard,create')->only(['checkIn', 'startBreak', 'storeLeaveRequest', 'storeOvertimeRequest', 'storeShiftChangeRequest']);
        $this->middleware('perm:pdks_dashboard,edit')->only(['checkOut', 'endBreak']);
        $this->middleware('perm:pdks_employees,index')->only(['employees']);
        $this->middleware('perm:pdks_employees,create')->only(['syncForesta']);
        $this->middleware('perm:pdks_attendance,index')->only(['attendance']);
        $this->middleware('perm:pdks_breaks,index')->only(['breakTypes']);
        $this->middleware('perm:pdks_breaks,create')->only(['storeBreakType']);
        $this->middleware('perm:pdks_shifts,index')->only(['shifts']);
        $this->middleware('perm:pdks_shifts,create')->only(['storeShiftType', 'storeShiftAssignment']);
        $this->middleware('perm:pdks_shifts,edit')->only(['updateShiftChangeStatus']);
        $this->middleware('perm:pdks_leaves,index')->only(['leaves']);
        $this->middleware('perm:pdks_leaves,create')->only(['storeLeaveType']);
        $this->middleware('perm:pdks_leaves,edit')->only(['updateLeaveStatus']);
        $this->middleware('perm:pdks_overtime,index')->only(['overtime']);
        $this->middleware('perm:pdks_overtime,edit')->only(['updateOvertimeStatus']);
        $this->middleware('perm:pdks_reports,index')->only(['reports']);
        $this->middleware('perm:pdks_notifications,index')->only(['notifications']);
        $this->middleware('perm:pdks_notifications,edit')->only(['markNotificationRead', 'markAllNotificationsRead']);
        $this->middleware('perm:pdks_settings,index')->only(['settings']);
        $this->middleware('perm:pdks_settings,edit')->only(['storePolicy']);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $employee = $this->employeeForUser($user, true);
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $todayRecord = PdksAttendanceRecord::with(['breaks.breakType', 'shiftAssignment.shiftType'])
            ->where('employee_id', $employee->id)
            ->whereDate('work_date', $today)
            ->first();

        $activeBreak = $todayRecord
            ? $todayRecord->breaks()->with('breakType')->where('status', 'active')->latest()->first()
            : null;

        $monthlyRecords = PdksAttendanceRecord::where('employee_id', $employee->id)
            ->whereBetween('work_date', [$monthStart, $monthEnd])
            ->orderBy('work_date')
            ->get();
        $annualLeaveType = PdksLeaveType::where('code', 'annual')->first();
        $approvedAnnualLeave = $annualLeaveType
            ? PdksLeaveRequest::where('employee_id', $employee->id)
                ->where('leave_type_id', $annualLeaveType->id)
                ->where('status', 'approved')
                ->whereYear('start_date', now()->year)
                ->sum('total_days')
            : 0;

        $monthlySummary = [
            'days' => $monthlyRecords->count(),
            'worked_minutes' => $monthlyRecords->sum('worked_minutes'),
            'break_minutes' => $monthlyRecords->sum('break_minutes'),
            'late_minutes' => $monthlyRecords->sum('late_minutes'),
            'early_leave_minutes' => $monthlyRecords->sum('early_leave_minutes'),
            'pending_leave' => PdksLeaveRequest::where('employee_id', $employee->id)->where('status', 'pending')->count(),
            'approved_leave_days' => PdksLeaveRequest::where('employee_id', $employee->id)->where('status', 'approved')->whereBetween('start_date', [$monthStart, $monthEnd])->sum('total_days'),
            'annual_leave_balance' => max(0, (float) ($annualLeaveType?->default_days ?? 0) - (float) $approvedAnnualLeave),
            'approved_overtime_minutes' => PdksOvertimeRequest::where('employee_id', $employee->id)->where('status', 'approved')->whereBetween('overtime_date', [$monthStart, $monthEnd])->sum('duration_minutes'),
        ];

        return view('modules.pdks.index', [
            'page_title' => 'Personel PDKS',
            'employee' => $employee,
            'todayRecord' => $todayRecord,
            'activeBreak' => $activeBreak,
            'breakTypes' => PdksBreakType::where('is_active', true)->orderBy('sort_order')->get(),
            'leaveTypes' => PdksLeaveType::where('is_active', true)->orderBy('name')->get(),
            'shiftTypes' => PdksShiftType::where('is_active', true)->orderBy('name')->get(),
            'todayShift' => PdksShiftAssignment::with('shiftType')->where('employee_id', $employee->id)->whereDate('shift_date', $today)->first(),
            'monthlySummary' => $monthlySummary,
            'monthlyRecords' => $monthlyRecords,
            'notifications' => PdksNotification::where(function ($q) use ($user, $employee) {
                $q->where('user_id', $user->id)->orWhere('employee_id', $employee->id);
            })->latest()->take(8)->get(),
        ]);
    }

    public function checkIn(Request $request, PdksVerificationService $verificationService)
    {
        $request->validate($this->locationRules());
        $employee = $this->employeeForUser(auth()->user(), true);
        $today = now()->toDateString();

        $record = PdksAttendanceRecord::firstOrNew([
            'employee_id' => $employee->id,
            'work_date' => $today,
        ]);

        if ($record->exists && $record->check_in_at && !$record->check_out_at) {
            return back()->with('error', 'Bugün için zaten açık bir giriş kaydın var.');
        }

        if ($record->exists && $record->check_out_at) {
            return back()->with('error', 'Bugünkü kayıt kapanmış. Ek giriş gerekiyorsa İK/PDKS sorumlusu manuel işlem yapmalı.');
        }

        $verification = $verificationService->verify($request, $employee->branch_id);
        if ($verification['status'] === 'rejected') {
            return back()->with('error', implode(' ', $verification['issues']));
        }

        $shift = PdksShiftAssignment::with('shiftType')
            ->where('employee_id', $employee->id)
            ->whereDate('shift_date', $today)
            ->first();

        $record->fill([
            'user_id' => auth()->id(),
            'branch_id' => $employee->branch_id,
            'department_id' => $employee->department_id,
            'shift_assignment_id' => $shift?->id,
            'check_in_at' => now(),
            'check_in_latitude' => $request->latitude,
            'check_in_longitude' => $request->longitude,
            'check_in_accuracy' => $request->accuracy,
            'check_in_wifi_ssid' => $request->wifi_ssid,
            'check_in_wifi_bssid' => $request->wifi_bssid,
            'check_in_mock_detected' => $request->boolean('is_mock_location'),
            'verification_status' => $verification['status'],
            'verification_issues' => $verification['issues'],
            'late_minutes' => $this->lateMinutes($shift?->shiftType, now()),
            'status' => 'open',
            'source' => 'web',
        ])->save();

        return back()->with('success', 'Giriş kaydın alındı.');
    }

    public function checkOut(Request $request, PdksVerificationService $verificationService)
    {
        $request->validate($this->locationRules());
        $employee = $this->employeeForUser(auth()->user(), true);
        $record = PdksAttendanceRecord::with('shiftAssignment.shiftType')
            ->where('employee_id', $employee->id)
            ->whereNull('check_out_at')
            ->latest('work_date')
            ->first();

        if (!$record) {
            return back()->with('error', 'Açık giriş kaydı bulunamadı.');
        }

        if ($record->breaks()->where('status', 'active')->exists()) {
            return back()->with('error', 'Önce aktif molayı bitirmelisin.');
        }

        $verification = $verificationService->verify($request, $employee->branch_id);
        if ($verification['status'] === 'rejected') {
            return back()->with('error', implode(' ', $verification['issues']));
        }

        $record->update([
            'check_out_at' => now(),
            'check_out_latitude' => $request->latitude,
            'check_out_longitude' => $request->longitude,
            'check_out_accuracy' => $request->accuracy,
            'check_out_wifi_ssid' => $request->wifi_ssid,
            'check_out_wifi_bssid' => $request->wifi_bssid,
            'check_out_mock_detected' => $request->boolean('is_mock_location'),
            'verification_status' => $verification['status'],
            'verification_issues' => array_values(array_unique(array_merge((array) $record->verification_issues, $verification['issues']))),
            'worked_minutes' => $this->workedMinutes($record),
            'break_minutes' => $record->breaks()->sum('duration_minutes'),
            'early_leave_minutes' => $this->earlyLeaveMinutes($record->shiftAssignment?->shiftType, now()),
            'status' => 'closed',
        ]);

        return back()->with('success', 'Çıkış kaydın alındı.');
    }

    public function startBreak(Request $request)
    {
        $request->validate([
            'break_type_id' => 'required|exists:pdks_break_types,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_mock_location' => 'nullable|boolean',
        ]);

        $employee = $this->employeeForUser(auth()->user(), true);
        $record = PdksAttendanceRecord::where('employee_id', $employee->id)
            ->whereNull('check_out_at')
            ->latest('work_date')
            ->first();

        if (!$record) {
            return back()->with('error', 'Mola başlatmak için önce giriş yapmalısın.');
        }

        if ($record->breaks()->where('status', 'active')->exists()) {
            return back()->with('error', 'Zaten aktif bir mola var.');
        }

        PdksBreakRecord::create([
            'attendance_record_id' => $record->id,
            'employee_id' => $employee->id,
            'break_type_id' => $request->break_type_id,
            'started_at' => now(),
            'start_latitude' => $request->latitude,
            'start_longitude' => $request->longitude,
            'mock_detected' => $request->boolean('is_mock_location'),
            'status' => 'active',
        ]);

        return back()->with('success', 'Mola başlatıldı.');
    }

    public function endBreak(Request $request)
    {
        $employee = $this->employeeForUser(auth()->user(), true);
        $break = PdksBreakRecord::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->latest('started_at')
            ->first();

        if (!$break) {
            return back()->with('error', 'Aktif mola bulunamadı.');
        }

        $duration = max(1, $break->started_at->diffInMinutes(now()));
        $break->update([
            'ended_at' => now(),
            'duration_minutes' => $duration,
            'end_latitude' => $request->latitude,
            'end_longitude' => $request->longitude,
            'status' => 'closed',
        ]);

        $break->attendanceRecord->update([
            'break_minutes' => $break->attendanceRecord->breaks()->sum('duration_minutes'),
        ]);

        return back()->with('success', 'Mola bitirildi.');
    }

    public function employees(Request $request)
    {
        $query = $this->visibleEmployees()->with(['branch', 'department', 'user'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('registry_no', 'like', "%{$search}%")
                ->orWhere('pdks_card_no', 'like', "%{$search}%"));
        }
        if ($request->filled('branch_id')) $query->where('branch_id', $request->branch_id);
        if ($request->filled('department_id')) $query->where('department_id', $request->department_id);
        if ($request->filled('active')) $query->where('is_active', $request->active === '1');

        return view('modules.pdks.employees', [
            'page_title' => 'PDKS Personeller',
            'employees' => $query->paginate(30)->withQueryString(),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'syncLogs' => PdksSyncLog::latest()->take(8)->get(),
        ]);
    }

    public function syncForesta(ElektraPdksService $service)
    {
        $log = $service->syncForesta();

        if ($log->status === 'failed') {
            return back()->with('error', 'Elektra sync başarısız: ' . $log->error_message);
        }

        return back()->with('success', "Elektra sync tamamlandı. Yeni: {$log->created_records}, Güncel: {$log->updated_records}, Hatalı: {$log->failed_records}");
    }

    public function attendance(Request $request)
    {
        $from = $request->input('date_from', now()->startOfMonth()->toDateString());
        $to = $request->input('date_to', now()->endOfMonth()->toDateString());

        $records = PdksAttendanceRecord::with(['employee.branch', 'employee.department', 'breaks.breakType', 'shiftAssignment.shiftType'])
            ->whereHas('employee', fn ($q) => $this->applyEmployeeVisibility($q))
            ->whereBetween('work_date', [$from, $to])
            ->when($request->filled('employee_id'), fn ($q) => $q->where('employee_id', $request->employee_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('work_date')
            ->paginate(30)
            ->withQueryString();

        return view('modules.pdks.attendance', [
            'page_title' => 'PDKS Devam Kayıtları',
            'records' => $records,
            'employees' => $this->visibleEmployees()->orderBy('name')->get(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function breakTypes()
    {
        return view('modules.pdks.breaks', [
            'page_title' => 'PDKS Mola Tipleri',
            'breakTypes' => PdksBreakType::orderBy('sort_order')->get(),
        ]);
    }

    public function storeBreakType(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'max_minutes' => 'nullable|integer|min:1',
            'is_paid' => 'nullable|boolean',
            'color' => 'nullable|string|max:30',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        PdksBreakType::updateOrCreate(['code' => $data['code']], [
            'name' => $data['name'],
            'max_minutes' => $data['max_minutes'] ?? null,
            'is_paid' => $request->boolean('is_paid'),
            'color' => $data['color'] ?? '#c19b77',
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        return back()->with('success', 'Mola tipi kaydedildi.');
    }

    public function shifts(Request $request)
    {
        $from = $request->input('date_from', now()->startOfWeek()->toDateString());
        $to = $request->input('date_to', now()->endOfWeek()->toDateString());

        return view('modules.pdks.shifts', [
            'page_title' => 'PDKS Vardiya Takvimi',
            'shiftTypes' => PdksShiftType::with('branch')->orderBy('name')->get(),
            'assignments' => PdksShiftAssignment::with(['employee.department', 'shiftType'])
                ->whereHas('employee', fn ($q) => $this->applyEmployeeVisibility($q))
                ->whereBetween('shift_date', [$from, $to])
                ->orderBy('shift_date')
                ->paginate(50)
                ->withQueryString(),
            'employees' => $this->visibleEmployees()->orderBy('name')->get(),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'from' => $from,
            'to' => $to,
            'changeRequests' => PdksShiftChangeRequest::with(['employee', 'shiftAssignment.shiftType', 'requestedShiftType'])
                ->whereHas('employee', fn ($q) => $this->applyEmployeeVisibility($q))
                ->latest()
                ->take(20)
                ->get(),
        ]);
    }

    public function storeShiftType(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'break_minutes' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:30',
        ]);

        $start = Carbon::createFromFormat('H:i', $data['start_time']);
        $end = Carbon::createFromFormat('H:i', $data['end_time']);
        $crossesMidnight = $end->lessThanOrEqualTo($start);
        $planned = $start->diffInMinutes($crossesMidnight ? $end->copy()->addDay() : $end);

        PdksShiftType::create([
            ...$data,
            'planned_minutes' => $planned,
            'break_minutes' => $data['break_minutes'] ?? 0,
            'crosses_midnight' => $crossesMidnight,
            'color' => $data['color'] ?? '#c19b77',
            'is_active' => true,
        ]);

        return back()->with('success', 'Vardiya tipi oluşturuldu.');
    }

    public function storeShiftAssignment(Request $request)
    {
        $data = $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:pdks_employees,id',
            'shift_type_id' => 'required|exists:pdks_shift_types,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'notes' => 'nullable|string|max:1000',
        ]);

        abort_if(
            $this->visibleEmployees()->whereIn('id', $data['employee_ids'])->count() !== count(array_unique($data['employee_ids'])),
            403
        );

        $dates = Carbon::parse($data['date_from'])->daysUntil(Carbon::parse($data['date_to'])->addDay());

        foreach ($data['employee_ids'] as $employeeId) {
            $employee = PdksEmployee::find($employeeId);
            foreach ($dates as $date) {
                PdksShiftAssignment::updateOrCreate([
                    'employee_id' => $employeeId,
                    'shift_date' => $date->toDateString(),
                ], [
                    'branch_id' => $employee?->branch_id,
                    'department_id' => $employee?->department_id,
                    'shift_type_id' => $data['shift_type_id'],
                    'status' => 'assigned',
                    'notes' => $data['notes'] ?? null,
                    'assigned_by' => auth()->id(),
                ]);
            }
        }

        return back()->with('success', 'Vardiya atamaları kaydedildi.');
    }

    public function storeShiftChangeRequest(Request $request)
    {
        $employee = $this->employeeForUser(auth()->user(), true);
        $data = $request->validate([
            'shift_assignment_id' => 'nullable|exists:pdks_shift_assignments,id',
            'requested_shift_type_id' => 'nullable|exists:pdks_shift_types,id',
            'requested_shift_date' => 'nullable|date',
            'reason' => 'required|string|max:1000',
        ]);

        PdksShiftChangeRequest::create([
            ...$data,
            'employee_id' => $employee->id,
            'status' => 'pending',
        ]);

        $this->notifyEmployee($employee, 'Vardiya değişim talebi alındı', 'Talebin onay sürecine gönderildi.', route('pdks.notifications'));

        return back()->with('success', 'Vardiya değişim talebin alındı.');
    }

    public function updateShiftChangeStatus(Request $request, PdksShiftChangeRequest $shiftChangeRequest)
    {
        abort_if(!$this->visibleEmployees()->whereKey($shiftChangeRequest->employee_id)->exists(), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'manager_note' => 'nullable|string|max:1000',
        ]);

        $shiftChangeRequest->update([
            'status' => $data['status'],
            'manager_note' => $data['manager_note'] ?? null,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->notifyEmployee($shiftChangeRequest->employee, 'Vardiya değişim talebi güncellendi', 'Talep durumu: ' . $data['status'], route('pdks.notifications'));

        return back()->with('success', 'Vardiya değişim talebi güncellendi.');
    }

    public function leaves(Request $request)
    {
        return view('modules.pdks.leaves', [
            'page_title' => 'PDKS İzinler',
            'leaveTypes' => PdksLeaveType::orderBy('name')->get(),
            'requests' => PdksLeaveRequest::with(['employee.department', 'leaveType', 'approver'])
                ->whereHas('employee', fn ($q) => $this->applyEmployeeVisibility($q))
                ->latest()
                ->paginate(30)
                ->withQueryString(),
        ]);
    }

    public function storeLeaveType(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'default_days' => 'nullable|numeric|min:0',
            'is_paid' => 'nullable|boolean',
            'color' => 'nullable|string|max:30',
        ]);

        PdksLeaveType::updateOrCreate(['code' => $data['code']], [
            'name' => $data['name'],
            'default_days' => $data['default_days'] ?? 0,
            'is_paid' => $request->boolean('is_paid', true),
            'requires_approval' => true,
            'color' => $data['color'] ?? '#c19b77',
            'is_active' => true,
        ]);

        return back()->with('success', 'İzin tipi kaydedildi.');
    }

    public function storeLeaveRequest(Request $request)
    {
        $employee = $this->employeeForUser(auth()->user(), true);
        $data = $request->validate([
            'leave_type_id' => 'required|exists:pdks_leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
        ]);

        $days = Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) + 1;

        $leave = PdksLeaveRequest::create([
            ...$data,
            'employee_id' => $employee->id,
            'branch_id' => $employee->branch_id,
            'department_id' => $employee->department_id,
            'total_days' => $days,
            'status' => 'pending',
        ]);

        $this->notifyEmployee($employee, 'İzin talebin alındı', $leave->start_date->format('d.m.Y') . ' - ' . $leave->end_date->format('d.m.Y') . ' tarihli izin talebin onay bekliyor.', route('pdks.notifications'));

        return back()->with('success', 'İzin talebin oluşturuldu.');
    }

    public function updateLeaveStatus(Request $request, PdksLeaveRequest $leaveRequest)
    {
        abort_if(!$this->visibleEmployees()->whereKey($leaveRequest->employee_id)->exists(), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected', 'cancelled'])],
            'manager_note' => 'nullable|string|max:1000',
        ]);

        $leaveRequest->update([
            'status' => $data['status'],
            'manager_note' => $data['manager_note'] ?? null,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->notifyEmployee($leaveRequest->employee, 'İzin talebi güncellendi', 'İzin talebi durumu: ' . $data['status'], route('pdks.notifications'));

        return back()->with('success', 'İzin talebi güncellendi.');
    }

    public function overtime()
    {
        return view('modules.pdks.overtime', [
            'page_title' => 'PDKS Fazla Mesai',
            'requests' => PdksOvertimeRequest::with(['employee.department', 'approver'])
                ->whereHas('employee', fn ($q) => $this->applyEmployeeVisibility($q))
                ->latest()
                ->paginate(30)
                ->withQueryString(),
        ]);
    }

    public function storeOvertimeRequest(Request $request)
    {
        $employee = $this->employeeForUser(auth()->user(), true);
        $data = $request->validate([
            'overtime_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'reason' => 'required|string|max:1000',
        ]);

        $start = Carbon::createFromFormat('H:i', $data['start_time']);
        $end = Carbon::createFromFormat('H:i', $data['end_time']);
        $duration = $start->diffInMinutes($end->lessThanOrEqualTo($start) ? $end->copy()->addDay() : $end);

        $overtime = PdksOvertimeRequest::create([
            ...$data,
            'employee_id' => $employee->id,
            'branch_id' => $employee->branch_id,
            'department_id' => $employee->department_id,
            'duration_minutes' => $duration,
            'status' => 'pending',
        ]);

        $this->notifyEmployee($employee, 'Fazla mesai talebin alındı', $overtime->overtime_date->format('d.m.Y') . ' tarihli fazla mesai talebin onay bekliyor.', route('pdks.notifications'));

        return back()->with('success', 'Fazla mesai talebin oluşturuldu.');
    }

    public function updateOvertimeStatus(Request $request, PdksOvertimeRequest $overtimeRequest)
    {
        abort_if(!$this->visibleEmployees()->whereKey($overtimeRequest->employee_id)->exists(), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected', 'cancelled'])],
            'manager_note' => 'nullable|string|max:1000',
        ]);

        $overtimeRequest->update([
            'status' => $data['status'],
            'manager_note' => $data['manager_note'] ?? null,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->notifyEmployee($overtimeRequest->employee, 'Fazla mesai talebi güncellendi', 'Fazla mesai talebi durumu: ' . $data['status'], route('pdks.notifications'));

        return back()->with('success', 'Fazla mesai talebi güncellendi.');
    }

    public function reports(Request $request)
    {
        $from = $request->input('date_from', now()->startOfMonth()->toDateString());
        $to = $request->input('date_to', now()->endOfMonth()->toDateString());

        $attendance = PdksAttendanceRecord::with('employee.department')
            ->whereHas('employee', fn ($q) => $this->applyEmployeeVisibility($q))
            ->whereBetween('work_date', [$from, $to])
            ->get();

        $employeeSummary = $attendance->groupBy('employee_id')->map(function ($records) {
            $employee = $records->first()->employee;

            return [
                'employee' => $employee,
                'days' => $records->count(),
                'worked_minutes' => $records->sum('worked_minutes'),
                'break_minutes' => $records->sum('break_minutes'),
                'late_minutes' => $records->sum('late_minutes'),
                'early_leave_minutes' => $records->sum('early_leave_minutes'),
            ];
        })->sortBy(fn ($row) => $row['employee']?->name);

        return view('modules.pdks.reports', [
            'page_title' => 'PDKS Raporlar',
            'from' => $from,
            'to' => $to,
            'attendance' => $attendance,
            'employeeSummary' => $employeeSummary,
            'leaveSummary' => PdksLeaveRequest::with('leaveType')
                ->whereHas('employee', fn ($q) => $this->applyEmployeeVisibility($q))
                ->whereBetween('start_date', [$from, $to])
                ->get()
                ->groupBy('status'),
            'overtimeSummary' => PdksOvertimeRequest::whereHas('employee', fn ($q) => $this->applyEmployeeVisibility($q))
                ->whereBetween('overtime_date', [$from, $to])
                ->get()
                ->groupBy('status'),
        ]);
    }

    public function notifications()
    {
        $employee = $this->employeeForUser(auth()->user(), true);

        return view('modules.pdks.notifications', [
            'page_title' => 'PDKS Bildirimleri',
            'notifications' => PdksNotification::where(function ($q) use ($employee) {
                $q->where('user_id', auth()->id())->orWhere('employee_id', $employee->id);
            })->latest()->paginate(30),
        ]);
    }

    public function markNotificationRead(PdksNotification $notification)
    {
        $employee = $this->employeeForUser(auth()->user(), true);
        $allowed = ($notification->user_id && $notification->user_id === auth()->id())
            || ($notification->employee_id && $notification->employee_id === $employee->id);

        abort_if(!$allowed, 403);
        $notification->update(['read_at' => now()]);

        return back()->with('success', 'Bildirim okundu işaretlendi.');
    }

    public function markAllNotificationsRead()
    {
        $employee = $this->employeeForUser(auth()->user(), true);

        PdksNotification::where(function ($q) use ($employee) {
            $q->where('user_id', auth()->id())->orWhere('employee_id', $employee->id);
        })->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'Tüm bildirimler okundu işaretlendi.');
    }

    public function settings()
    {
        return view('modules.pdks.settings', [
            'page_title' => 'PDKS Ayarları',
            'policies' => PdksDevicePolicy::with('branch')->latest()->get(),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storePolicy(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|exists:pdks_device_policies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'allowed_latitude' => 'nullable|numeric',
            'allowed_longitude' => 'nullable|numeric',
            'allowed_radius_meters' => 'required|integer|min:10',
            'max_accuracy_meters' => 'nullable|integer|min:1',
            'allowed_wifi_ssids' => 'nullable|string',
            'allowed_wifi_bssids' => 'nullable|string',
        ]);

        $policy = !empty($data['id']) ? PdksDevicePolicy::findOrFail($data['id']) : new PdksDevicePolicy();
        $policy->fill([
            'branch_id' => $data['branch_id'] ?? null,
            'name' => $data['name'],
            'require_gps' => $request->boolean('require_gps', true),
            'require_wifi' => $request->boolean('require_wifi'),
            'allowed_latitude' => $data['allowed_latitude'] ?? null,
            'allowed_longitude' => $data['allowed_longitude'] ?? null,
            'allowed_radius_meters' => $data['allowed_radius_meters'],
            'max_accuracy_meters' => $data['max_accuracy_meters'] ?? null,
            'allowed_wifi_ssids' => $this->linesToArray($data['allowed_wifi_ssids'] ?? ''),
            'allowed_wifi_bssids' => $this->linesToArray($data['allowed_wifi_bssids'] ?? ''),
            'block_mock_location' => $request->boolean('block_mock_location', true),
            'is_active' => $request->boolean('is_active', true),
        ])->save();

        return back()->with('success', 'PDKS doğrulama politikası kaydedildi.');
    }

    private function employeeForUser($user, bool $createIfMissing = false): ?PdksEmployee
    {
        $employee = PdksEmployee::where('user_id', $user->id)->first();

        if (!$employee && $user->email) {
            $employee = PdksEmployee::whereNull('user_id')->where('email', $user->email)->first();
            if ($employee) {
                $employee->update(['user_id' => $user->id]);
            }
        }

        if (!$employee && $createIfMissing) {
            $employee = PdksEmployee::create([
                'branch_id' => $user->branch_id,
                'department_id' => $user->department_id,
                'user_id' => $user->id,
                'source' => 'user',
                'external_employee_id' => 'user_' . $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'title' => $user->title,
                'is_active' => true,
            ]);
        }

        return $employee;
    }

    private function visibleEmployees()
    {
        return $this->applyEmployeeVisibility(PdksEmployee::query());
    }

    private function applyEmployeeVisibility($query)
    {
        $user = auth()->user();

        if ($user->isSuperAdmin() || $user->isHumanResources()) {
            return $query;
        }

        if ($user->isBranchManager() && $user->branch_id) {
            return $query->where('branch_id', $user->branch_id);
        }

        if ($user->isDeptManager() && $user->department_id) {
            return $query->where('department_id', $user->department_id);
        }

        return $query->where('user_id', $user->id);
    }

    private function locationRules(): array
    {
        return [
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'accuracy' => 'nullable|integer|min:0',
            'wifi_ssid' => 'nullable|string|max:255',
            'wifi_bssid' => 'nullable|string|max:255',
            'is_mock_location' => 'nullable|boolean',
        ];
    }

    private function lateMinutes(?PdksShiftType $shiftType, Carbon $actual): int
    {
        if (!$shiftType) {
            return 0;
        }

        $planned = Carbon::parse($actual->toDateString() . ' ' . $shiftType->start_time)
            ->addMinutes((int) $shiftType->late_grace_minutes);

        return max(0, $planned->diffInMinutes($actual, false));
    }

    private function earlyLeaveMinutes(?PdksShiftType $shiftType, Carbon $actual): int
    {
        if (!$shiftType) {
            return 0;
        }

        $planned = Carbon::parse($actual->toDateString() . ' ' . $shiftType->end_time);
        if ($shiftType->crosses_midnight && $planned->lessThan($actual->copy()->startOfDay())) {
            $planned->addDay();
        }
        $planned->subMinutes((int) $shiftType->early_leave_grace_minutes);

        return max(0, $actual->diffInMinutes($planned, false));
    }

    private function workedMinutes(PdksAttendanceRecord $record): int
    {
        if (!$record->check_in_at) {
            return 0;
        }

        return max(0, $record->check_in_at->diffInMinutes(now()) - (int) $record->breaks()->sum('duration_minutes'));
    }

    private function linesToArray(string $value): array
    {
        return collect(preg_split('/[\r\n,]+/', $value))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    private function notifyEmployee(?PdksEmployee $employee, string $title, string $body, ?string $url = null): void
    {
        if (!$employee) {
            return;
        }

        PdksNotification::create([
            'employee_id' => $employee->id,
            'user_id' => $employee->user_id,
            'type' => 'pdks',
            'title' => $title,
            'body' => $body,
            'url' => $url,
        ]);
    }
}
