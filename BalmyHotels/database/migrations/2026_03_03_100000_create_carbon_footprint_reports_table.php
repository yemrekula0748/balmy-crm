<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carbon_footprint_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->enum('report_type', ['monthly', 'quarterly', 'annual'])->default('monthly');
            $table->date('period_start');
            $table->date('period_end');

            // Operasyonel veriler
            $table->integer('total_guests')->default(0)->comment('Toplam misafir sayısı');
            $table->integer('occupied_rooms')->default(0)->comment('Dolu oda-gece sayısı');
            $table->integer('total_rooms')->default(0)->comment('Toplam oda kapasitesi');
            $table->integer('staff_count')->default(0)->comment('Toplam personel sayısı');
            $table->decimal('total_area_sqm', 10, 2)->default(0)->comment('Toplam alan (m²)');

            // GHG Protocol Scope bazlı sonuçlar (kgCO2e)
            $table->decimal('total_co2_scope1', 14, 3)->default(0)->comment('Scope 1 - Doğrudan emisyonlar (kgCO2e)');
            $table->decimal('total_co2_scope2', 14, 3)->default(0)->comment('Scope 2 - Dolaylı enerji emisyonları (kgCO2e)');
            $table->decimal('total_co2_scope3', 14, 3)->default(0)->comment('Scope 3 - Diğer dolaylı emisyonlar (kgCO2e)');
            $table->decimal('total_co2_total', 14, 3)->default(0)->comment('Toplam emisyon (kgCO2e)');

            // Yoğunluk metrikleri (HCMI standardı)
            $table->decimal('co2_per_guest', 10, 4)->default(0)->comment('Misafir başına emisyon (kgCO2e/misafir)');
            $table->decimal('co2_per_room_night', 10, 4)->default(0)->comment('Oda-gece başına emisyon (kgCO2e/oda-gece)');
            $table->decimal('co2_per_sqm', 10, 4)->default(0)->comment('m² başına emisyon (kgCO2e/m²)');
            $table->decimal('co2_per_staff', 10, 4)->default(0)->comment('Personel başına emisyon (kgCO2e/personel)');

            // HCMI Skor (0-100)
            $table->decimal('hcmi_score', 5, 2)->nullable()->comment('HCMI Karbon Skoru');
            $table->string('hcmi_rating', 10)->nullable()->comment('A+/A/B/C/D/E rating');

            // Yenilenebilir enerji & hedefler
            $table->decimal('renewable_energy_pct', 5, 2)->default(0)->comment('Yenilenebilir enerji oranı (%)');
            $table->decimal('waste_recycling_rate', 5, 2)->default(0)->comment('Atık geri dönüşüm oranı (%)');
            $table->decimal('water_intensity', 10, 4)->default(0)->comment('Su yoğunluğu (m³/oda-gece)');

            // Standartlar
            $table->json('standards_applied')->nullable()->comment('Uygulanan standartlar: ISO14064, HCMI, GHG, CSRD...');

            $table->text('methodology_notes')->nullable();
            $table->text('improvement_notes')->nullable();
            $table->enum('status', ['draft', 'final', 'verified'])->default('draft');
            $table->string('pdf_path')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
        });

        Schema::create('carbon_footprint_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('carbon_footprint_reports')->cascadeOnDelete();

            $table->tinyInteger('scope')->comment('1=Doğrudan, 2=Enerji Dolaylı, 3=Diğer Dolaylı');
            $table->string('category')->comment('energy_electricity, energy_gas, transport_vehicle, water, waste, food, refrigerant, procurement...');
            $table->string('sub_category')->nullable()->comment('Alt kategori etiketi');
            $table->string('source_description')->nullable()->comment('Kaynak açıklaması');

            $table->decimal('quantity', 14, 4)->default(0)->comment('Tüketim miktarı');
            $table->string('unit', 20)->comment('kWh, m3, L, kg, km, tonne');
            $table->decimal('emission_factor', 12, 6)->default(0)->comment('Emisyon faktörü (kgCO2e/birim)');
            $table->string('ef_source', 100)->nullable()->comment('EF kaynağı: IPCC 2023, DEFRA, IEA...');
            $table->decimal('co2_kg', 14, 3)->default(0)->comment('Hesaplanan CO2e (kg)');

            $table->boolean('is_renewable')->default(false)->comment('Yenilenebilir kaynak mı?');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carbon_footprint_entries');
        Schema::dropIfExists('carbon_footprint_reports');
    }
};
