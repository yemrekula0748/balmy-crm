<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdks_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 30)->default('manual')->index();
            $table->string('tenant_id', 50)->nullable()->index();
            $table->string('company_id', 100)->nullable()->index();
            $table->string('external_employee_id', 100)->nullable()->index();
            $table->string('registry_no', 100)->nullable()->index();
            $table->string('pdks_card_no', 100)->nullable()->index();
            $table->string('identity_no', 30)->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('title')->nullable();
            $table->string('employment_type', 50)->nullable();
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->json('raw_payload')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['source', 'tenant_id', 'company_id', 'external_employee_id'], 'pdks_employee_source_unique');
        });

        Schema::create('pdks_device_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->boolean('require_gps')->default(true);
            $table->boolean('require_wifi')->default(false);
            $table->decimal('allowed_latitude', 10, 7)->nullable();
            $table->decimal('allowed_longitude', 10, 7)->nullable();
            $table->unsignedInteger('allowed_radius_meters')->default(200);
            $table->unsignedInteger('max_accuracy_meters')->nullable();
            $table->json('allowed_wifi_ssids')->nullable();
            $table->json('allowed_wifi_bssids')->nullable();
            $table->boolean('block_mock_location')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('pdks_break_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->unsignedInteger('max_minutes')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->string('color', 30)->default('#c19b77');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('pdks_leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->decimal('default_days', 6, 2)->default(0);
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_approval')->default(true);
            $table->string('color', 30)->default('#c19b77');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('pdks_shift_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code', 50)->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('planned_minutes')->nullable();
            $table->unsignedInteger('break_minutes')->default(0);
            $table->unsignedInteger('late_grace_minutes')->default(5);
            $table->unsignedInteger('early_leave_grace_minutes')->default(5);
            $table->boolean('crosses_midnight')->default(false);
            $table->string('color', 30)->default('#c19b77');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('pdks_shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('pdks_employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shift_type_id')->nullable()->constrained('pdks_shift_types')->nullOnDelete();
            $table->date('shift_date')->index();
            $table->string('status', 30)->default('assigned')->index();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'shift_date'], 'pdks_shift_employee_date_unique');
        });

        Schema::create('pdks_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('pdks_employees')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shift_assignment_id')->nullable()->constrained('pdks_shift_assignments')->nullOnDelete();
            $table->date('work_date')->index();
            $table->dateTime('check_in_at')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->decimal('check_in_latitude', 10, 7)->nullable();
            $table->decimal('check_in_longitude', 10, 7)->nullable();
            $table->unsignedInteger('check_in_accuracy')->nullable();
            $table->decimal('check_out_latitude', 10, 7)->nullable();
            $table->decimal('check_out_longitude', 10, 7)->nullable();
            $table->unsignedInteger('check_out_accuracy')->nullable();
            $table->string('check_in_wifi_ssid')->nullable();
            $table->string('check_in_wifi_bssid')->nullable();
            $table->string('check_out_wifi_ssid')->nullable();
            $table->string('check_out_wifi_bssid')->nullable();
            $table->boolean('check_in_mock_detected')->default(false);
            $table->boolean('check_out_mock_detected')->default(false);
            $table->string('verification_status', 30)->default('pending')->index();
            $table->json('verification_issues')->nullable();
            $table->unsignedInteger('worked_minutes')->default(0);
            $table->unsignedInteger('break_minutes')->default(0);
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leave_minutes')->default(0);
            $table->string('status', 30)->default('open')->index();
            $table->string('source', 30)->default('web');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'work_date'], 'pdks_attendance_employee_date_unique');
        });

        Schema::create('pdks_break_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_record_id')->constrained('pdks_attendance_records')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('pdks_employees')->cascadeOnDelete();
            $table->foreignId('break_type_id')->constrained('pdks_break_types')->restrictOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->decimal('start_latitude', 10, 7)->nullable();
            $table->decimal('start_longitude', 10, 7)->nullable();
            $table->decimal('end_latitude', 10, 7)->nullable();
            $table->decimal('end_longitude', 10, 7)->nullable();
            $table->boolean('mock_detected')->default(false);
            $table->string('status', 30)->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pdks_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('pdks_employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('leave_type_id')->constrained('pdks_leave_types')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_days', 6, 2)->default(1);
            $table->string('status', 30)->default('pending')->index();
            $table->text('reason')->nullable();
            $table->text('manager_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pdks_shift_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('pdks_employees')->cascadeOnDelete();
            $table->foreignId('shift_assignment_id')->nullable()->constrained('pdks_shift_assignments')->nullOnDelete();
            $table->foreignId('requested_shift_type_id')->nullable()->constrained('pdks_shift_types')->nullOnDelete();
            $table->date('requested_shift_date')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->text('reason')->nullable();
            $table->text('manager_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pdks_overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('pdks_employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->date('overtime_date')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->string('status', 30)->default('pending')->index();
            $table->text('reason')->nullable();
            $table->text('manager_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pdks_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('pdks_employees')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type', 50)->default('info')->index();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('pdks_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 50)->default('elektra')->index();
            $table->string('status', 30)->default('started')->index();
            $table->json('parameters')->nullable();
            $table->unsignedInteger('total_records')->default(0);
            $table->unsignedInteger('created_records')->default(0);
            $table->unsignedInteger('updated_records')->default(0);
            $table->unsignedInteger('failed_records')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        $this->seedDefaults();
    }

    public function down(): void
    {
        Schema::dropIfExists('pdks_sync_logs');
        Schema::dropIfExists('pdks_notifications');
        Schema::dropIfExists('pdks_overtime_requests');
        Schema::dropIfExists('pdks_shift_change_requests');
        Schema::dropIfExists('pdks_leave_requests');
        Schema::dropIfExists('pdks_break_records');
        Schema::dropIfExists('pdks_attendance_records');
        Schema::dropIfExists('pdks_shift_assignments');
        Schema::dropIfExists('pdks_shift_types');
        Schema::dropIfExists('pdks_leave_types');
        Schema::dropIfExists('pdks_break_types');
        Schema::dropIfExists('pdks_device_policies');
        Schema::dropIfExists('pdks_employees');
    }

    private function seedDefaults(): void
    {
        DB::table('pdks_break_types')->insert([
            ['name' => 'Yemek Molasi', 'code' => 'meal', 'max_minutes' => 60, 'is_paid' => false, 'color' => '#c19b77', 'sort_order' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sigara Molasi', 'code' => 'smoke', 'max_minutes' => 15, 'is_paid' => false, 'color' => '#d97706', 'sort_order' => 20, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dinlenme Molasi', 'code' => 'rest', 'max_minutes' => 20, 'is_paid' => true, 'color' => '#64748b', 'sort_order' => 30, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('pdks_leave_types')->insert([
            ['name' => 'Yillik Izin', 'code' => 'annual', 'default_days' => 14, 'is_paid' => true, 'requires_approval' => true, 'color' => '#c19b77', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mazeret Izni', 'code' => 'excuse', 'default_days' => 0, 'is_paid' => true, 'requires_approval' => true, 'color' => '#64748b', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ucretsiz Izin', 'code' => 'unpaid', 'default_days' => 0, 'is_paid' => false, 'requires_approval' => true, 'color' => '#ef4444', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rapor / Saglik', 'code' => 'medical', 'default_days' => 0, 'is_paid' => true, 'requires_approval' => true, 'color' => '#0ea5e9', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('pdks_shift_types')->insert([
            ['branch_id' => null, 'name' => 'A Vardiyasi', 'code' => 'A', 'start_time' => '08:00:00', 'end_time' => '16:00:00', 'planned_minutes' => 480, 'break_minutes' => 60, 'late_grace_minutes' => 5, 'early_leave_grace_minutes' => 5, 'crosses_midnight' => false, 'color' => '#c19b77', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['branch_id' => null, 'name' => 'B Vardiyasi', 'code' => 'B', 'start_time' => '16:00:00', 'end_time' => '00:00:00', 'planned_minutes' => 480, 'break_minutes' => 60, 'late_grace_minutes' => 5, 'early_leave_grace_minutes' => 5, 'crosses_midnight' => true, 'color' => '#475569', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['branch_id' => null, 'name' => 'Gece Vardiyasi', 'code' => 'N', 'start_time' => '00:00:00', 'end_time' => '08:00:00', 'planned_minutes' => 480, 'break_minutes' => 60, 'late_grace_minutes' => 5, 'early_leave_grace_minutes' => 5, 'crosses_midnight' => false, 'color' => '#1f2937', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
};
