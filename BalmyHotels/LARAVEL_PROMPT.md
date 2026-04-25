# Laravel Backend Prompt — IT Inventory Agent

Aşağıdaki prompt'u Laravel projenizde kullanın. Bu prompt, IT Inventory Agent'ın
gönderdiği verileri alacak, veritabanına kaydedecek eksiksiz bir backend oluşturur.

---

## PROMPT (Laravel projenize gönderin)

```
Bir Windows IT Envanter Agent'ından gelen sistem bilgilerini kaydeden bir Laravel backend oluştur.
Database: MySQL (balmycrmdatabase)

### 1 — VERİTABANI TABLOLARI (Migration)

Aşağıdaki migration'ları oluştur:

---

#### a) computers

```php
Schema::create('computers', function (Blueprint $table) {
    $table->id();
    $table->string('machine_guid', 100)->unique();  // Windows registry GUID
    $table->string('hostname', 255);
    $table->string('os_product_name', 255)->nullable();
    $table->string('os_version', 100)->nullable();
    $table->string('os_release', 50)->nullable();
    $table->string('os_build_number', 50)->nullable();
    $table->string('os_architecture', 20)->nullable();
    $table->date('os_install_date')->nullable();
    $table->string('os_registered_owner', 255)->nullable();
    $table->string('os_serial_number', 100)->nullable();
    $table->boolean('is_domain_joined')->default(false);
    $table->string('domain_name', 255)->nullable();
    $table->string('workgroup_name', 255)->nullable();
    $table->string('domain_controller', 255)->nullable();
    $table->json('current_users')->nullable();       // ["user1","user2"]
    $table->datetime('last_boot_time')->nullable();
    $table->string('agent_version', 20)->nullable();
    $table->datetime('reported_at')->nullable();     // Agent'ın gönderdiği zaman
    $table->datetime('last_seen_at')->nullable();    // Sunucunun aldığı zaman
    $table->timestamps();
    
    $table->index('hostname');
    $table->index('last_seen_at');
    $table->index('is_domain_joined');
});
```

---

#### b) computer_hardware

```php
Schema::create('computer_hardware', function (Blueprint $table) {
    $table->id();
    $table->foreignId('computer_id')->constrained('computers')->cascadeOnDelete();
    $table->string('cpu_name', 255)->nullable();
    $table->unsignedSmallInteger('cpu_cores_physical')->nullable();
    $table->unsignedSmallInteger('cpu_cores_logical')->nullable();
    $table->unsignedInteger('cpu_speed_mhz')->nullable();
    $table->float('cpu_usage_percent')->nullable();
    $table->float('total_ram_gb')->nullable();
    $table->float('available_ram_gb')->nullable();
    $table->float('ram_usage_percent')->nullable();
    $table->json('ram_slots')->nullable();   // [{capacity_gb, speed_mhz, manufacturer, ...}]
    $table->string('motherboard', 255)->nullable();
    $table->string('bios_version', 100)->nullable();
    $table->string('bios_date', 20)->nullable();
    $table->timestamps();
});
```

---

#### c) computer_network_adapters

```php
Schema::create('computer_network_adapters', function (Blueprint $table) {
    $table->id();
    $table->foreignId('computer_id')->constrained('computers')->cascadeOnDelete();
    $table->string('adapter_name', 255);
    $table->string('mac_address', 20)->nullable();
    $table->string('ip_address', 45)->nullable();    // IPv4
    $table->string('ip_address_v6', 50)->nullable(); // IPv6
    $table->string('subnet_mask', 45)->nullable();
    $table->string('gateway', 45)->nullable();
    $table->json('dns_servers')->nullable();          // ["8.8.8.8","8.8.4.4"]
    $table->boolean('dhcp_enabled')->nullable();
    $table->string('dhcp_server', 45)->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->index('computer_id');
    $table->index('ip_address');
    $table->index('mac_address');
});
```

---

#### d) computer_disks

```php
Schema::create('computer_disks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('computer_id')->constrained('computers')->cascadeOnDelete();
    $table->string('drive_letter', 10);
    $table->string('mount_point', 255)->nullable();
    $table->string('filesystem', 20)->nullable();
    $table->string('label', 255)->nullable();
    $table->float('total_space_gb')->nullable();
    $table->float('used_space_gb')->nullable();
    $table->float('free_space_gb')->nullable();
    $table->float('usage_percent')->nullable();
    $table->timestamps();
    
    $table->index('computer_id');
});
```

---

#### e) computer_antivirus

```php
Schema::create('computer_antivirus', function (Blueprint $table) {
    $table->id();
    $table->foreignId('computer_id')->constrained('computers')->cascadeOnDelete();
    $table->string('product_name', 255);
    $table->string('product_state_raw', 20)->nullable();
    $table->boolean('is_enabled')->default(false);
    $table->boolean('is_up_to_date')->default(false);
    $table->string('timestamp', 100)->nullable();
    $table->timestamps();
    
    $table->index('computer_id');
});
```

---

#### f) computer_security

```php
Schema::create('computer_security', function (Blueprint $table) {
    $table->id();
    $table->foreignId('computer_id')->constrained('computers')->cascadeOnDelete()->unique();
    $table->boolean('rdp_enabled')->nullable();
    $table->boolean('uac_enabled')->nullable();
    $table->boolean('firewall_domain')->nullable();
    $table->boolean('firewall_private')->nullable();
    $table->boolean('firewall_public')->nullable();
    $table->tinyInteger('auto_update')->nullable();  // 1=Kapalı 2=Bildir 3=İndir 4=Kur
    $table->string('last_windows_update', 100)->nullable();
    $table->json('bitlocker')->nullable();            // {"C:": {"is_encrypted": true}}
    $table->timestamps();
});
```

---

#### g) computer_installed_programs

```php
Schema::create('computer_installed_programs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('computer_id')->constrained('computers')->cascadeOnDelete();
    $table->string('name', 500);
    $table->string('version', 100)->nullable();
    $table->string('publisher', 255)->nullable();
    $table->string('install_date', 20)->nullable();
    $table->string('install_location', 500)->nullable();
    $table->timestamps();
    
    $table->index('computer_id');
    $table->index('name');
    $table->index(['computer_id', 'name']);
});
```

---

### 2 — MODELLER

Her migration için bir Eloquent Model oluştur:

**Computer** modeli şu ilişkileri içersin:
- hasOne(ComputerHardware)
- hasOne(ComputerSecurity)
- hasMany(ComputerNetworkAdapter)
- hasMany(ComputerDisk)
- hasMany(ComputerAntivirus)
- hasMany(ComputerInstalledProgram)

---

### 3 — MİDDLEWARE: API Key Doğrulama

`app/Http/Middleware/AgentKeyMiddleware.php` oluştur:

- Request header'ından `X-Agent-Key` değerini al
- `config('agent.key')` ile karşılaştır (sabit karşılaştırma: `hash_equals`)
- Eşleşmiyorsa 401 JSON döndür: `{"error": "Unauthorized"}`

`config/agent.php` dosyası oluştur:
```php
return [
    'key' => env('AGENT_API_KEY', ''),
];
```

`.env` dosyasına ekle:
```
AGENT_API_KEY=CHANGE_THIS_TO_A_STRONG_SECRET_KEY
```

Bu key, agent'ın `agent/config.py` dosyasındaki `API_KEY` ile TAM AYNI olmalıdır.

Middleware'i `app/Http/Kernel.php`'de `$routeMiddleware` dizisine ekle:
```php
'agent.key' => \App\Http\Middleware\AgentKeyMiddleware::class,
```

---

### 4 — CONTROLLER: Agent Raporu Al

`app/Http/Controllers/Api/AgentReportController.php` oluştur.

Tek metot: `store(Request $request)`

**Gelen JSON yapısı:**
```json
{
  "machine_guid": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
  "hostname": "DESKTOP-ABC123",
  "agent_version": "1.0.0",
  "reported_at": "2025-03-25T14:30:00",
  "last_boot_time": "2025-03-25T08:00:00",
  "current_users": ["john.doe"],
  "os": {
    "product_name": "Windows 11 Pro",
    "version": "10.0.22621",
    "release": "11",
    "build_number": "22621.3296",
    "architecture": "AMD64",
    "install_date": "2024-01-15",
    "registered_owner": "John Doe",
    "serial_number": "XXXXX-XXXXX-XXXXX-XXXXX"
  },
  "domain": {
    "is_domain_joined": true,
    "domain_name": "COMPANY.LOCAL",
    "workgroup_name": null,
    "domain_controller": "DC01.COMPANY.LOCAL"
  },
  "hardware": {
    "cpu_name": "Intel(R) Core(TM) i7-8700 CPU @ 3.20GHz",
    "cpu_speed_mhz": 3200,
    "cpu_cores_physical": 6,
    "cpu_cores_logical": 12,
    "cpu_usage_percent": 15.3,
    "total_ram_gb": 16.0,
    "available_ram_gb": 9.2,
    "ram_usage_percent": 42.5,
    "ram_slots": [{"capacity_gb": 8, "speed_mhz": 2666, "manufacturer": "Samsung"}],
    "motherboard": "ASUS Z370-A",
    "bios_version": "2401",
    "bios_date": "20230101"
  },
  "network_adapters": [
    {
      "adapter_name": "Intel(R) Ethernet Connection",
      "mac_address": "00:1A:2B:3C:4D:5E",
      "ip_address": "192.168.1.100",
      "ip_address_v6": null,
      "subnet_mask": "255.255.255.0",
      "gateway": "192.168.1.1",
      "dns_servers": ["192.168.1.1", "8.8.8.8"],
      "dhcp_enabled": true,
      "dhcp_server": "192.168.1.1",
      "is_active": true
    }
  ],
  "disks": [
    {
      "drive_letter": "C:\\",
      "mount_point": "C:\\",
      "filesystem": "NTFS",
      "label": "Windows",
      "total_space_gb": 237.5,
      "used_space_gb": 120.3,
      "free_space_gb": 117.2,
      "usage_percent": 50.6
    }
  ],
  "antivirus": [
    {
      "product_name": "Windows Defender",
      "product_state_raw": "397568",
      "is_enabled": true,
      "is_up_to_date": true,
      "timestamp": "20250101120000.000000+000"
    }
  ],
  "security": {
    "rdp_enabled": false,
    "uac_enabled": true,
    "firewall_domain": true,
    "firewall_private": true,
    "firewall_public": true,
    "auto_update": 4,
    "last_windows_update": "2025-03-01 10:22:00",
    "bitlocker": {"C:": {"protection_status": 1, "is_encrypted": true}}
  },
  "installed_programs": [
    {
      "name": "Google Chrome",
      "version": "123.0.0.0",
      "publisher": "Google LLC",
      "install_date": "20240901",
      "install_location": "C:\\Program Files\\Google\\Chrome"
    }
  ]
}
```

**Controller mantığı (DB transaction içinde):**

1. `machine_guid` ile `computers` tablosunda `updateOrCreate` yap
2. `computer_hardware` → `updateOrCreate(['computer_id' => $computer->id], [...])`
3. `computer_security`  → `updateOrCreate(['computer_id' => $computer->id], [...])`
4. `computer_network_adapters` → Önce `deleteWhere(['computer_id' => ...])`, sonra `insert` (chunk ile)
5. `computer_disks`            → Aynı şekilde (sil + yeniden ekle)
6. `computer_antivirus`        → Aynı şekilde (sil + yeniden ekle)
7. `computer_installed_programs` → Aynı şekilde (sil + chunk insert, 100'erli gruplar)
8. `computers.last_seen_at` = `now()` olarak güncelle

**Validation:**
- `machine_guid`: required, string, max:100
- `hostname`: required, string, max:255
- Diğer alanlar: nullable (eksik veri gelse de kaydet)

**Response:**
```json
{"message": "OK", "computer_id": 42}
```

---

### 5 — CONTROLLER: Bilgisayar Listeleme / Detay

`app/Http/Controllers/Api/ComputerController.php` oluştur:

**index()** — Tüm bilgisayarlar (paginate 50):
```json
{
  "data": [
    {
      "id": 1,
      "hostname": "DESKTOP-ABC123",
      "os_product_name": "Windows 11 Pro",
      "is_domain_joined": true,
      "domain_name": "COMPANY.LOCAL",
      "ip_address": "192.168.1.100",   // ilk aktif adaptörden
      "last_seen_at": "2025-03-25T14:30:00",
      "disk_usage_warning": true        // herhangi bir disk >85% ise true
    }
  ],
  "meta": {"total": 150, "per_page": 50}
}
```

**show($id)** — Tek bilgisayar tüm detaylarıyla:
- `with(['hardware','security','networkAdapters','disks','antivirus'])`
- `installed_programs` ayrı endpoint veya pagination

**programs($id)** — Yüklü programlar (paginate 100):

**Filtreler (index):**
- `?search=hostname_veya_ip`
- `?domain=COMPANY.LOCAL`
- `?os=Windows 11`
- `?av_disabled=1` — antivirüs kapalı olanlar
- `?disk_warning=1` — disk doluluk >80%
- `?rdp_enabled=1`

---

### 6 — ROTALAR (routes/api.php)

```php
// Agent rotası (sadece API key ile erişilir)
Route::middleware('agent.key')->post('/agent/report', [AgentReportController::class, 'store']);

