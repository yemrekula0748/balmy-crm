<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentComputer;
use App\Models\AgentScreenshot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AgentAutoScreenshotController extends Controller
{
    /**
     * Her rapor döngüsünde agent'tan otomatik gelen ekran görüntüsü.
     *
     * POST /api/agent/auto-screenshot
     * Header: X-Agent-Key: <secret>
     * Body: {
     *   "machine_guid": "...",
     *   "hostname":     "PC001",
     *   "image_data":   "<base64 JPEG>",
     *   "captured_at":  "2026-04-16T22:10:00"
     * }
     *
     * Her bilgisayar için en fazla 10 screenshot saklanır.
     */
    public function store(Request $request)
    {
        $request->validate([
            'machine_guid' => 'required|string|max:100',
            'hostname'     => 'required|string|max:255',
            'image_data'   => 'required|string',
            'captured_at'  => 'required|date',
        ]);

        $computer = AgentComputer::where('machine_guid', $request->machine_guid)->first()
            ?? AgentComputer::where('hostname', $request->hostname)->first();

        if (!$computer) {
            return response()->json(['error' => 'Computer not found'], 404);
        }

        $binary = base64_decode($request->image_data, true);
        if ($binary === false) {
            return response()->json(['error' => 'Invalid image_data: base64 decode failed'], 422);
        }

        // JPEG magic bytes: FF D8 FF
        if (strlen($binary) < 3 || substr($binary, 0, 3) !== "\xFF\xD8\xFF") {
            return response()->json(['error' => 'Invalid image format: expected JPEG'], 422);
        }

        if (strlen($binary) > 10485760) { // 10 MB max for auto screenshots
            return response()->json(['error' => 'Image too large (maximum 10 MB)'], 422);
        }

        $capturedAt = Carbon::parse($request->captured_at);
        $filename   = 'auto_' . $capturedAt->format('Y-m-d') . '_' . $capturedAt->format('H-i-s') . '.jpg';
        $dir        = 'screenshots/' . $computer->id;
        $path       = $dir . '/' . $filename;

        Storage::disk('public')->put($path, $binary);

        AgentScreenshot::create([
            'agent_computer_id' => $computer->id,
            'command_id'        => null,
            'image_path'        => $path,
            'captured_at'       => $capturedAt,
        ]);

        // last_screenshot_at güncelle
        $computer->update(['last_screenshot_at' => $capturedAt]);

        // En fazla 10 screenshot sakla — eskilerini temizle
        $all = AgentScreenshot::where('agent_computer_id', $computer->id)
            ->orderByDesc('captured_at')
            ->get();

        if ($all->count() > 10) {
            foreach ($all->skip(10) as $old) {
                Storage::disk('public')->delete($old->image_path);
                $old->delete();
            }
        }

        return response()->json(['message' => 'OK']);
    }
}
