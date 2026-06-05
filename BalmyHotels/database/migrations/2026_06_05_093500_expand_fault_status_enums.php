<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $statuses = [
        'open',
        'in_progress',
        'winter_plan',
        'waiting_material',
        'resolved',
        'closed',
    ];

    public function up(): void
    {
        if (Schema::hasTable('faults') && Schema::hasColumn('faults', 'status')) {
            DB::statement($this->modifyEnumSql('faults', 'status', true));
        }

        if (Schema::hasTable('fault_updates')) {
            if (Schema::hasColumn('fault_updates', 'status_from')) {
                DB::statement($this->modifyEnumSql('fault_updates', 'status_from'));
            }

            if (Schema::hasColumn('fault_updates', 'status_to')) {
                DB::statement($this->modifyEnumSql('fault_updates', 'status_to'));
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('faults') && Schema::hasColumn('faults', 'status')) {
            DB::table('faults')
                ->whereIn('status', ['winter_plan', 'waiting_material'])
                ->update(['status' => 'in_progress']);

            DB::statement($this->modifyEnumSql('faults', 'status', true, [
                'open',
                'in_progress',
                'resolved',
                'closed',
            ]));
        }

        if (Schema::hasTable('fault_updates')) {
            $legacyStatuses = ['open', 'in_progress', 'resolved', 'closed'];

            if (Schema::hasColumn('fault_updates', 'status_from')) {
                DB::table('fault_updates')
                    ->whereIn('status_from', ['winter_plan', 'waiting_material'])
                    ->update(['status_from' => 'in_progress']);

                DB::statement($this->modifyEnumSql('fault_updates', 'status_from', false, $legacyStatuses));
            }

            if (Schema::hasColumn('fault_updates', 'status_to')) {
                DB::table('fault_updates')
                    ->whereIn('status_to', ['winter_plan', 'waiting_material'])
                    ->update(['status_to' => 'in_progress']);

                DB::statement($this->modifyEnumSql('fault_updates', 'status_to', false, $legacyStatuses));
            }
        }
    }

    private function modifyEnumSql(string $table, string $column, bool $defaultOpen = false, ?array $statuses = null): string
    {
        $values = collect($statuses ?? $this->statuses)
            ->map(fn (string $status) => "'" . str_replace("'", "''", $status) . "'")
            ->implode(',');

        $nullSql = $defaultOpen ? 'NOT NULL' : 'NULL';
        $defaultSql = $defaultOpen ? " DEFAULT 'open'" : '';

        return "ALTER TABLE `{$table}` MODIFY `{$column}` ENUM({$values}) {$nullSql}{$defaultSql}";
    }
};
