<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\RolePermission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Temel rolleri oluştur
        $roles = [
            ['name' => 'super_admin',    'display_name' => 'Süper Admin',   'color' => 'danger',  'is_system' => true],
            ['name' => 'branch_manager', 'display_name' => 'Şube Müdürü',  'color' => 'primary', 'is_system' => true],
            ['name' => 'dept_manager',   'display_name' => 'Departman Müdürü', 'color' => 'info', 'is_system' => true],
            ['name' => 'staff',          'display_name' => 'Personel',      'color' => 'secondary', 'is_system' => false],
            ['name' => 'egitmen',        'display_name' => 'Egitmen',       'color' => 'warning', 'is_system' => false],
            ['name' => 'ogrenen',        'display_name' => 'Ogrenen',       'color' => 'success', 'is_system' => false],
        ];

        foreach ($roles as $data) {
            Role::updateOrCreate(['name' => $data['name']], $data);
        }

        // 2) branch_manager: çoğu modülde tam yetki, kullanıcı yönetiminde sınırlı
        $branchManagerPerms = [];
        $allModules = RolePermission::flatModules();

        foreach (array_keys($allModules) as $module) {
            $branchManagerPerms[$module] = ['index'=>1,'show'=>1,'create'=>1,'edit'=>1,'delete'=>1];
        }
        // Kullanıcı yönetiminden silme yetkisi yok
        $branchManagerPerms['users']['delete'] = 0;

        $this->savePerms('branch_manager', $branchManagerPerms);

        // dept_manager için ek modüller
        $branchManagerPerms['it_computers']['delete'] = 1;
        $branchManagerPerms['it_backup']['delete']    = 1;

        // 3) dept_manager: kendi departmanıyla ilgili modüller
        $deptManagerPerms = [
            'faults'         => ['index'=>1,'show'=>1,'create'=>1,'edit'=>1,'delete'=>0],
            'fault_types'    => ['index'=>1,'show'=>0,'create'=>0,'edit'=>0,'delete'=>0],
            'fault_locations'=> ['index'=>1,'show'=>0,'create'=>0,'edit'=>0,'delete'=>0],
            'fault_room_reports' => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'fault_type_reports' => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'animation_events' => ['index'=>1,'show'=>1,'create'=>1,'edit'=>0,'delete'=>1],
            'event_tracking' => ['index'=>1,'show'=>1,'create'=>1,'edit'=>0,'delete'=>0],
            'event_show_reports' => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'guest_logs'     => ['index'=>1,'show'=>1,'create'=>1,'edit'=>1,'delete'=>0],
            'door_logs'      => ['index'=>1,'show'=>0,'create'=>1,'edit'=>0,'delete'=>0],
            'assets'         => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'asset_exits'    => ['index'=>1,'show'=>1,'create'=>1,'edit'=>0,'delete'=>0],
            'surveys'        => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'staff_surveys'  => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'food_labels'    => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'vehicles'       => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'it_computers'   => ['index'=>1,'show'=>0,'create'=>0,'edit'=>0,'delete'=>0],
            'my_tasks'       => ['index'=>1,'show'=>1,'create'=>1,'edit'=>1,'delete'=>1],
            // Önbüro & Acenteler
            'agencies'         => ['index'=>1,'show'=>1,'create'=>1,'edit'=>1,'delete'=>0],
            'agency_contracts' => ['index'=>1,'show'=>1,'create'=>1,'edit'=>1,'delete'=>0],
            'bed_types'        => ['index'=>1,'show'=>0,'create'=>0,'edit'=>0,'delete'=>0],
            'room_types'       => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'rooms'            => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'guest_control'    => ['index'=>1,'show'=>1,'create'=>1,'edit'=>0,'delete'=>0],
            'guest_control_history' => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'reservations'     => ['index'=>1,'show'=>1,'create'=>1,'edit'=>1,'delete'=>0],
        ];
        $this->savePerms('dept_manager', $deptManagerPerms);

        // 4) staff: sadece okuma + arıza bildirimi
        $staffPerms = [
            'faults'      => ['index'=>1,'show'=>1,'create'=>1,'edit'=>0,'delete'=>0],
            'guest_logs'  => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'door_logs'   => ['index'=>1,'show'=>0,'create'=>0,'edit'=>0,'delete'=>0],
            'food_labels' => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'my_tasks'    => ['index'=>1,'show'=>1,'create'=>1,'edit'=>1,'delete'=>1],
            // Önbüro
            'rooms'        => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'guest_control' => ['index'=>1,'show'=>1,'create'=>1,'edit'=>0,'delete'=>0],
            'guest_control_history' => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'reservations' => ['index'=>1,'show'=>1,'create'=>1,'edit'=>0,'delete'=>0],
        ];
        $this->savePerms('staff', $staffPerms);

        // 5) egitmen: egitim icerigi, atama, yuz yuze egitim ve rapor yonetimi
        $trainerPerms = [
            'education_courses'     => ['index'=>1,'show'=>1,'create'=>1,'edit'=>1,'delete'=>1],
            'education_assignments' => ['index'=>1,'show'=>0,'create'=>1,'edit'=>0,'delete'=>1],
            'education_learning'    => ['index'=>1,'show'=>1,'create'=>0,'edit'=>1,'delete'=>0],
            'education_events'      => ['index'=>1,'show'=>1,'create'=>1,'edit'=>1,'delete'=>1],
            'education_reports'     => ['index'=>1,'show'=>0,'create'=>0,'edit'=>0,'delete'=>0],
        ];
        $this->savePerms('egitmen', $trainerPerms);

        // 6) ogrenen: kendi egitimleri ve yuz yuze katilim cevabi
        $learnerPerms = [
            'education_learning' => ['index'=>1,'show'=>1,'create'=>0,'edit'=>1,'delete'=>0],
            'education_events'   => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
        ];
        $this->savePerms('ogrenen', $learnerPerms);

        $this->command->info('Roller ve varsayılan izinler oluşturuldu.');
    }

    private function savePerms(string $roleName, array $perms): void
    {
        $actions = ['index','show','create','edit','delete'];
        $allModules = RolePermission::flatModules();

        // Tüm modüller için kayıt oluştur (verilen perms'de yoksa tümü false)
        foreach (array_keys($allModules) as $module) {
            $data = [];
            foreach ($actions as $action) {
                $data["can_{$action}"] = (bool) ($perms[$module][$action] ?? 0);
            }
            RolePermission::updateOrCreate(
                ['role_name' => $roleName, 'module' => $module],
                $data
            );
        }
    }
}
