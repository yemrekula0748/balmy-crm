# Elektra'da pasif, Balmy sisteminde aktif manuel Foresta hesapları

Oluşturulma tarihi: 12 Ağustos 2026  
Kaynak: Canlı Balmy kullanıcı veritabanı + Elektra `FN_API_SICIL_LISTE` (Tenant 32904, aktif ve pasif personel sorguları)

## Özet

- Sistemde aktif, Foresta şubesine bağlı, Elektra sicil bağlantısı olmayan manuel hesap: **54**
- Elektra aktif personel kaydı: **388**
- Elektra pasif personel kaydı: **818**
- Tekil ve güçlü eşleşme: **23 hesap**
- Manuel kontrol gereken yerel kullanıcı: **3 kullanıcı**
- Pasif Elektra listesinde yeterli eşleşme bulunamayan manuel hesap: **28**

Bu rapor yalnızca inceleme içindir. Hiçbir kullanıcı hesabı değiştirilmemiş veya pasife alınmamıştır. TC numarası ve açık telefon numarası rapora dahil edilmemiştir.

## Güçlü eşleşmeler

| Sistem ID | Yerel kullanıcı | E-posta | Yerel departman | Elektra ünvan | Elektra departman | İşten çıkış |
|---:|---|---|---|---|---|---|
| 164 | Abdullah Aras | fb62@balmyforesta.com | F&B | BAR GARSON | F&B BAR | 30.06.2026 |
| 139 | Ahmet Yakan | fb48@balmyforesta.com | F&B | BAR GARSON | F&B BAR | 31.07.2026 |
| 121 | CANDAN SEÇİL YALÇIN | fb36@balmyforesta.com | F&B | GARSON 1 | F&B RESTAURANT | 09.05.2026 |
| 67 | EMİNE KARAHAN | hk17@balmyforesta.com | HK | KAT GÖREVLİSİ | KAT HİZMETLERİ | 08.05.2026 |
| 146 | Gökhan Aygün | fb55@balmyforesta.com | F&B | GARSON 1 | F&B RESTAURANT | 22.07.2026 |
| 62 | GÜLSÜM YAMAN | hk12@balmyforesta.com | HK | MEYDAN GÖREVLİSİ | KAT HİZMETLERİ | 15.05.2026 |
| 46 | Güngör YILMAZ | cost@balmyforesta.com | Muhasebe | MALİYET KONTROLÖRÜ | MUHASEBE | 19.04.2026 |
| 132 | Halime Vardı | hk32@balmyforesta.com | HK | KAT ŞEFİ | KAT HİZMETLERİ | 31.05.2026 |
| 162 | İsmail Korkut | fb60@balmyforesta.com | F&B | BAR GARSON | F&B BAR | 29.06.2026 |
| 142 | Metehan Omurca | fb51@balmyforesta.com | F&B | BAR GARSON | F&B BAR | 01.08.2026 |
| 106 | MEVLÜT KURT | fb21@balmyforesta.com | F&B | GARSON 1 | F&B RESTAURANT | 10.06.2026 |
| 155 | Münir Özbek | hk35@balmyforesta.com | HK | MEYDAN ŞEFİ | KAT HİZMETLERİ | 07.07.2026 |
| 60 | MURAT GEZİCİ | hk10@balmyforesta.com | HK | MEYDAN GÖREVLİSİ | KAT HİZMETLERİ | 30.04.2026 |
| 109 | MURAT OTUK | fb24@balmyforesta.com | F&B | GARSON 1 | F&B RESTAURANT | 29.04.2026 |
| 100 | MUSTAFA AKDOĞAN | fb15@balmyforesta.com | F&B | BARMEN 1 | F&B BAR | 31.07.2026 |
| 81 | MUSTAFA KANIK | hk31@balmyforesta.com | HK | ÜTÜCÜ | ÇAMAŞIRHANE | 03.05.2026 |
| 71 | NAZYM KAZHDAROVA | hk21@balmyforesta.com | HK | KAT GÖREVLİSİ - YABANCI | KAT HİZMETLERİ | 09.06.2026 |
| 56 | NURCAN PARPAR | hk6@balmyforesta.com | HK | KAT ŞEFİ | KAT HİZMETLERİ | 01.05.2026 |
| 163 | Ümit CİNPOLAT | fb61@balmyforesta.com | F&B | GARSON 1 | F&B RESTAURANT | 28.06.2026 |
| 52 | ÜMİTCAN ARSLAN | hk2@balmyforesta.com | HK | MEYDAN ŞEFİ | KAT HİZMETLERİ | 25.04.2026 |
| 57 | ZERİF YOLOĞLU | hk7@balmyforesta.com | HK | KAT ŞEFİ | KAT HİZMETLERİ | 21.04.2026 |
| 148 | Zhanerke Zhumagaliyeva | gr3@balmyforesta.com | Misafir İlişkileri | MİSAFİR İLİŞKİLERİ GÖREVLİSİ | GENEL MÜDÜRLÜK | 07.07.2026 |
| 58 | ZİYA KILIÇ | hk8@balmyforesta.com | HK | MEYDAN GÖREVLİSİ | KAT HİZMETLERİ | 27.04.2026 |

## Manuel kontrol gerekenler

| Sistem ID | Yerel kullanıcı | Elektra pasif kayıt | İşten çıkış | Neden |
|---:|---|---|---|---|
| 135 | Ali Karakaş | ARİF ALİ KARAKAŞ | 05.05.2026 | Ek/eksik ikinci ad nedeniyle aynı kişi olup olmadığı doğrulanmalı. |
| 96 | SEMİHA ÜNLÜ | SEMİHA ÜNLÜ | 19.06.2026 ve 03.08.2026 | Sistemde aynı isimli iki aktif manuel hesap, Elektra pasif listesinde de iki kayıt var. |
| 166 | Semiha Ünlü | SEMİHA ÜNLÜ | 19.06.2026 ve 03.08.2026 | Sistemde aynı isimli iki aktif manuel hesap, Elektra pasif listesinde de iki kayıt var. |

## Eşleştirme yöntemi

Güçlü adaylar; yerel sistemde tekil aktif manuel hesap olup Elektra pasif listesinde tekil ad-soyad eşleşmesi bulunan ve aynı ad-soyad Elektra aktif listesinde bulunmayan kayıtlardır. Birden fazla kayıt, ek/eksik ad veya aktif listede çakışma olduğunda hesap otomatik aday sayılmamış ve manuel kontrol bölümüne ayrılmıştır.
