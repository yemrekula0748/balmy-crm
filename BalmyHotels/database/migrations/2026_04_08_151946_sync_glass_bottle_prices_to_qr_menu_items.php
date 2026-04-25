<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // qr_menu_items içinde food_product_id bağlı olan ama bardak/şişe fiyatı
        // NULL olan tüm satırları kaynak food_product'dan güncelle.
        DB::statement("
            UPDATE qr_menu_items qi
            JOIN food_products fp ON fp.id = qi.food_product_id
            SET
                qi.price_glass  = COALESCE(qi.price_glass,  fp.price_glass),
                qi.price_bottle = COALESCE(qi.price_bottle, fp.price_bottle),
                qi.cl_glass     = COALESCE(qi.cl_glass,     fp.cl_glass),
                qi.cl_bottle    = COALESCE(qi.cl_bottle,    fp.cl_bottle)
            WHERE qi.food_product_id IS NOT NULL
              AND (
                    (qi.price_glass  IS NULL AND fp.price_glass  IS NOT NULL)
                 OR (qi.price_bottle IS NULL AND fp.price_bottle IS NOT NULL)
                 OR (qi.cl_glass     IS NULL AND fp.cl_glass     IS NOT NULL)
                 OR (qi.cl_bottle    IS NULL AND fp.cl_bottle    IS NOT NULL)
              )
        ");
    }

    public function down(): void
    {
        // Geri alma: sadece bu migration tarafından doldurulmuş satırları NULL yapmak
        // mümkün olmadığından (önceden girilmiş değerlerden ayırt edilemez) boş bırakılıyor.
    }
};
