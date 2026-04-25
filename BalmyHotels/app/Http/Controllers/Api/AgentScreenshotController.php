<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentComputer;
use App\Models\AgentComputerCommand;
use App\Models\AgentScreenshot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AgentScreenshotController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image_data'   => 'required|string',
            'captured_at'  => 'required|date',
            'machine_guid' => 'nullable|string|max:255',
            'hostname'     => 'nullable|string|max:255',
            'command_id'   => 'nullable|integer',
        ]);

        // Find computer by machine_guid first, fallback to hostname
        $computer = null;
        if ($request->filled('machine_guid')) {
            $computer = AgentComputer::where('machine_guid', $request->machine_guid)->first();
        }
        if (!$computer && $request->filled('hostname')) {
            $computer = AgentComputer::where('hostname', $request->hostname)->first();
        }
        if (!$computer) {
            return response()->json(['error' => 'Computer not found'], 404);
        }

        // Decode base64 image data
        $binary = base64_decode($request->image_data, true);
        if ($binary === false) {
            return response()->json(['error' => 'Invalid image_data: base64 decode failed'], 422);
        }

        // Validate JPEG magic bytes: FF D8 FF
        if (strlen($binary) < 3 || substr($binary, 0, 3) !== "\xFF\xD8\xFF") {
            return response()->json(['error' => 'Invalid image format: expected JPEG'], 422);
        }

        // Validate file size: must be between 100 KB and 5 MB
        $size = strlen($binary);
        if ($size < 102400) {
            return response()->json(['error' => 'Image too small (minimum 100 KB)'], 422);
        }
        if ($size > 5242880) {
            return response()->json(['error' => 'Image too large (maximum 5 MB)'], 422);
        }

        // Build storage path: screenshots/{computer_id}/YYYY-MM-DD_HH-mm-ss.jpg
        $capturedAt = Carbon::parse($request->captured_at);
        $filename   = $capturedAt->format('Y-m-d') . '_' . $capturedAt->format('H-i-s') . '.jpg';
        $dir        = 'screenshots/' . $computer->id;
        $path       = $dir . '/' . $filename;

        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }

        Storage::disk('public')->put($path, $binary);

        $screenshot = AgentScreenshot::create([
            'agent_computer_id' => $computer->id,
            'command_id'        => $request->command_id,
            'image_path'        => $path,
            'captured_at'       => $capturedAt,
        ]);

        // Mark associated command as completed
        if ($request->filled('command_id')) {
            AgentComputerCommand::where('id', $request->command_id)
                ->where('agent_computer_id', $computer->id)
                ->update([
                    'status'      => 'completed',
                    'success'     => true,
                    'executed_at' => now(),
                ]);
        }

        return response()->json(['message' => 'screenshot saved', 'id' => $screenshot->id], 201);
    }
}
