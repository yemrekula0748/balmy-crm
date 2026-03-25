<?php

namespace App\Http\Controllers\Modules;

use App\Models\AgentComputer;
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
        $query = AgentComputer::with(['networkAdapters' => fn($q) => $q->where('is_active', true), 'disks', 'antivirus', 'hardware']);

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
        ];

        return view('modules.bilgi_islem.agent_inventory.index', compact('computers', 'domains', 'osList', 'stats'));
    }

    public function show(AgentComputer $agentComputer)
    {
        $agentComputer->load(['hardware', 'security', 'networkAdapters', 'disks', 'antivirus', 'mail', 'mailAccounts']);
        $programCount = AgentComputerInstalledProgram::where('agent_computer_id', $agentComputer->id)->count();

        return view('modules.bilgi_islem.agent_inventory.show', compact('agentComputer', 'programCount'));
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

    public function destroy(AgentComputer $agentComputer)
    {
        $agentComputer->delete();
        return redirect()->route('it.agent.index')->with('success', 'Kayıt silindi.');
    }
}
