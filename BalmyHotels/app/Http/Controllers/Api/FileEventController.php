<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentComputer;
use App\Models\AgentComputerFileEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FileEventController extends Controller
{
    /**
     * Agent'tan dosya silme olay listesi kabul et.
     *
     * POST /api/agent/file-events
     * Header: X-Agent-Key: <secret>
     * Body: {
     *   "machine_guid": "...",
     *   "hostname":     "...",
     *   "events": [
     *     {
     *       "event_id":        4663,
     *       "event_time":      "2025-03-25T14:32:11",
     *       "subject_user":    "john.doe",
     *       "subject_domain":  "COMPANY",
     *       "subject_logon_id":"0x1A2B3C",
     *       "object_name":     "C:\\Users\\john.doe\\Desktop\\rapor.xlsx",
     *       "object_type":     "File",
     *       "process_name":    "C:\\Windows\\explorer.exe",
     *       "access_mask":     "0x10000",
     *       "handle_id":       "0x7F8"
     *     }
     *   ]
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_guid' => 'required|string|max:100',
            'hostname'     => 'required|string|max:255',
            'events'       => 'required|array',
            'events.*'     => 'array',
        ]);

        $computer = AgentComputer::firstOrCreate(
            ['machine_guid' => $validated['machine_guid']],
            ['hostname'     => $validated['hostname']]
        );

        $events = $validated['events'];

        if (empty($events)) {
            return response()->json(['message' => 'OK', 'inserted' => 0]);
        }

        DB::transaction(function () use ($computer, $events) {
            $now    = now();
            $chunks = array_chunk($events, 200);

            foreach ($chunks as $chunk) {
                $rows = [];
                foreach ($chunk as $ev) {
                    $rows[] = [
                        'agent_computer_id' => $computer->id,
                        'event_id'          => (int) ($ev['event_id'] ?? 4663),
                        'event_time'        => $ev['event_time'] ?? $now,
                        'subject_user'      => $ev['subject_user']      ?? null,
                        'subject_domain'    => $ev['subject_domain']    ?? null,
                        'subject_logon_id'  => $ev['subject_logon_id']  ?? null,
                        'object_name'       => isset($ev['object_name'])
                                                ? mb_substr($ev['object_name'], 0, 1000)
                                                : null,
                        'object_type'       => $ev['object_type']       ?? null,
                        'process_name'      => isset($ev['process_name'])
                                                ? mb_substr($ev['process_name'], 0, 500)
                                                : null,
                        'access_mask'       => $ev['access_mask']       ?? null,
                        'handle_id'         => $ev['handle_id']         ?? null,
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];
                }
                DB::table('agent_computer_file_events')->insert($rows);
            }
        });

        return response()->json([
            'message'  => 'OK',
            'inserted' => count($events),
        ], 200);
    }
}
