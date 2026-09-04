<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $branchId = (int) config('services.elektra_pdks.foresta.branch_id', 2);

        DB::transaction(function () use ($branchId) {
            $canonical = DB::table('departments')
                ->where('branch_id', $branchId)
                ->where('name', 'F&B')
                ->first();

            if (!$canonical) {
                return;
            }

            $duplicates = DB::table('departments')
                ->where('branch_id', $branchId)
                ->whereIn('name', ['F&B BAR', 'F&B RESTAURANT'])
                ->get();

            foreach ($duplicates as $duplicate) {
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
        // Birleştirilen personeli eski departmanlara güvenle ayırmak mümkün değildir.
    }
};
