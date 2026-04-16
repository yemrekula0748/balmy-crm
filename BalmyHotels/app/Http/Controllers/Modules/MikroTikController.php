<?php

namespace App\Http\Controllers\Modules;

use App\Services\MikroTikService;
use Illuminate\Http\Request;

class MikroTikController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('mikrotik', ['index'], [], [], [], []);
    }

    public function index()
    {
        $mikrotik = new MikroTikService();
        $data = $mikrotik->getDashboardData();

        return view('modules.bilgi_islem.mikrotik.index', compact('data'));
    }

    /**
     * AJAX: Sadece aktif hotspot oturumlarını yenile.
     */
    public function hotspotActive()
    {
        $mikrotik = new MikroTikService();
        if (!$mikrotik->connect()) {
            return response()->json(['error' => 'Bağlantı hatası'], 503);
        }
        $active = $mikrotik->getHotspotActive();
        $mikrotik->disconnect();

        return response()->json($active);
    }

    /**
     * AJAX: DHCP lease listesini yenile.
     */
    public function dhcpLeases()
    {
        $mikrotik = new MikroTikService();
        if (!$mikrotik->connect()) {
            return response()->json(['error' => 'Bağlantı hatası'], 503);
        }
        $leases = $mikrotik->getDhcpLeases();
        $mikrotik->disconnect();

        return response()->json($leases);
    }

    /**
     * AJAX: Sistem kaynakları (CPU/RAM) anlık.
     */
    public function resources()
    {
        $mikrotik = new MikroTikService();
        if (!$mikrotik->connect()) {
            return response()->json(['error' => 'Bağlantı hatası'], 503);
        }
        $res = $mikrotik->getSystemResources();
        $mikrotik->disconnect();

        return response()->json($res);
    }
}
