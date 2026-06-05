<?php

namespace App\Services\Pdks;

use App\Models\Department;
use App\Models\PdksEmployee;
use App\Models\PdksSyncLog;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ElektraPdksService
{
    public function syncForesta(?array $overrides = []): PdksSyncLog
    {
        $config = config('services.elektra_pdks.foresta');
        $branchId = (int) ($overrides['branch_id'] ?? $config['branch_id']);
        $params = $this->buildParameters($config, $overrides);

        $log = PdksSyncLog::create([
            'branch_id' => $branchId ?: null,
            'source' => 'elektra_foresta',
            'status' => 'started',
            'parameters' => $params,
            'started_at' => now(),
        ]);

        try {
            $rows = $this->fetchRows($config, $params);
            $created = 0;
            $updated = 0;
            $failed = 0;

            foreach ($rows as $row) {
                try {
                    $mapped = $this->mapEmployee($row, $branchId, $params);
                    if (empty($mapped['user_id'])) {
                        unset($mapped['user_id']);
                    }
                    $key = [
                        'source' => 'elektra_foresta',
                        'tenant_id' => (string) $params['TENANTID'],
                        'company_id' => (string) $params['FIRMAID'],
                        'external_employee_id' => $mapped['external_employee_id'],
                    ];

                    $employee = PdksEmployee::where($key)->first();
                    $employee ? $updated++ : $created++;
                    PdksEmployee::updateOrCreate($key, $mapped);
                } catch (\Throwable) {
                    $failed++;
                }
            }

            $log->update([
                'status' => 'success',
                'total_records' => count($rows),
                'created_records' => $created,
                'updated_records' => $updated,
                'failed_records' => $failed,
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
        }

        return $log->fresh();
    }

    public function fetchRows(array $config, array $params): array
    {
        if (empty($config['api_key'])) {
            throw new RuntimeException('Elektra PDKS API key tanımlı değil.');
        }

        $response = Http::withHeaders([
            'Authorization' => $config['api_key'],
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->timeout(45)->post($config['endpoint'], [
            'Parameters' => $params,
            'Action' => 'Function',
            'Object' => 'FN_API_SICIL_LISTE',
            'OrderBy' => [
                ['Column' => 'null', 'Direction' => null],
            ],
            'Where' => [],
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('Elektra PDKS yanıtı başarısız: HTTP ' . $response->status());
        }

        $payload = $response->json();

        return $this->extractRows($payload);
    }

    private function buildParameters(array $config, array $overrides): array
    {
        return [
            'TENANTID' => $overrides['tenant_id'] ?? $config['tenant_id'],
            'FIRMAID' => (string) ($overrides['company_id'] ?? $config['company_id']),
            'DEPARTMANID' => $overrides['department_id'] ?? null,
            'SICILID' => $overrides['registry_id'] ?? null,
            'PDKSKARTNO' => $overrides['pdks_card_no'] ?? null,
            'CALISIYOR' => $overrides['active'] ?? 1,
        ];
    }

    private function extractRows(?array $payload): array
    {
        if (!$payload) {
            return [];
        }

        foreach (['ResultSets.0', 'ResultSets.0.Rows', 'Data', 'data', 'Result', 'Rows', 'rows'] as $path) {
            $value = Arr::get($payload, $path);

            if (is_array($value) && array_is_list($value)) {
                return $value;
            }
        }

        if (array_is_list($payload)) {
            return $payload;
        }

        return [];
    }

    private function mapEmployee(array $row, int $branchId, array $params): array
    {
        $externalId = $this->firstValue($row, ['SICILID', 'SICIL_ID', 'ID', 'id', 'SICILKAYITID']);
        $registryNo = $this->firstValue($row, ['SICILNO', 'SICIL_NO', 'SICILKODU', 'REGISTRY_NO']);
        $cardNo = $this->firstValue($row, ['PDKSKARTNO', 'PDKS_KART_NO', 'KARTNO', 'CARDNO']);
        $name = $this->firstValue($row, ['ADISOYADI', 'ADI_SOYADI', 'ADSOYAD', 'NAME']);
        $email = $this->firstValue($row, ['EMAIL', 'EPOSTA', 'MAIL']);

        if (!$name) {
            $name = trim($this->firstValue($row, ['ADI', 'AD']) . ' ' . $this->firstValue($row, ['SOYADI', 'SOYAD']));
        }

        $externalId = $externalId ?: $registryNo ?: $cardNo ?: md5(json_encode($row));
        $department = $this->resolveDepartment($branchId, $this->firstValue($row, ['DEPARTMANADI', 'DEPARTMAN_ADI', 'DEPARTMENT']));

        return [
            'branch_id' => $branchId ?: null,
            'department_id' => $department?->id,
            'user_id' => $email ? User::where('email', $email)->value('id') : null,
            'source' => 'elektra_foresta',
            'tenant_id' => (string) $params['TENANTID'],
            'company_id' => (string) $params['FIRMAID'],
            'external_employee_id' => (string) $externalId,
            'registry_no' => $registryNo,
            'pdks_card_no' => $cardNo,
            'identity_no' => $this->firstValue($row, ['TCNO', 'TCKIMLIKNO', 'KIMLIKNO']),
            'name' => $name ?: 'Elektra Personel ' . $externalId,
            'email' => $email,
            'phone' => $this->firstValue($row, ['TELEFON', 'GSM', 'PHONE']),
            'title' => $this->firstValue($row, ['GOREV', 'UNVAN', 'POZISYON']),
            'employment_type' => $this->firstValue($row, ['CALISMATIPI', 'ISTIHDAMTIPI']),
            'started_at' => $this->parseDate($this->firstValue($row, ['ISEGIRISTARIHI', 'GIRISTARIHI', 'STARTDATE'])),
            'ended_at' => $this->parseDate($this->firstValue($row, ['ISTENCIKISTARIHI', 'CIKISTARIHI', 'ENDDATE'])),
            'is_active' => (int) ($this->firstValue($row, ['CALISIYOR', 'AKTIF', 'ACTIVE']) ?? 1) !== 0,
            'raw_payload' => $row,
            'last_synced_at' => now(),
        ];
    }

    private function resolveDepartment(int $branchId, ?string $name): ?Department
    {
        $name = trim((string) $name);

        if ($name === '' || !$branchId) {
            return null;
        }

        return Department::firstOrCreate(
            ['branch_id' => $branchId, 'name' => $name],
            ['color' => '#c19b77', 'is_active' => true, 'fault_assignable' => false]
        );
    }

    private function firstValue(array $row, array $keys): ?string
    {
        $normalised = [];
        foreach ($row as $key => $value) {
            $normalised[Str::upper((string) $key)] = $value;
        }

        foreach ($keys as $key) {
            $value = $normalised[Str::upper($key)] ?? null;

            if ($value !== null && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }

    private function parseDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
