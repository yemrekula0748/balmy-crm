<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = [
        'role_name', 'module',
        'can_index', 'can_show', 'can_create', 'can_edit', 'can_delete',
    ];

    protected $casts = [
        'can_index'  => 'boolean',
        'can_show'   => 'boolean',
        'can_create' => 'boolean',
        'can_edit'   => 'boolean',
        'can_delete' => 'boolean',
    ];

    // ----------------------------------------------------------------
    // Projede tanımlı tüm modüller
    // Grup adı => [ 'module_key' => 'Görünen Ad', ... ]
    // ----------------------------------------------------------------
    const MODULES = [
        'Araç Yönetimi' => [
            'vehicles'       => 'Araçlar',
            'vehicle_ops'    => 'Araç Operasyonları',
            'vehicle_maint'  => 'Araç Bakım',
            'vehicle_ins'    => 'Araç Sigorta',
        ],
        'Personel' => [
            'users'          => 'Kullanıcılar',
            'departments'    => 'Departmanlar',
        ],
        'Güvenlik & Misafir' => [
            'door_logs'        => 'Kapı Geçiş Logları',
            'door_log_reports' => 'Kapı Geçiş Raporları',
            'guest_logs'       => 'Misafir Logları',
        ],
        'Teknik Arıza' => [
            'faults'         => 'Arızalar',
            'fault_types'    => 'Arıza Tipleri',
            'fault_locations'=> 'Arıza Konumları',
            'fault_stats'    => 'Arıza İstatistikleri',
        ],
        'Demirbaş' => [
            'assets'         => 'Demirbaş Envanter',
            'asset_categories'=> 'Demirbaş Kategorileri',
            'asset_exits'    => 'Demirbaş Çıkış',
        ],
        'Menü & Anket' => [
            'qrmenus'        => 'QR Menü',
            'surveys'        => 'Misafir Anket',
            'food_labels'    => 'Yemek İsimlik',
            'staff_surveys'  => 'Personel Anket',
        ],
        'Araçlar' => [
            'contract_compare' => 'Sözleşme Karşılaştırma',
            'pdf_converter'    => 'PDF Word Çevirici',
            'pdf_merger'       => 'PDF Birleştirici',
        ],
        'Sürdürülebilirlik' => [
            'carbon_footprint' => 'Karbon Ayak İzi Raporları',
        ],
    ];

    /** Düz module_key => Ad listesi döner */
    public static function flatModules(): array
    {
        $flat = [];
        foreach (self::MODULES as $modules) {
            foreach ($modules as $key => $label) {
                $flat[$key] = $label;
            }
        }
        return $flat;
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_name', 'name');
    }
}
