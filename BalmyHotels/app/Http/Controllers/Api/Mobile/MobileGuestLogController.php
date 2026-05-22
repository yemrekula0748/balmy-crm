<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\GuestLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class MobileGuestLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = GuestLog::with('host')->orderByDesc('check_in_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('visitor_name', 'like', "%{$search}%")
                    ->orWhere('visitor_phone', 'like', "%{$search}%")
                    ->orWhere('visitor_company', 'like', "%{$search}%")
                    ->orWhereHas('host', fn ($hostQuery) => $hostQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $logs = $query->paginate(30);

        return response()->json([
            'data' => $logs->getCollection()->map(fn (GuestLog $log) => $this->guestPayload($log))->values(),
            'total' => $logs->total(),
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'purpose' => ['required', Rule::in(array_keys(GuestLog::PURPOSES))],
            'host_id' => ['nullable', 'exists:users,id'],
            'check_in' => ['required', 'date'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $branchId = $data['branch_id'] ?? $request->user()->branch_id;
        if (! $branchId) {
            return response()->json(['message' => 'branch_id zorunludur.'], 422);
        }

        $host = ! empty($data['host_id']) ? User::find($data['host_id']) : null;

        $guestLog = new GuestLog();
        $guestLog->branch_id = $branchId;
        $guestLog->department_id = $host?->department_id;
        $guestLog->host_user_id = $host?->id;
        $guestLog->created_by = $request->user()->id;
        $guestLog->visitor_name = $data['guest_name'];
        $guestLog->visitor_phone = $data['guest_phone'] ?? null;
        $guestLog->visitor_company = $data['company'] ?? null;
        $guestLog->purpose = $data['purpose'];
        $guestLog->check_in_at = $data['check_in'];
        $guestLog->notes = $data['note'] ?? null;

        if (Schema::hasColumn('guest_logs', 'status')) {
            $guestLog->status = 'active';
        }

        $guestLog->save();

        return response()->json(['data' => $this->guestPayload($guestLog->load('host'))], 201);
    }

    public function checkOut(int $id): JsonResponse
    {
        $guestLog = GuestLog::with('host')->findOrFail($id);
        $guestLog->check_out_at = now();

        if (Schema::hasColumn('guest_logs', 'status')) {
            $guestLog->status = 'completed';
        }

        $guestLog->save();

        return response()->json(['data' => $this->guestPayload($guestLog)]);
    }

    private function guestPayload(GuestLog $log): array
    {
        return [
            'id' => $log->id,
            'guest_name' => $log->visitor_name,
            'guest_phone' => $log->visitor_phone,
            'company' => $log->visitor_company,
            'purpose' => $log->purpose,
            'host' => $log->host ? ['name' => $log->host->name] : null,
            'check_in' => optional($log->check_in_at)->toISOString(),
            'check_out' => optional($log->check_out_at)->toISOString(),
            'status' => $log->check_out_at ? 'completed' : 'active',
        ];
    }
}
