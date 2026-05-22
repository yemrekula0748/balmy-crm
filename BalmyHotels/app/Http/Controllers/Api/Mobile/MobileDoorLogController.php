<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\DoorLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileDoorLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DoorLog::with('user')->orderByDesc('logged_at');

        if ($request->filled('date')) {
            $query->whereDate('logged_at', $request->input('date'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->paginate(30);

        return response()->json([
            'data' => $logs->getCollection()->map(fn (DoorLog $log) => $this->logPayload($log))->values(),
            'total' => $logs->total(),
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
        ]);
    }

    private function logPayload(DoorLog $log): array
    {
        return [
            'id' => $log->id,
            'user' => $log->user ? [
                'name' => $log->user->name,
                'email' => $log->user->email,
            ] : null,
            'direction' => $log->type,
            'type' => $log->type,
            'time' => optional($log->logged_at)->toISOString(),
            'created_at' => optional($log->created_at)->toISOString(),
            'card_number' => $log->card_number ?? null,
            'door_name' => $log->door_name ?? null,
        ];
    }
}
