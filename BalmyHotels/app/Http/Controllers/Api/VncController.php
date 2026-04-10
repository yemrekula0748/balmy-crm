<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentComputer;
use App\Models\VncFrame;
use App\Models\VncInput;
use Illuminate\Http\Request;

class VncController extends Controller
{
    /**
     * POST /api/agent/vnc/frame
     * Agent pushes the latest screen frame as base64 JPEG.
     */
    public function storeFrame(Request $request)
    {
        $request->validate([
            'image_data'   => 'required|string',
            'machine_guid' => 'nullable|string|max:255',
            'hostname'     => 'nullable|string|max:255',
            'screen_w'     => 'nullable|integer|min:1|max:9999',
            'screen_h'     => 'nullable|integer|min:1|max:9999',
            'seq'          => 'nullable|integer|min:0',
        ]);

        // Validate JPEG: base64 decode + magic bytes FF D8
        $binary = base64_decode($request->image_data, true);
        if ($binary === false || strlen($binary) < 3 || substr($binary, 0, 2) !== "\xFF\xD8") {
            return response()->json(['error' => 'Invalid image_data: expected base64-encoded JPEG'], 422);
        }

        $computer = $this->findComputer($request);
        if (!$computer) {
            return response()->json(['error' => 'Computer not found'], 404);
        }

        VncFrame::updateOrCreate(
            ['agent_computer_id' => $computer->id],
            [
                'image_data' => $request->image_data,
                'screen_w'   => $request->input('screen_w', 1920),
                'screen_h'   => $request->input('screen_h', 1080),
                'seq'        => $request->input('seq', 0),
            ]
        );

        return response()->json(['ok' => true]);
    }

    /**
     * GET /api/agent/vnc/input?machine_guid=...
     * Agent polls for pending input events — with short long-polling to minimise latency.
     */
    public function getInput(Request $request)
    {
        $computer = $this->findComputer($request);
        if (!$computer) {
            return response()->json(['error' => 'Computer not found'], 404);
        }

        $deadline = microtime(true) + 0.5; // wait up to 500 ms
        do {
            $inputs = VncInput::where('agent_computer_id', $computer->id)
                ->where('consumed', false)
                ->orderBy('created_at')
                ->get();

            if ($inputs->isNotEmpty()) {
                $allEvents = [];
                foreach ($inputs as $input) {
                    $allEvents = array_merge($allEvents, $input->events ?? []);
                }

                VncInput::where('agent_computer_id', $computer->id)
                    ->where('consumed', false)
                    ->update(['consumed' => true]);

                return response()->json($allEvents);
            }

            if (microtime(true) >= $deadline) break;
            usleep(50000); // 50 ms sleep before retrying
        } while (true);

        return response()->json([]);
    }

    private function findComputer(Request $request): ?AgentComputer
    {
        $computer = null;
        if ($request->filled('machine_guid')) {
            $computer = AgentComputer::where('machine_guid', $request->machine_guid)->first();
        }
        if (!$computer && $request->filled('hostname')) {
            $computer = AgentComputer::where('hostname', $request->hostname)->first();
        }
        return $computer;
    }
}
