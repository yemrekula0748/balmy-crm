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
            'vehicles'             => 'Araçlar',
            'vehicle_ops'          => 'Araç Operasyonları',
            'vehicle_maint'        => 'Araç Bakım',
            'vehicle_ins'          => 'Araç Sigorta',
            'vehicle_trips'        => 'Araç Görev Başlat/Bitir',
            'vehicle_trip_control' => 'Araç Görev Kontrol (Harita)',
        ],
        'Personel' => [
            'users'          => 'Kullanıcılar',
            'departments'    => 'Departmanlar',
        ],
        'Güvenlik & Misafir' => [
            'door_logs'        => 'Kapı Geçiş Logları',
            'door_log_reports' => 'Kapı Geçiş Raporları',
            'hr_reports'       => 'İ.K Raporları (Tüm Şubeler)',
            'guest_logs'       => 'Misafir Logları',
        ],
        'Teknik Arıza' => [
            'faults'         => 'Arızalar',
            'fault_types'    => 'Arıza Tipleri',
            'fault_locations'=> 'Arıza Konumları',
            'fault_stats'    => 'Arıza İstatistikleri',
            'fault_room_reports' => 'Oda Bazlı Arıza Raporu',
            'fault_type_reports' => 'Arıza Bazlı Rapor',
        ],
        'Demirbaş' => [
            'assets'         => 'Demirbaş Envanter',
            'asset_categories'=> 'Demirbaş Kategorileri',
            'asset_exits'    => 'Demirbaş Çıkış',
        ],
        'Menü & Anket' => [
            'qrmenus'        => 'QR Menü',
            'food_library'   => 'Yemek Kütüphanesi',
            'printers'       => 'Yazıcılar',
            'surveys'        => 'Misafir Anket',
            'food_labels'    => 'Yemek İsimlik',
            'staff_surveys'  => 'Personel Anket',
        ],
        'Araçlar' => [
            'contract_compare' => 'Sözleşme Karşılaştırma',
            'pdf_converter'    => 'PDF Word Çevirici',
            'pdf_merger'       => 'PDF Birleştirici',
            'ocr'              => 'Yazıya Çevir (OCR)',
        ],
        'Sürdürülebilirlik' => [
            'carbon_footprint' => 'Karbon Ayak İzi Raporları',
        ],
        'Raporlar' => [
            'tripadvisor_report' => 'TripAdvisor Puanları',
            'google_report'      => 'Google Puanları',
        ],
        'Servis Takip' => [
            'shuttle_routes'     => 'Güzergah Tanımları',
            'shuttle_vehicles'   => 'Servis Araçları',
            'shuttle_operations' => 'Servis Operasyonu',
            'shuttle_reports'    => 'Servis Raporları',
        ],
        'Egitim ve Gelisim' => [
            'education_courses'     => 'Egitim Icerikleri',
            'education_assignments' => 'Egitim Atamalari',
            'education_learning'    => 'Egitimlerim',
            'education_events'      => 'Yuz Yuze Egitimler',
            'education_reports'     => 'Egitim Raporlari',
        ],
        'Sipariş Modülü' => [
            'restaurant_settings'  => 'Restoran & Masa Tanımları',
            'orders'               => 'Sipariş Al',
            'order_reports'        => 'Sipariş Raporları',
            'order_analytics'      => 'Sipariş Analizi',
            'order_ai_analysis'    => 'Stratejik AI Analizi',
            'order_guest_analysis' => 'Misafir Tüketim Analizi',
        ],
        'İç Denetim' => [
            'audit_types'            => 'Denetim Tipleri Yönetimi',
            'audits'                 => 'Denetim Oluştur / Listele',
            'audit_nonconformities'  => 'Uygunsuzluklarım',
            'audit_analytics'        => 'Denetim Analiz & İstatistik',
        ],
        'Bilgi İşlem' => [
            'it_computers'       => 'Bilgisayar Envanteri (Manuel)',
            'it_agent_inventory' => 'Ajan Envanter (Windows Agent)',
            'it_backup'          => 'Veritabanı Yedekleme',
            'login_logs'         => 'Giriş Logları',
            'mikrotik'           => 'MikroTik Dashboard',
        ],
        'İşlerim' => [
            'my_tasks' => 'Görevlerim',
        ],
        'Acenteler' => [
            'agencies'         => 'Acenteler',
            'agency_contracts' => 'Acente Kontratları',
        ],
        'Önbüro' => [
            'bed_types'    => 'Yatak Tipleri',
            'room_types'   => 'Oda Tipleri',
            'rooms'        => 'Odalar',
            'guest_control' => 'Misafir Kontrol',
            'guest_control_history' => 'Misafir Kontrol Kayitlari',
            'reservations' => 'Rezervasyonlar',
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
