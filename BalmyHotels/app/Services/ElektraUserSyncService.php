<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Department;
use App\Models\PdksEmployee;
use App\Models\Role;
use App\Models\User;
use App\Services\Pdks\ElektraPdksService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ElektraUserSyncService
{
    private const SOURCE = 'elektra_foresta';

    public function __construct(private readonly ElektraPdksService $elektra)
    {
    }

    public function syncForesta(): array
    {
        $config = config('services.elektra_pdks.foresta');
        $tenantId = (string) ($config['user_sync_tenant_id'] ?? $config['tenant_id'] ?? '');
        $companyId = (string) ($config['company_id'] ?? '');
        $branchId = (int) ($config['branch_id'] ?? 0);

        if ($tenantId === '' || $companyId === '' || !$branchId) {
            throw new RuntimeException('Elektra Foresta tenant, firma veya şube ayarı eksik.');
        }

        if (!Branch::whereKey($branchId)->exists()) {
            throw new RuntimeException("Foresta şubesi bulunamadı (şube ID: {$branchId}).");
        }

        if (!Role::where('name', 'ogrenen')->exists()) {
            throw new RuntimeException('"ogrenen" rolü bulunamadı; senkronizasyon yapılmadı.');
        }

        $lock = Cache::lock("elektra-users:{$tenantId}:{$companyId}", 120);

        if (!$lock->get()) {
            throw new RuntimeException('Elektra kullanıcı senkronizasyonu zaten çalışıyor.');
        }

        try {
            $params = [
                'TENANTID' => (int) $tenantId,
                'FIRMAID' => $companyId,
                'DEPARTMANID' => null,
                'SICILID' => null,
                'PDKSKARTNO' => null,
                'CALISIYOR' => 1,
            ];

            $rows = $this->elektra->fetchRows($config, $params);
            $minimum = max(1, (int) ($config['user_sync_min_active'] ?? 25));
            $minimumRatio = min(1, max(0.1, (float) ($config['user_sync_min_ratio'] ?? 0.6)));

            if (count($rows) < $minimum) {
                throw new RuntimeException(
                    'Elektra beklenenden az aktif personel döndürdü (' . count($rows) .
                    "). Güvenlik için işlem ve pasife alma durduruldu; alt sınır: {$minimum}."
                );
            }

            return $this->synchronize($rows, $tenantId, $companyId, $branchId, $minimum, $minimumRatio);
        } finally {
            $lock->release();
        }
    }

    private function synchronize(
        array $rows,
        string $tenantId,
        string $companyId,
        int $branchId,
        int $minimum,
        float $minimumRatio
    ): array
    {
        $prepared = $this->prepareRows($rows);
        $activeRows = $prepared['rows'];
        $activeIds = array_keys($activeRows);

        if (count($activeRows) < $minimum) {
            throw new RuntimeException(
                'Elektra yanıtında yalnızca ' . count($activeRows) .
                " geçerli ve tekil SICILID bulundu. Güvenlik için işlem durduruldu; alt sınır: {$minimum}."
            );
        }

        $knownLinkedCount = User::query()
            ->where('elektra_tenant_id', $tenantId)
            ->where('elektra_company_id', $companyId)
            ->whereNotNull('elektra_sicil_id')
            ->count();

        if ($knownLinkedCount >= $minimum
            && count($activeRows) < (int) floor($knownLinkedCount * $minimumRatio)) {
            throw new RuntimeException(
                'Aktif Elektra listesi önceki bağlı personel sayısına göre olağan dışı düşük geldi. ' .
                'Toplu pasife almayı önlemek için işlem durduruldu.'
            );
        }

        $localUsers = User::query()->get();
        $maps = $this->buildUserMaps($localUsers, $tenantId, $companyId, $branchId, $activeRows);
        $departmentMap = $this->departmentMap($branchId);

        $result = [
            'total' => count($activeRows),
            'created' => 0,
            'updated' => 0,
            'adopted' => 0,
            'reactivated' => 0,
            'deactivated' => 0,
            'conflicts' => $prepared['duplicate_sicil_count'],
            'login_unavailable' => 0,
            'conflict_names' => [],
        ];

        return DB::transaction(function () use (
            $activeRows,
            $activeIds,
            $tenantId,
            $companyId,
            $branchId,
            $maps,
            &$departmentMap,
            $result
        ) {
            $stats = $result;

            foreach ($activeRows as $sicilId => $row) {
                $name = $this->employeeName($row, $sicilId);
                $nameKey = User::normalizePersonName($name);
                $phone = User::normalizeTurkishPhone($this->firstValue($row, ['GSM', 'TEL', 'TELEFON', 'PHONE']));
                $identityNumber = User::normalizeIdentityNumber($this->firstValue($row, ['TCKNO', 'TCNO', 'TCKIMLIKNO', 'KIMLIKNO']));
                $identityHash = $identityNumber ? User::identityHash($identityNumber) : null;

                if ($identityHash && ($maps['identity_counts'][$identityHash] ?? 0) > 1) {
                    $identityHash = null;
                }

                $user = $maps['linked'][$sicilId] ?? null;
                $adopted = false;

                if (!$user && isset($maps['pdks'][$sicilId])) {
                    $user = $maps['pdks'][$sicilId];
                }

                if (!$user && $identityHash) {
                    $user = $maps['identity'][$identityHash] ?? null;
                }

                if (!$user && $phone && ($maps['api_phone_counts'][$phone] ?? 0) === 1) {
                    $safeKey = $nameKey . '|' . $phone;
                    $candidates = $maps['manual_name_phone'][$safeKey] ?? collect();

                    if ($candidates->count() === 1) {
                        $user = $candidates->first();
                        $adopted = true;
                    }
                }

                // Aynı ad hem Foresta aktif listesinde hem Foresta yerel hesaplarında tekilse
                // mevcut hesabı kullan. Şube filtresi Beach'teki adaş hesabı dışarıda bırakır.
                if (!$user && ($maps['api_name_counts'][$nameKey] ?? 0) === 1) {
                    $candidates = $maps['manual_names'][$nameKey] ?? collect();

                    if ($candidates->count() === 1) {
                        $user = $candidates->first();
                        $adopted = true;
                    }
                }

                if (!$user && isset($maps['manual_names'][$nameKey])) {
                    $stats['conflicts']++;
                    $this->appendConflictName($stats['conflict_names'], $name);
                    continue;
                }

                if ($identityHash) {
                    $hashOwner = $maps['identity'][$identityHash] ?? null;
                    if ($hashOwner && $user && $hashOwner->id !== $user->id) {
                        $stats['conflicts']++;
                        $this->appendConflictName($stats['conflict_names'], $name);
                        continue;
                    }
                }

                $departmentName = $this->firstValue($row, ['DEP', 'DEPARTMANADI', 'DEPARTMAN_ADI', 'DEPARTMENT']);
                $department = $this->resolveDepartment($branchId, $departmentName, $departmentMap);
                $title = $this->firstValue($row, ['GOREV', 'UNVAN', 'MESLEKADI', 'POZISYON']);
                $wasActive = $user?->is_active;

                if (!$user) {
                    $user = new User();
                    $user->email = $this->availableEmail($row, $tenantId, $sicilId);
                    $user->password = Str::random(64);
                    $user->role = 'ogrenen';
                    $user->fault_notify = false;
                    $user->account_source = 'elektra';
                    $stats['created']++;
                } else {
                    $stats['updated']++;
                    if ($adopted) {
                        $user->account_source = 'elektra_linked';
                        $stats['adopted']++;
                    }
                    if ($wasActive === false) {
                        $stats['reactivated']++;
                    }
                }

                $user->fill([
                    'name' => $name,
                    'branch_id' => $branchId,
                    'department_id' => $department?->id,
                    'phone' => $phone ? '+' . $phone : null,
                    'phone_normalized' => $phone,
                    'title' => $title,
                    'is_active' => true,
                    'elektra_tenant_id' => $tenantId,
                    'elektra_company_id' => $companyId,
                    'elektra_sicil_id' => $sicilId,
                    'identity_no_hash' => $identityHash,
                    'elektra_synced_at' => now(),
                ]);
                $user->save();

                $user->userRoles()->firstOrCreate(['role_name' => 'ogrenen']);

                if (!$identityHash || !$phone) {
                    $stats['login_unavailable']++;
                }

                $this->syncPdksEmployee(
                    $row,
                    $user,
                    $department,
                    $identityNumber,
                    $tenantId,
                    $companyId,
                    $sicilId,
                    $branchId
                );
            }

            $staleUsers = User::query()
                ->where('elektra_tenant_id', $tenantId)
                ->where('elektra_company_id', $companyId)
                ->whereNotNull('elektra_sicil_id')
                ->whereNotIn('elektra_sicil_id', $activeIds)
                ->where('is_active', true);

            $stats['deactivated'] = (clone $staleUsers)->count();
            $staleUsers->update(['is_active' => false, 'elektra_synced_at' => now()]);

            PdksEmployee::query()
                ->where('source', self::SOURCE)
                ->where('tenant_id', $tenantId)
                ->where('company_id', $companyId)
                ->whereNotIn('external_employee_id', $activeIds)
                ->update(['is_active' => false, 'last_synced_at' => now()]);

            return $stats;
        });
    }

    private function prepareRows(array $rows): array
    {
        $activeRows = [];
        $duplicateSicilCount = 0;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $sicilId = $this->firstValue($row, ['SICILID', 'SICIL_ID', 'SICILKAYITID']);
            if (!$sicilId) {
                continue;
            }

            if (isset($activeRows[$sicilId])) {
                $duplicateSicilCount++;
                continue;
            }

            $activeRows[$sicilId] = $row;
        }

        return ['rows' => $activeRows, 'duplicate_sicil_count' => $duplicateSicilCount];
    }

    private function buildUserMaps(
        Collection $users,
        string $tenantId,
        string $companyId,
        int $branchId,
        array $activeRows
    ): array {
        $activeIds = array_keys($activeRows);
        $linked = $users
            ->filter(fn (User $user) => (string) $user->elektra_tenant_id === $tenantId
                && (string) $user->elektra_company_id === $companyId
                && $user->elektra_sicil_id)
            ->keyBy(fn (User $user) => (string) $user->elektra_sicil_id);

        $manual = $users->filter(fn (User $user) => !$user->elektra_sicil_id
            && (!$user->branch_id || (int) $user->branch_id === $branchId));

        $manualNames = $manual->groupBy(fn (User $user) => User::normalizePersonName($user->name));
        $manualNamePhone = $manual
            ->filter(fn (User $user) => User::normalizeTurkishPhone($user->phone) !== null)
            ->groupBy(fn (User $user) => User::normalizePersonName($user->name)
                . '|' . User::normalizeTurkishPhone($user->phone));

        $pdks = PdksEmployee::query()
            ->with('user')
            ->where('source', self::SOURCE)
            ->where('company_id', $companyId)
            ->whereIn('external_employee_id', $activeIds)
            ->whereNotNull('user_id')
            ->get()
            ->groupBy(fn (PdksEmployee $employee) => (string) $employee->external_employee_id)
            ->map(function (Collection $employees) {
                $users = $employees->pluck('user')->filter()->unique('id');

                return $users->count() === 1 ? $users->first() : null;
            })
            ->filter();

        $identity = $users
            ->whereNotNull('identity_no_hash')
            ->keyBy('identity_no_hash');

        $identityCounts = [];
        $apiPhoneCounts = [];
        $apiNameCounts = [];
        foreach ($activeRows as $row) {
            $nameKey = User::normalizePersonName(
                $this->employeeName($row, (string) ($this->firstValue($row, ['SICILID']) ?? ''))
            );
            $apiNameCounts[$nameKey] = ($apiNameCounts[$nameKey] ?? 0) + 1;

            $identityNumber = User::normalizeIdentityNumber(
                $this->firstValue($row, ['TCKNO', 'TCNO', 'TCKIMLIKNO', 'KIMLIKNO'])
            );
            if ($identityNumber) {
                $identityHash = User::identityHash($identityNumber);
                $identityCounts[$identityHash] = ($identityCounts[$identityHash] ?? 0) + 1;
            }

            $phone = User::normalizeTurkishPhone(
                $this->firstValue($row, ['GSM', 'TEL', 'TELEFON', 'PHONE'])
            );
            if ($phone) {
                $apiPhoneCounts[$phone] = ($apiPhoneCounts[$phone] ?? 0) + 1;
            }
        }

        return [
            'linked' => $linked,
            'manual_names' => $manualNames,
            'manual_name_phone' => $manualNamePhone,
            'pdks' => $pdks,
            'identity' => $identity,
            'identity_counts' => $identityCounts,
            'api_phone_counts' => $apiPhoneCounts,
            'api_name_counts' => $apiNameCounts,
        ];
    }

    private function departmentMap(int $branchId): Collection
    {
        return Department::where('branch_id', $branchId)
            ->get()
            ->keyBy(fn (Department $department) => User::normalizePersonName($department->name));
    }

    private function resolveDepartment(int $branchId, ?string $name, Collection &$map): ?Department
    {
        $name = Str::squish((string) $name);
        if ($name === '') {
            return null;
        }

        $name = $this->canonicalDepartmentName($name);

        $key = User::normalizePersonName($name);
        if ($map->has($key)) {
            return $map->get($key);
        }

        $department = Department::create([
            'branch_id' => $branchId,
            'name' => $name,
            'color' => '#c19b77',
            'is_active' => true,
            'fault_assignable' => false,
        ]);
        $map->put($key, $department);

        return $department;
    }

    private function canonicalDepartmentName(string $name): string
    {
        $normalisedName = User::normalizePersonName($name);

        foreach ((array) config('services.elektra_pdks.foresta.department_aliases', []) as $alias => $canonical) {
            if (User::normalizePersonName($alias) === $normalisedName) {
                return Str::squish((string) $canonical);
            }
        }

        return $name;
    }

    private function availableEmail(array $row, string $tenantId, string $sicilId): string
    {
        $email = strtolower((string) $this->firstValue($row, ['EMAIL', 'EMAIL2', 'EPOSTA', 'MAIL']));
        if (filter_var($email, FILTER_VALIDATE_EMAIL) && !User::where('email', $email)->exists()) {
            return $email;
        }

        $safeSicilId = substr(preg_replace('/[^a-zA-Z0-9._-]/', '', $sicilId), 0, 80) ?: Str::random(12);
        $base = "elektra.{$tenantId}.{$safeSicilId}";
        $candidate = "{$base}@users.balmy.invalid";
        $suffix = 1;

        while (User::where('email', $candidate)->exists()) {
            $candidate = "{$base}.{$suffix}@users.balmy.invalid";
            $suffix++;
        }

        return $candidate;
    }

    private function syncPdksEmployee(
        array $row,
        User $user,
        ?Department $department,
        ?string $identityNumber,
        string $tenantId,
        string $companyId,
        string $sicilId,
        int $branchId
    ): void {
        PdksEmployee::updateOrCreate(
            [
                'source' => self::SOURCE,
                'tenant_id' => $tenantId,
                'company_id' => $companyId,
                'external_employee_id' => $sicilId,
            ],
            [
                'branch_id' => $branchId,
                'department_id' => $department?->id,
                'user_id' => $user->id,
                'registry_no' => $this->firstValue($row, ['SICILNO', 'SICIL_NO']),
                'pdks_card_no' => $this->firstValue($row, ['PDKSKARTNO', 'PDKS_KART_NO', 'KARTNO']),
                'identity_no' => $identityNumber,
                'name' => $user->name,
                'email' => $this->firstValue($row, ['EMAIL', 'EMAIL2']),
                'phone' => $this->firstValue($row, ['GSM', 'TEL']),
                'title' => $user->title,
                'employment_type' => $this->firstValue($row, ['BORDROTIPI', 'CALISMAZAMANI']),
                'started_at' => $this->parseDate($this->firstValue($row, ['ISEGIRIS', 'ISEGIRISTARIHI'])),
                'ended_at' => $this->parseDate($this->firstValue($row, ['CIKIS', 'ISTENCIKISTARIHI'])),
                'is_active' => true,
                'raw_payload' => $row,
                'last_synced_at' => now(),
            ]
        );
    }

    private function employeeName(array $row, string $sicilId): string
    {
        $name = $this->firstValue($row, ['ADISOYADI', 'ADI_SOYADI', 'ADSOYAD', 'NAME']);
        if (!$name) {
            $name = trim(
                (string) $this->firstValue($row, ['AD', 'ADI']) . ' ' .
                (string) $this->firstValue($row, ['SOYAD', 'SOYADI'])
            );
        }

        return Str::squish($name ?: 'Elektra Personel ' . $sicilId);
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
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function appendConflictName(array &$names, string $name): void
    {
        if (count($names) < 8 && !in_array($name, $names, true)) {
            $names[] = $name;
        }
    }
}
