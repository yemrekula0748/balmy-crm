# Balmy Hotels — Yönetim Paneli

> Laravel 10 tabanlı, iki şubeli otel yönetim sistemi.

---

## Şubeler
| ID | Ad |
|----|----|
| 1  | Balmy Beach Resort |
| 2  | Balmy Foresta |

---

## Teknik Altyapı
- **Laravel** 10.x
- **Veritabanı** SQLite (local) → MySQL (production)
- **UI** Balmy Admin Template (Bootstrap 5)
- **Renk** `#c19b77`
- **Auth** Laravel Sanctum + özel rol/yetki sistemi
- **Çoklu Şube** Her kullanıcı bir şubeye bağlı; veriler şubeye göre filtrelenir

---

## Kullanıcı & Yetki Yapısı
| Rol | Açıklama |
|-----|----------|
| `super_admin` | Her iki şubeyi görür, tüm yetkiler |
| `branch_manager` | Kendi şubesini görür, tüm modüller |
| `dept_manager` | Kendi departmanını görür, tanımlı modüller |
| `staff` | Yalnızca yetki verilen ekranlar |

**Yetki Tablosu:** `role_permissions` — hangi rolün hangi modüle erişeceği
**Şube Filtresi:** Her modeldeki `branch_id` kolonuyla scope otomatik uygulanır

---

## Modüller

### Tamamlanan
| Modül | Durum |
|-------|-------|
| Admin Template kurulumu | TAMAMLANDI |
| Renk & logo & favicon | TAMAMLANDI |
| Temel altyapı (Şube, Departman, Rol, Kullanıcı) | TAMAMLANDI |

---

### ARAC MODULU — GELISTIRILIYOR

**Tablolar:** `vehicles`, `vehicle_operations`, `vehicle_maintenances`, `vehicle_insurances`

**Özellikler:**
- Araç kartı (plaka, marka, model, yıl, renk, şube)
- Giriş/Çıkış ve Göreve Gidiş/Görevden Geliş kayıtları
- Kilometre takibi (her operasyonda km girişi)
- Bakım/Servis kayıtları (tarih, işlem, tutar, sonraki bakım km)
- Sigorta & Kasko takibi (poliçe no, başlangıç, bitiş, tutar, şirket, tür)
- Yaklaşan sigorta/bakım uyarıları

---

### Bekleyen Modüller
- Kapı Giriş/Çıkış
- Misafir Giriş/Çıkış
- Demirbaş (QR + onay akışı + dinamik özellikler)
- Eşya Çıkış Formu
- QR Menü (çok dilli)
- Misafir Anket (çok dilli, QR+link)
- Personel Anket (koşullu sorular)
- Yemek İsimlik (A4 yazdırma)
- Teknik Arıza Takip (istatistikler)

---

## Geliştirme Kuralları
1. Her modül → `app/Http/Controllers/Modules/` altında kendi klasörü
2. Route'lar prefix ile gruplanır (`/araclar`, `/kapi-giris` vb.)
3. Template kütüphaneleri kullanılır (toastr, sweetalert2, datatable vb.)
4. Şube filtresi `BranchScope` trait ile otomatik uygulanır
5. Tüm validasyonlar server-side + toastr feedback

---

## Kurulum
```
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
touch database/balmy_hotels.sqlite
php artisan migrate --seed
php artisan serve
```
