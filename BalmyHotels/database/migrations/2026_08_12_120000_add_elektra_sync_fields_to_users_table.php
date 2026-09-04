<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_source', 30)->default('manual')->after('fault_notify')->index();
            $table->string('elektra_tenant_id', 50)->nullable()->after('account_source')->index();
            $table->string('elektra_company_id', 100)->nullable()->after('elektra_tenant_id')->index();
            $table->string('elektra_sicil_id', 100)->nullable()->after('elektra_company_id');
            $table->string('identity_no_hash', 64)->nullable()->after('elektra_sicil_id')->unique();
            $table->string('phone_normalized', 20)->nullable()->after('identity_no_hash')->index();
            $table->timestamp('elektra_synced_at')->nullable()->after('phone_normalized');

            $table->unique(
                ['elektra_tenant_id', 'elektra_company_id', 'elektra_sicil_id'],
                'users_elektra_identity_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_elektra_identity_unique');
            $table->dropUnique(['identity_no_hash']);
            $table->dropIndex(['account_source']);
            $table->dropIndex(['elektra_tenant_id']);
            $table->dropIndex(['elektra_company_id']);
            $table->dropIndex(['phone_normalized']);
            $table->dropColumn([
                'account_source',
                'elektra_tenant_id',
                'elektra_company_id',
                'elektra_sicil_id',
                'identity_no_hash',
                'phone_normalized',
                'elektra_synced_at',
            ]);
        });
    }
};
