<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentComputer;
use App\Models\AgentComputerHardware;
use App\Models\AgentComputerNetworkAdapter;
use App\Models\AgentComputerDisk;
use App\Models\AgentComputerAntivirus;
use App\Models\AgentComputerSecurity;
use App\Models\AgentComputerInstalledProgram;
use App\Models\AgentComputerMail;
use App\Models\AgentComputerMailAccount;
use App\Models\AgentComputerSecuritySnapshot;
use App\Models\Computer;
use App\Models\ComputerSecuritySnapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'machine_guid' => 'required|string|max:100',
            'hostname'     => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            // 1) Ana bilgisayar kaydı — machine_guid ile eşleştir
            $osData     = $request->input('os', []);
            $domainData = $request->input('domain', []);

            $computer = AgentComputer::updateOrCreate(
                ['machine_guid' => $request->input('machine_guid')],
                [
                    'hostname'             => $request->input('hostname'),
                    'agent_version'        => $request->input('agent_version'),
                    'reported_at'          => $request->input('reported_at'),
                    'last_boot_time'       => $request->input('last_boot_time'),
                    'uptime_seconds'       => $request->input('uptime_seconds'),
                    'uptime_display'       => $request->input('uptime_display'),
                    'current_users'        => $request->input('current_users', []),
                    'os_product_name'      => $osData['product_name'] ?? null,
                    'os_version'           => $osData['version'] ?? null,
                    'os_release'           => $osData['release'] ?? null,
                    'os_build_number'      => $osData['build_number'] ?? null,
                    'os_architecture'      => $osData['architecture'] ?? null,
                    'os_install_date'      => $osData['install_date'] ?? null,
                    'os_registered_owner'  => $osData['registered_owner'] ?? null,
                    'os_serial_number'     => $osData['serial_number'] ?? null,
                    'is_domain_joined'     => (bool) ($domainData['is_domain_joined'] ?? false),
                    'domain_name'          => $domainData['domain_name'] ?? null,
                    'workgroup_name'       => $domainData['workgroup_name'] ?? null,
                    'domain_controller'    => $domainData['domain_controller'] ?? null,
                    'last_seen_at'         => now(),
                ]
            );

            // 2) Donanım — updateOrCreate
            if ($hw = $request->input('hardware')) {
                AgentComputerHardware::updateOrCreate(
                    ['agent_computer_id' => $computer->id],
                    [
                        'cpu_name'            => $hw['cpu_name'] ?? null,
                        'cpu_cores_physical'  => $hw['cpu_cores_physical'] ?? null,
                        'cpu_cores_logical'   => $hw['cpu_cores_logical'] ?? null,
                        'cpu_speed_mhz'       => $hw['cpu_speed_mhz'] ?? null,
                        'cpu_usage_percent'   => $hw['cpu_usage_percent'] ?? null,
                        'total_ram_gb'        => $hw['total_ram_gb'] ?? null,
                        'available_ram_gb'    => $hw['available_ram_gb'] ?? null,
                        'ram_usage_percent'   => $hw['ram_usage_percent'] ?? null,
                        'ram_slots'           => $hw['ram_slots'] ?? null,
                        'motherboard'         => $hw['motherboard'] ?? null,
                        'bios_version'        => $hw['bios_version'] ?? null,
                        'bios_date'           => $hw['bios_date'] ?? null,
                    ]
                );
            }

            // 3) Güvenlik — updateOrCreate
            if ($sec = $request->input('security')) {
                AgentComputerSecurity::updateOrCreate(
                    ['agent_computer_id' => $computer->id],
                    [
                        'rdp_enabled'         => $sec['rdp_enabled'] ?? null,
                        'uac_enabled'         => $sec['uac_enabled'] ?? null,
                        'firewall_domain'     => $sec['firewall_domain'] ?? null,
                        'firewall_private'    => $sec['firewall_private'] ?? null,
                        'firewall_public'     => $sec['firewall_public'] ?? null,
                        'auto_update'         => $sec['auto_update'] ?? null,
                        'last_windows_update' => $sec['last_windows_update'] ?? null,
                        'bitlocker'           => $sec['bitlocker'] ?? null,
                    ]
                );
            }

            // 4) Ağ adaptörleri — sil + yeniden ekle
            AgentComputerNetworkAdapter::where('agent_computer_id', $computer->id)->delete();
            if ($adapters = $request->input('network_adapters', [])) {
                $rows = array_map(function ($a) use ($computer) {
                    return [
                        'agent_computer_id' => $computer->id,
                        'adapter_name'      => $a['adapter_name'] ?? '',
                        'mac_address'       => $a['mac_address'] ?? null,
                        'ip_address'        => $a['ip_address'] ?? null,
                        'ip_address_v6'     => $a['ip_address_v6'] ?? null,
                        'subnet_mask'       => $a['subnet_mask'] ?? null,
                        'gateway'           => $a['gateway'] ?? null,
                        'dns_servers'       => isset($a['dns_servers']) ? json_encode($a['dns_servers']) : null,
                        'dhcp_enabled'      => $a['dhcp_enabled'] ?? null,
                        'dhcp_server'       => $a['dhcp_server'] ?? null,
                        'is_active'         => $a['is_active'] ?? true,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }, $adapters);
                AgentComputerNetworkAdapter::insert($rows);
            }

            // 5) Diskler — sil + yeniden ekle
            AgentComputerDisk::where('agent_computer_id', $computer->id)->delete();
            if ($disks = $request->input('disks', [])) {
                $rows = array_map(fn($d) => array_merge($d, [
                    'agent_computer_id' => $computer->id,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]), $disks);
                AgentComputerDisk::insert($rows);
            }

            // 6) Antivirüs — sil + yeniden ekle
            AgentComputerAntivirus::where('agent_computer_id', $computer->id)->delete();
            if ($avList = $request->input('antivirus', [])) {
                $rows = array_map(fn($av) => array_merge($av, [
                    'agent_computer_id' => $computer->id,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]), $avList);
                AgentComputerAntivirus::insert($rows);
            }

            // 7) Kurulu programlar — sil + chunk insert (100'er)
            AgentComputerInstalledProgram::where('agent_computer_id', $computer->id)->delete();
            if ($programs = $request->input('installed_programs', [])) {
                $now    = now();
                $chunks = array_chunk($programs, 100);
                foreach ($chunks as $chunk) {
                    $rows = array_map(fn($p) => [
                        'agent_computer_id' => $computer->id,
                        'name'              => $p['name'] ?? '',
                        'version'           => $p['version'] ?? null,
                        'publisher'         => $p['publisher'] ?? null,
                        'install_date'      => $p['install_date'] ?? null,
                        'install_location'  => $p['install_location'] ?? null,
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ], $chunk);
                    DB::table('agent_computer_installed_programs')->insert($rows);
                }
            }

            // 8) Mail bilgileri
            if ($mail = $request->input('mail')) {
                // Yeni Outlook tespiti: agent açıkça gönderiyorsa kullan,
                // yoksa default_mail_progid veya outlook_version'dan çıkar.
                // Yeni Outlook progid örneği: Microsoft.OutlookForWindows_8wekyb3d8bbwe!...
                $progid  = strtolower($mail['default_mail_progid'] ?? '');
                $version = strtolower($mail['outlook_version'] ?? '');
                if (isset($mail['is_new_outlook'])) {
                    $isNewOutlook = (bool) $mail['is_new_outlook'];
                } else {
                    $isNewOutlook = str_contains($progid, 'outlookforwindows')
                        || str_contains($version, 'new outlook')
                        || str_contains($version, 'yeni outlook')
                        || str_contains($version, 'microsoft outlook for windows');
                }

                AgentComputerMail::updateOrCreate(
                    ['agent_computer_id' => $computer->id],
                    [
                        'default_mail_client' => $mail['default_mail_client'] ?? null,
                        'default_mail_progid' => $mail['default_mail_progid'] ?? null,
                        'is_new_outlook'      => $isNewOutlook,
                        'outlook_version'     => $mail['outlook_version'] ?? null,
                    ]
                );

                AgentComputerMailAccount::where('agent_computer_id', $computer->id)->delete();
                if (!empty($mail['outlook_accounts'])) {
                    $now  = now();
                    $rows = array_map(fn($a) => [
                        'agent_computer_id' => $computer->id,
                        'smtp_address'      => $a['smtp_address'] ?? null,
                        'display_name'      => $a['display_name'] ?? null,
                        'account_type'      => $a['account_type'] ?? null,
                        'exchange_server'   => $a['exchange_server'] ?? null,
                        'source'            => $a['source'] ?? null,
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ], $mail['outlook_accounts']);
                    AgentComputerMailAccount::insert($rows);
                }
            }

            // 9) Güvenlik tehditleri (security_threats)
            if ($threats = $request->input('security_threats')) {
                $shadowData = $threats['shadow_copies'] ?? [];
                $failedData = $threats['failed_logins_1h'] ?? [];

                $snapshotData = [
                    'alert_count'          => $threats['alert_count'] ?? 0,
                    'defender_threats'     => $threats['defender_threats'] ?? [],
                    'shadow_copy_count'    => $shadowData['count'] ?? null,
                    'failed_logins_1h'     => is_array($failedData) ? ($failedData['count'] ?? null) : null,
                    'account_lockouts_1h'  => $threats['account_lockouts_1h'] ?? [],
                    'suspicious_processes' => $threats['suspicious_processes'] ?? [],
                    'open_ports'           => $threats['open_ports'] ?? [],
                    'usb_history'          => $threats['usb_history'] ?? [],
                    'smb_signing'          => $threats['smb_signing'] ?? null,
                    'local_admins'         => $threats['local_admins'] ?? [],
                    'windows_update'       => $threats['windows_update'] ?? null,
                    'new_services_24h'     => $threats['new_services_24h'] ?? [],
                    'account_changes_24h'  => $threats['account_changes_24h'] ?? [],
                    'unexpected_shutdowns' => $threats['unexpected_shutdowns'] ?? [],
                    'rdp_events_24h'       => $threats['rdp_events_24h'] ?? null,
                    'scheduled_tasks_24h'  => $threats['scheduled_tasks_24h'] ?? [],
                    'admin_logins_1h'      => $threats['admin_logins_1h'] ?? [],
                    'service_crashes_24h'  => $threats['service_crashes_24h'] ?? [],
                    'reported_at'          => now(),
                ];

                AgentComputerSecuritySnapshot::updateOrCreate(
                    ['agent_computer_id' => $computer->id],
                    $snapshotData
                );

                // 10) IP eşleşmesiyle manuel Computer kaydına da snapshot kaydet
                $primaryIp = DB::table('agent_computer_network_adapters')
                    ->where('agent_computer_id', $computer->id)
                    ->where('is_active', true)
                    ->whereNotNull('ip_address')
                    ->value('ip_address');

                if ($primaryIp) {
                    $manualComputer = Computer::where('ip_address', $primaryIp)->first();
                    if ($manualComputer) {
                        ComputerSecuritySnapshot::create(array_merge($snapshotData, [
                            'computer_id'       => $manualComputer->id,
                            'agent_computer_id' => $computer->id,
                        ]));
                    }
                }
            }

            return $computer;
        });

        $computer = AgentComputer::where('machine_guid', $request->input('machine_guid'))->first();

        return response()->json([
            'message'     => 'OK',
            'computer_id' => $computer?->id,
        ], 200);
    }
}
