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
        ];
        $this->savePerms('dept_manager', $deptManagerPerms);

        // 4) staff: sadece okuma + arıza bildirimi
        $staffPerms = [
            'faults'      => ['index'=>1,'show'=>1,'create'=>1,'edit'=>0,'delete'=>0],
            'guest_logs'  => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'door_logs'   => ['index'=>1,'show'=>0,'create'=>0,'edit'=>0,'delete'=>0],
            'food_labels' => ['index'=>1,'show'=>1,'create'=>0,'edit'=>0,'delete'=>0],
            'my_tasks'    => ['index'=>1,'show'=>1,'create'=>1,'edit'=>1,'delete'=>1],
        ];
        $this->savePerms('staff', $staffPerms);

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
