<?php

namespace App\Http\Controllers\Modules;

use App\Models\AgentComputer;
use App\Models\AgentComputerCommand;
use App\Models\AgentComputerInstalledProgram;
use Illuminate\Http\Request;

class AgentInventoryController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('it_agent_inventory',
            ['index', 'stats'],
            ['show', 'programs'],
            [],
            [],
            ['destroy']
        );
    }

    public function index(Request $request)
    {
        $query = AgentComputer::with(['networkAdapters' => fn($q) => $q->where('is_active', true), 'disks', 'antivirus', 'hardware', 'securitySnapshot']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('hostname', 'like', "%{$search}%")
                  ->orWhereHas('networkAdapters', fn($a) => $a->where('ip_address', 'like', "%{$search}%"));
            });
        }

        if ($domain = $request->input('domain')) {
            $query->where('domain_name', $domain);
        }

        if ($os = $request->input('os')) {
            $query->where('os_product_name', 'like', "%{$os}%");
        }

        if ($request->boolean('av_disabled')) {
            $query->whereDoesntHave('antivirus', fn($q) => $q->where('is_enabled', true));
        }

        if ($request->boolean('disk_warning')) {
            $query->whereHas('disks', fn($q) => $q->where('usage_percent', '>', 80));
        }

        if ($request->boolean('rdp_enabled')) {
            $query->whereHas('security', fn($q) => $q->where('rdp_enabled', true));
        }

        $computers = $query->orderByDesc('last_seen_at')->paginate(50)->withQueryString();

        $domains = AgentComputer::whereNotNull('domain_name')->distinct()->pluck('domain_name');
        $osList  = AgentComputer::whereNotNull('os_product_name')->distinct()->pluck('os_product_name');

        $stats = [
            'total'      => AgentComputer::count(),
            'online'     => AgentComputer::where('last_seen_at', '>=', now()->subMinutes(10))->count(),
            'recent'     => AgentComputer::where('last_seen_at', '>=', now()->subHours(24))
                                          ->where('last_seen_at', '<', now()->subMinutes(10))->count(),
            'av_issue'   => AgentComputer::whereDoesntHave('antivirus', fn($q) => $q->where('is_enabled', true))->count(),
            'disk_warn'  => AgentComputer::whereHas('disks', fn($q) => $q->where('usage_percent', '>', 85))->count(),
            'rdp_open'   => AgentComputer::whereHas('security', fn($q) => $q->where('rdp_enabled', true))->count(),
            'threats'    => AgentComputer::whereHas('securitySnapshot', fn($q) => $q->where('alert_count', '>', 0))->count(),
        ];

        return view('modules.bilgi_islem.agent_inventory.index', compact('computers', 'domains', 'osList', 'stats'));
    }

    public function clearSnapshotFields(Request $request)
    {
        \App\Models\AgentComputerSecuritySnapshot::query()->update([
            'scheduled_tasks_24h' => null,
            'usb_history'         => null,
        ]);
        return response()->json(['success' => true, 'message' => 'Zamanlanmış görevler ve USB geçmişi tüm makinelerden temizlendi.']);
    }

    public function show(AgentComputer $agentComputer)
    {
        $agentComputer->load(['hardware', 'security', 'networkAdapters', 'disks', 'antivirus', 'mail', 'mailAccounts', 'securitySnapshot', 'usbDevices']);
        $programCount = AgentComputerInstalledProgram::where('agent_computer_id', $agentComputer->id)->count();
        $recentCommands = $agentComputer->commands()
            ->with('createdBy')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
        $latestScreenshot = $agentComputer->screenshots()->first();
        $allScreenshots   = $agentComputer->screenshots()->limit(10)->get();

        return view('modules.bilgi_islem.agent_inventory.show', compact('agentComputer', 'programCount', 'recentCommands', 'latestScreenshot', 'allScreenshots'));
    }

    public function programs(Request $request, AgentComputer $agentComputer)
    {
        $query = $agentComputer->installedPrograms();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%");
            });
        }

        $programs = $query->orderBy('name')->paginate(100)->withQueryString();

        return view('modules.bilgi_islem.agent_inventory.programs', compact('agentComputer', 'programs'));
    }

    public function fileEvents(Request $request, AgentComputer $agentComputer)
    {
        $query = $agentComputer->fileEvents()->orderBy('event_time', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('object_name', 'like', "%{$search}%")
                  ->orWhere('subject_user', 'like', "%{$search}%")
                  ->orWhere('process_name', 'like', "%{$search}%");
            });
        }

        if ($from = $request->input('from')) {
            $query->where('event_time', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->where('event_time', '<=', $to . ' 23:59:59');
        }

        $events = $query->paginate(100)->withQueryString();

        return view('modules.bilgi_islem.agent_inventory.file_events', compact('agentComputer', 'events'));
    }

    public function programEvents(Request $request, AgentComputer $agentComputer)
    {
        $query = $agentComputer->programEvents()->orderBy('detected_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('program_name', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('event_type', $type);
        }

        if ($from = $request->input('from')) {
            $query->where('detected_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->where('detected_at', '<=', $to . ' 23:59:59');
        }

        $events = $query->paginate(100)->withQueryString();

        return view('modules.bilgi_islem.agent_inventory.program_events', compact('agentComputer', 'events'));
    }

    public function deletions(Request $request, AgentComputer $agentComputer)
    {
        $query = $agentComputer->deletions()->orderBy('deleted_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('path', 'like', "%{$search}%")
                  ->orWhere('directory', 'like', "%{$search}%");
            });
        }

        if ($from = $request->input('from')) {
            $query->where('deleted_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->where('deleted_at', '<=', $to . ' 23:59:59');
        }

        $deletions = $query->paginate(100)->withQueryString();

        return view('modules.bilgi_islem.agent_inventory.deletions', compact('agentComputer', 'deletions'));
    }

    public function browserHistory(Request $request, AgentComputer $agentComputer)
    {
        $query = $agentComputer->browserHistory()->orderBy('visit_time', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('url', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($user = $request->input('username')) {
            $query->where('username', 'like', "%{$user}%");
        }

        if ($browser = $request->input('browser')) {
            $query->where('browser', $browser);
        }

        if ($from = $request->input('from')) {
            $query->where('visit_time', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->where('visit_time', '<=', $to . ' 23:59:59');
        }

        $history  = $query->paginate(100)->withQueryString();
        $browsers = $agentComputer->browserHistory()->distinct()->pluck('browser');
        $users    = $agentComputer->browserHistory()->distinct()->pluck('username');

        return view('modules.bilgi_islem.agent_inventory.browser_history', compact('agentComputer', 'history', 'browsers', 'users'));
    }

    public function stats()
    {
        $total       = AgentComputer::count();
        $domain      = AgentComputer::where('is_domain_joined', true)->count();
        $avDisabled  = AgentComputer::whereDoesntHave('antivirus', fn($q) => $q->where('is_enabled', true))
                                    ->whereHas('antivirus')
                                    ->count();
        $diskWarning = AgentComputer::whereHas('disks', fn($q) => $q->where('usage_percent', '>', 80))->count();
        $rdpEnabled  = AgentComputer::whereHas('security', fn($q) => $q->where('rdp_enabled', true))->count();
        $seenLastHour = AgentComputer::where('last_seen_at', '>=', now()->subHour())->count();
        $seenLast24h  = AgentComputer::where('last_seen_at', '>=', now()->subDay())->count();
        $neverSeen    = AgentComputer::whereNull('last_seen_at')->count();

        $osBreakdown = AgentComputer::selectRaw('os_product_name, count(*) as total')
                                    ->whereNotNull('os_product_name')
                                    ->groupBy('os_product_name')
                                    ->pluck('total', 'os_product_name');

        return view('modules.bilgi_islem.agent_inventory.stats', compact(
            'total', 'domain', 'avDisabled', 'diskWarning', 'rdpEnabled',
            'seenLastHour', 'seenLast24h', 'neverSeen', 'osBreakdown'
        ));
    }

    public function sendCommand(Request $request, AgentComputer $agentComputer)
    {
        $request->validate([
            'type'       => 'required|in:shutdown,restart,logoff,cmd,msgbox',
            'payload'    => 'nullable|string|max:1024',
            'expires_in' => 'nullable|integer|min:0|max:1440', // dakika
        ]);

        $agentComputer->commands()->create([
            'type'       => $request->type,
            'payload'    => $request->payload,
            'status'     => 'pending',
            'expires_at' => $request->filled('expires_in')
                                ? now()->addMinutes((int) $request->expires_in)
                                : null,
            'created_by' => auth()->id(),
        ]);

        return back()->with('cmd_success', AgentComputerCommand::TYPES[$request->type] . ' komutu gönderildi.');
    }

    public function requestScreenshot(AgentComputer $agentComputer)
    {
        $command = $agentComputer->commands()->create([
            'type'       => 'screenshot',
            'payload'    => null,
            'status'     => 'pending',
            'expires_at' => now()->addMinutes(2),
            'created_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'command_id' => $command->id]);
    }

    public function clearScreenshots(AgentComputer $agentComputer)
    {
        $agentComputer->screenshots()->delete();
        return response()->json(['success' => true]);
    }

    public function latestScreenshot(Request $request, AgentComputer $agentComputer)
    {
        $query = $agentComputer->screenshots();

        if ($request->filled('after')) {
            $query->where('captured_at', '>', $request->input('after'));
        }

        $screenshot = $query->first();

        if (!$screenshot) {
            return response()->json(['url' => null]);
        }

        return response()->json([
            'url'         => $screenshot->image_url,
            'captured_at' => $screenshot->captured_at->diffForHumans(),
        ]);
    }

    public function wakeOnLan(AgentComputer $agentComputer)
    {
        $adapter = $agentComputer->networkAdapters()
            ->whereNotNull('mac_address')
            ->where('is_active', true)
            ->first()
            ?? $agentComputer->networkAdapters()->whereNotNull('mac_address')->first();

        if (!$adapter || !$adapter->mac_address) {
            return back()->with('error', '"' . $agentComputer->hostname . '" için MAC adresi bulunamadı. WOL gönderilemedi.');
        }

        $mac = strtoupper(preg_replace('/[^0-9a-fA-F]/', '', $adapter->mac_address));

        if (strlen($mac) !== 12) {
            return back()->with('error', 'Geçersiz MAC adresi: ' . $adapter->mac_address);
        }

        // Broadcast adresini adapter'ın IP + subnet_mask'ından hesapla (directed broadcast)
        $targetIp   = $adapter->ip_address;
        $subnetMask = $adapter->subnet_mask;
        if ($targetIp && $subnetMask && ip2long($subnetMask) !== false) {
            $broadcastIp = long2ip(ip2long($targetIp) | (~ip2long($subnetMask) & 0xFFFFFFFF));
        } else {
            $broadcastIp = '255.255.255.255';
        }

        // Magic packet: 6×FF + MAC×16
        $macBytes = '';
        for ($i = 0; $i < 12; $i += 2) {
            $macBytes .= chr(hexdec(substr($mac, $i, 2)));
        }
        $packet = str_repeat(chr(0xFF), 6) . str_repeat($macBytes, 16);

        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock === false) {
            return back()->with('error', 'UDP soket oluşturulamadı.');
        }
        socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
        socket_sendto($sock, $packet, strlen($packet), 0, $broadcastIp, 9);
        socket_close($sock);

        return back()->with('success', '"' . $agentComputer->hostname . '" için Wake-on-LAN paketi gönderildi (' . $adapter->mac_address . ').');
    }

    public function destroy(AgentComputer $agentComputer)
    {
        $agentComputer->delete();
        return redirect()->route('it.agent.index')->with('success', 'Kayıt silindi.');
    }
}
