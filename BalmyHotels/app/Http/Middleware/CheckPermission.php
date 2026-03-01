<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\RolePermission;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Kullanım: middleware('perm:module,action')
     * action: index | show | create | edit | delete
     */
    public function handle(Request $request, Closure $next, string $module, string $action = 'index'): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Hesabınız devre dışı bırakılmış.');
        }

        // super_admin her şeye erişebilir
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if (!$user->hasPermission($module, $action)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Bu işlem için yetkiniz yok.'], 403);
            }
            abort(403, 'Bu işlem için yetkiniz bulunmamaktadır.');
        }

        return $next($request);
    }
}
