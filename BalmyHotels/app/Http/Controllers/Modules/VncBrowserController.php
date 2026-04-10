<?php

namespace App\Http\Controllers\Modules;

use App\Models\AgentComputer;
use Illuminate\Http\Request;

class VncBrowserController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('it_agent_inventory',
            [],
            ['show', 'connect'],
            [],
            [],
            []
        );
    }

    /** GET ajan-envanter/{agentComputer}/vnc → viewer page */
    public function show(AgentComputer $agentComputer)
    {
        $agentComputer->load(['networkAdapters']);
        return view('modules.bilgi_islem.agent_inventory.vnc', compact('agentComputer'));
    }

    /**
     * POST ajan-envanter/{agentComputer}/vnc/connect
     * Returns the WebSocket URL for noVNC to connect to directly.
     * Agent runs websockify on port 6080 which bridges WS → TightVNC :5900
     */
    public function connect(Request $request, AgentComputer $agentComputer)
    {
        $request->validate([
            'password' => 'nullable|string|max:255',
        ]);

        $agentComputer->load(['networkAdapters']);
        $ip = $agentComputer->ip_address;

        if (!$ip) {
            return response()->json(['ok' => false, 'error' => 'Makineye ait aktif IP adresi bulunamadı.'], 422);
        }

        $wsPort = config('dz.vnc_ws_port', 6080);

        return response()->json([
            'ok'       => true,
            'ws_url'   => "ws://{$ip}:{$wsPort}",
            'password' => $request->input('password', ''),
        ]);
    }
}
