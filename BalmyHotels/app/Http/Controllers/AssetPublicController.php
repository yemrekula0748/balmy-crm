<?php

namespace App\Http\Controllers;

use App\Models\Asset;

class AssetPublicController extends Controller
{
    public function show(string $token)
    {
        $asset = Asset::where('qr_token', $token)
            ->with(['category', 'branch', 'exits', 'histories.user'])
            ->firstOrFail();

        return view('public.asset-qr', compact('asset'));
    }
}
