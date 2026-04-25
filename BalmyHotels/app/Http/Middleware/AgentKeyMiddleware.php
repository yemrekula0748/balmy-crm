<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AgentKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $incoming = $request->header('X-Agent-Key', '');
        $expected = (string) config('agent.key', '');

        if (empty($expected) || !hash_equals($expected, $incoming)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