// Yönetim rotaları (kendi auth middleware'inizi ekleyin)
Route::prefix('computers')->group(function () {
    Route::get('/',           [ComputerController::class, 'index']);
    Route::get('/{id}',       [ComputerController::class, 'show']);
    Route::get('/{id}/programs', [ComputerController::class, 'programs']);
    Route::delete('/{id}',    [ComputerController::class, 'destroy']);
});
```

---

### 7 — İSTATİSTİK ENDPOINT (Opsiyonel)

`GET /api/computers/stats` → Dashboard özeti:
```json
{
  "total_computers": 150,
  "domain_joined": 142,
  "av_disabled": 3,
  "disk_warning": 12,
  "rdp_enabled": 8,
  "os_breakdown": {
    "Windows 11 Pro": 80,
    "Windows 10 Pro": 65,
    "Windows Server 2019": 5
  },
  "seen_last_hour": 130,
  "seen_last_24h": 148,
  "never_seen": 2
}
```

---

### 8 — NOTLAR

- `installed_programs` tablosu büyük olabilir (1000 bilgisayar × 200 program = 200.000 satır).
  Toplu insert için `DB::table('computer_installed_programs')->insert($chunk)` kullan, chunk size 100.
  
- `computers` tablosunun `machine_guid` kolonuna unique index ekle.

- `last_seen_at` her raporda güncellenir; bu kolona index ekle (offline makine sorguları için).

- Tüm controller metodlarını `DB::transaction()` içine al.

- CORS: Eğer frontend başka bir adreste ise `config/cors.php`'de `api/*` path'i ayarla.

- Rate Limiting: `routes/api.php`'de `agent/report` route'una
  `throttle:60,1` middleware ekle (dakikada 60 istek).
```

---

## ÖZET: Agent → Laravel Akışı

```
Windows PC
  ↓  (her 5 dakikada bir)
  POST /api/agent/report
  Header: X-Agent-Key: <shared_secret>
  Body: JSON (makine, OS, ağ, disk, AV, programlar...)
  ↓
Laravel Middleware (AgentKeyMiddleware)
  ↓ hash_equals() ile key doğrula
AgentReportController::store()
  ↓ DB Transaction
  computers         → updateOrCreate (machine_guid ile)
  computer_hardware → updateOrCreate
  computer_security → updateOrCreate
  computer_network_adapters → delete + bulk insert
  computer_disks            → delete + bulk insert
  computer_antivirus        → delete + bulk insert
  computer_installed_programs → delete + chunked bulk insert
  ↓
Response: {"message": "OK", "computer_id": 42}
```
