<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentComputer;
use App\Models\AgentComputerCommand;
use Illuminate\Http\Request;

class CommandController extends Controller
{
    /**
     * GET /api/agent/commands/pending?machine_guid=...
     * Agent tarafından her polling döngüsünde çağrılır.
     * Bekleyen komutları döner ve durumunu pending → sent yapar.
     */
    public function pending(Request $request)
    {
        $machineGuid = $request->query('machine_guid');
        if (!$machineGuid) {
            return response()->json([]);
        }

        $computer = AgentComputer::where('machine_guid', $machineGuid)->first();
        if (!$computer) {
            return response()->json([]);
        }

        $commands = $computer->commands()
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get(['id', 'type', 'payload']);

        if ($commands->isNotEmpty()) {
            $computer->commands()
                ->whereIn('id', $commands->pluck('id'))
                ->update(['status' => 'sent']);
        }

        return response()->json($commands);
    }

    /**
     * POST /api/agent/commands/result
     * Body: { command_id, success, output, machine_guid }
     */
    public function result(Request $request)
    {
        $request->validate([
            'command_id'   => 'required|integer',
            'success'      => 'required|boolean',
            'output'       => 'nullable|string|max:4096',
            'machine_guid' => 'required|string',
        ]);

        $command = AgentComputerCommand::findOrFail($request->command_id);

        // Güvenlik: komutun bu agent'a ait olduğunu doğrula
        $computer = AgentComputer::where('machine_guid', $request->machine_guid)->first();
        if (!$computer || $command->agent_computer_id !== $computer->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $command->update([
            'status'      => $request->boolean('success') ? 'completed' : 'failed',
            'output'      => $request->input('output'),
            'success'     => $request->boolean('success'),
            'executed_at' => now(),
        ]);

        return response()->json(['message' => 'OK']);
    }
}
