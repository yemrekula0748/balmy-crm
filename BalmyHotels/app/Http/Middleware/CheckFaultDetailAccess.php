<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFaultDetailAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Hesabınız devre dışı bırakılmış.');
        }

        $canSeeDetail = $user->isSuperAdmin()
            || $user->hasPermission('faults', 'show')
            || $user->hasPermission('fault_room_reports', 'index')
            || $user->hasPermission('fault_type_reports', 'index');

        if (!$canSeeDetail) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Bu işlem için yetkiniz yok.'], 403);
            }

            abort(403, 'Bu işlem için yetkiniz bulunmamaktadır.');
        }

        return $next($request);
    }
}
