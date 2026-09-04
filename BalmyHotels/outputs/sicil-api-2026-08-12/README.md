# Sicil API incelemesi — 12.08.2026

Kaynak fonksiyon: `FN_API_SICIL_LISTE`

## Dosyalar

- `personel_tumu.json`: `CALISIYOR=2`, 1206 kayıt
- `personel_calisanlar.json`: `CALISIYOR=1`, 388 kayıt
- `personel_calismayanlar.json`: `CALISIYOR=0`, 818 kayıt
- `inceleme_ozeti.json`: alan doluluk oranları, departman dağılımı ve teknik bulgular

## Önemli bulgular

- `TENANTID=32904`, `TENANTID=30570` ve `TENANTID=2429` istekleri aynı kaydı ve aynı SHA-256 değerini döndürdü. API anahtarı erişim kapsamını kendisi belirliyor; gönderilen `TENANTID` sonucu ayırmadı.
- Dönen 1206 kaydın tamamı `FIRMAID=3617`, `ANİA TUR.YAT.A.Ş.` firmasına ait.
- Yanıtta otel/şube kimliği bulunmuyor. `GRUP` ve `BIRIM` alanları tamamen boş olduğu için kayıtları Balmy Foresta ve Balmy Beach Resort olarak güvenilir biçimde ayırmak şu an mümkün değil.
- `CALISIYOR=1` ve `CALISIYOR=0` sonuçları çakışmıyor ve birleşimleri tam 1206 kaydı oluşturuyor.
- `AKTIF` alanındaki dağılım (`1049/157`) API filtresindeki dağılımla (`388/818`) aynı değil. Sonraki entegrasyonda çalışma durumu için `CALISIYOR` filtre sonuçları esas alınmalıdır.
- API toplam 55 alan döndürüyor. Alanlar; kimlik/sicil, iletişim, işe giriş-çıkış, departman, unvan/görev, bordro, izin, eğitim, meslek ve üst yönetici bilgilerini kapsıyor.

## Güvenlik

JSON dosyaları TC kimlik numarası, doğum tarihi ve iletişim bilgileri gibi kişisel veri içerir. Genel depoya yüklenmemeli veya yetkisiz kişilerle paylaşılmamalıdır. API anahtarı çıktı dosyalarına yazılmamıştır.
