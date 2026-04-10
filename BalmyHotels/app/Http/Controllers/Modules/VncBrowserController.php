<?php

namespace App\Http\Controllers\Modules;

use App\Models\AgentComputer;
use App\Models\AgentComputerCommand;
use App\Models\VncFrame;
use App\Models\VncInput;
use Illuminate\Http\Request;

class VncBrowserController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('it_agent_inventory',
            [],
            ['show', 'frame', 'start', 'stop', 'input'],
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

    /** POST ajan-envanter/{agentComputer}/vnc/start */
    public function start(Request $request, AgentComputer $agentComputer)
    {
        $request->validate([
            'password' => 'nullable|string|max:255',
        ]);

        $agentComputer->commands()->create([
            'type'       => 'vnc_start',
            'payload'    => json_encode([
                'password' => $request->input('password', ''),
                'port'     => 5900,
            ]),
            'status'     => 'pending',
            'expires_at' => now()->addMinutes(5),
            'created_by' => auth()->id(),
        ]);

        return response()->json(['ok' => true]);
    }

    /** POST ajan-envanter/{agentComputer}/vnc/stop */
    public function stop(AgentComputer $agentComputer)
    {
        $agentComputer->commands()->create([
            'type'       => 'vnc_stop',
            'payload'    => null,
            'status'     => 'pending',
            'expires_at' => now()->addMinutes(2),
            'created_by' => auth()->id(),
        ]);

        VncFrame::where('agent_computer_id', $agentComputer->id)->delete();
        VncInput::where('agent_computer_id', $agentComputer->id)->delete();

        return response()->json(['ok' => true]);
    }

    /** GET ajan-envanter/{agentComputer}/vnc/frame */
    public function frame(AgentComputer $agentComputer)
    {
        $frame = VncFrame::where('agent_computer_id', $agentComputer->id)->first();

        if (!$frame) {
            return response()->json(['image_data' => null]);
        }

        return response()->json([
            'image_data' => $frame->image_data,
            'screen_w'   => $frame->screen_w,
            'screen_h'   => $frame->screen_h,
            'seq'        => $frame->seq,
            'ts'         => $frame->updated_at ? (int) ($frame->updated_at->getPreciseTimestamp(3)) : 0,
        ]);
    }

    /** POST ajan-envanter/{agentComputer}/vnc/input */
    public function input(Request $request, AgentComputer $agentComputer)
    {
        $request->validate([
            'events'          => 'required|array|max:500',
            'events.*.type'   => 'required|string|in:mouse_move,mouse_down,mouse_up,scroll,key_down,key_up',
            'events.*.x'      => 'sometimes|integer|min:0|max:9999',
            'events.*.y'      => 'sometimes|integer|min:0|max:9999',
            'events.*.button' => 'sometimes|integer|min:1|max:3',
            'events.*.delta'  => 'sometimes|integer',
            'events.*.keysym' => 'sometimes|integer',
        ]);

        $events = $request->events;

        // One unconsumed row per computer; merge events into it
        $existing = VncInput::where('agent_computer_id', $agentComputer->id)
            ->where('consumed', false)
            ->first();

        if ($existing) {
            $existing->update(['events' => array_merge($existing->events ?? [], $events)]);
        } else {
            VncInput::create([
                'agent_computer_id' => $agentComputer->id,
                'events'            => $events,
                'consumed'          => false,
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
