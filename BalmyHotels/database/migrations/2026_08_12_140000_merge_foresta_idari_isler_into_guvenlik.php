<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $branchId = (int) config('services.elektra_pdks.foresta.branch_id', 2);

        DB::transaction(function () use ($branchId) {
            $duplicate = DB::table('departments')
                ->where('branch_id', $branchId)
                ->where('name', 'İDARİ İŞLER')
                ->first();
            $canonical = DB::table('departments')
                ->where('branch_id', $branchId)
                ->where('name', 'Güvenlik')
                ->first();

            if (!$duplicate || !$canonical || $duplicate->id === $canonical->id) {
                return;
            }

            DB::table('users')
                ->where('department_id', $duplicate->id)
                ->update(['department_id' => $canonical->id]);
            DB::table('pdks_employees')
                ->where('department_id', $duplicate->id)
                ->update(['department_id' => $canonical->id]);

            DB::table('departments')->where('id', $duplicate->id)->delete();
        });
    }

    public function down(): void
    {
        // Birleştirilen personeli eski departmana güvenle ayırmak mümkün değildir.
    }
};
