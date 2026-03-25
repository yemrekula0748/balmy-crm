<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentComputer;
use App\Models\AgentComputerBrowserHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrowserHistoryController extends Controller
{
    /**
     * Agent'tan tarayıcı geçmişi kabul et.
     *
     * POST /api/agent/browser-history
     * Header: X-Agent-Key: <secret>
     * Body: {
     *   "machine_guid": "...",
     *   "hostname":     "...",
     *   "records": [
     *     {
     *       "username":    "john.doe",
     *       "browser":     "chrome",
     *       "profile":     "Default",
     *       "url":         "https://example.com",
     *       "title":       "Example Domain",
     *       "visit_time":  "2026-03-25 14:32:11",
     *       "visit_count": 3
     *     }
     *   ]
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_guid' => 'required|string|max:100',
            'hostname'     => 'required|string|max:255',
            'records'      => 'required|array',
            'records.*'    => 'array',
        ]);

        $computer = AgentComputer::firstOrCreate(
            ['machine_guid' => $validated['machine_guid']],
            ['hostname'     => $validated['hostname']]
        );

        $records = $validated['records'];

        if (empty($records)) {
            return response()->json(['message' => 'OK', 'inserted' => 0]);
        }

        DB::transaction(function () use ($computer, $records) {
            $now    = now();
            $chunks = array_chunk($records, 500);

            foreach ($chunks as $chunk) {
                $rows = [];
                foreach ($chunk as $rec) {
                    $url = $rec['url'] ?? '';
                    if (empty($url)) {
                        continue;
                    }
                    $rows[] = [
                        'agent_computer_id' => $computer->id,
                        'username'          => $rec['username']    ?? 'unknown',
                        'browser'           => $rec['browser']     ?? 'unknown',
                        'profile'           => $rec['profile']     ?? 'Default',
                        'url'               => $url,
                        'title'             => isset($rec['title'])
                                                ? mb_substr($rec['title'], 0, 500)
                                                : null,
                        'visit_time'        => $rec['visit_time']  ?? $now,
                        'visit_count'       => (int) ($rec['visit_count'] ?? 1),
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];
                }
                if (!empty($rows)) {
                    DB::table('agent_computer_browser_history')->insert($rows);
                }
            }
        });

        return response()->json([
            'message'  => 'OK',
            'inserted' => count($records),
        ], 200);
    }
}
