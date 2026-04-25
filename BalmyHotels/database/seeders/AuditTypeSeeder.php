<?php

namespace Database\Seeders;

use App\Models\AuditType;
use Illuminate\Database\Seeder;

class AuditTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Form Kontrolü',                'description' => 'Formların eksiksiz ve doğru şekilde doldurulup doldurulmadığının kontrolü.',                              'sort_order' => 1],
            ['name' => 'Dolap Kontrolü',               'description' => 'Personel dolaplarının düzen, temizlik ve kurallara uygunluğunun denetimi.',                              'sort_order' => 2],
            ['name' => 'Atık Ayrışım Kontrolü',        'description' => 'Atıkların renk koduna uygun biçimde ayrıştırılıp ayrıştırılmadığının denetimi.',                        'sort_order' => 3],
            ['name' => 'Kesme Tahtası Bıçak Renk Kodu','description' => 'Mutfakta kesme tahtası ve bıçakların renk kodlarına uygunluğunun kontrolü.',                           'sort_order' => 4],
            ['name' => 'Şahit Numune Kontrolü',        'description' => 'Şahit numunelerin doğru alınıp alınmadığı ve muhafaza şartlarının denetimi.',                           'sort_order' => 5],
            ['name' => 'Temizlik Kontrolü',             'description' => 'Alanların genel temizlik standartlarına uygunluğunun denetimi.',                                        'sort_order' => 6],
            ['name' => 'Büfe Denetimi',                'description' => 'Büfede sunulan ürünlerin kalite, çeşitlilik ve süreç standartlarının denetimi.',                        'sort_order' => 7],
            ['name' => 'Ham Madde Depolama Kontrolü',  'description' => 'Ham maddelerin uygun koşullarda depolanıp depolanmadığının ve etiketlemenin kontrolü.',                  'sort_order' => 8],
        ];

        foreach ($types as $type) {
            AuditType::firstOrCreate(
                ['name' => $type['name']],
                array_merge($type, ['is_active' => true])
            );
        }
    }
}
