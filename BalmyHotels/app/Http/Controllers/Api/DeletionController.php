<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentComputer;
use App\Models\AgentComputerDeletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeletionController extends Controller
{
    /**
     * Agent'tan silinen dosya listesi kabul et.
     *
     * POST /api/agent/deletions
     * Header: X-Agent-Key: <secret>
     * Body: {
     *   "machine_guid": "...",
     *   "hostname":     "PC001",
     *   "deletions": [
     *     {
     *       "name":       "rapor.xlsx",
     *       "path":       "c:\\users\\ahmet\\documents\\rapor.xlsx",
     *       "directory":  "c:\\users\\ahmet\\documents",
     *       "size":       24576,
     *       "modified":   "2026-04-16T10:23:00",
     *       "deleted_at": "2026-04-16T22:05:12"
     *     }
     *   ]
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_guid' => 'required|string|max:100',
            'hostname'     => 'required|string|max:255',
            'deletions'    => 'required|array',
            'deletions.*'  => 'array',
        ]);

        $computer = AgentComputer::firstOrCreate(
            ['machine_guid' => $validated['machine_guid']],
            ['hostname'     => $validated['hostname']]
        );

        $deletions = $validated['deletions'];

        if (empty($deletions)) {
            return response()->json(['message' => 'OK', 'inserted' => 0]);
        }

        $inserted = 0;

        DB::transaction(function () use ($computer, $deletions, &$inserted) {
            $now    = now();
            $chunks = array_chunk($deletions, 200);

            foreach ($chunks as $chunk) {
                $rows = [];
                foreach ($chunk as $d) {
                    $rows[] = [
                        'agent_computer_id' => $computer->id,
                        'name'              => isset($d['name'])
                                                ? mb_substr($d['name'], 0, 500)
                                                : null,
                        'path'              => isset($d['path'])
                                                ? mb_substr($d['path'], 0, 1000)
                                                : null,
                        'directory'         => isset($d['directory'])
                                                ? mb_substr($d['directory'], 0, 1000)
                                                : null,
                        'size'              => isset($d['size']) ? (int) $d['size'] : null,
                        'modified_at'       => $d['modified'] ?? null,
                        'deleted_at'        => $d['deleted_at'] ?? $now,
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];
                }
                AgentComputerDeletion::insert($rows);
                $inserted += count($rows);
            }

            $computer->update(['last_seen_at' => $now]);
        });

        return response()->json(['message' => 'OK', 'inserted' => $inserted]);
    }
}
