<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $branchId = (int) config('services.elektra_pdks.foresta.branch_id', 2);
        $aliases = [
            'ÖNBÜRO' => 'Ön Büro',
            'KONSEPT & ANİMASYON' => 'Animasyon',
            'KAT HİZMETLERİ' => 'HK',
            'SATIŞ & PAZARLAMA' => 'Satış ve Pazarlama',
        ];

        DB::transaction(function () use ($branchId, $aliases) {
            foreach ($aliases as $duplicateName => $canonicalName) {
                $duplicate = DB::table('departments')
                    ->where('branch_id', $branchId)
                    ->where('name', $duplicateName)
                    ->first();
                $canonical = DB::table('departments')
                    ->where('branch_id', $branchId)
                    ->where('name', $canonicalName)
                    ->first();

                if (!$duplicate || !$canonical || $duplicate->id === $canonical->id) {
                    continue;
                }

                DB::table('users')
                    ->where('department_id', $duplicate->id)
                    ->update(['department_id' => $canonical->id]);
                DB::table('pdks_employees')
                    ->where('department_id', $duplicate->id)
                    ->update(['department_id' => $canonical->id]);

                DB::table('departments')->where('id', $duplicate->id)->delete();
            }
        });
    }

    public function down(): void
    {
        // Birleştirilen personeli eski mükerrer kayda güvenle ayırmak mümkün değildir.
    }
};
