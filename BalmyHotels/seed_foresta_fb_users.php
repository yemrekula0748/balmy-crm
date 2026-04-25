<?php
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Hashing\BcryptHasher;

$hasher = new BcryptHasher();
$pdo = new PDO('mysql:host=192.168.7.131;port=3306;dbname=balmycrmdatabase;charset=utf8mb4', 'balmycrmusername', '8qdhZ1Rbtv1QxjlUdkjV');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$BRANCH_ID    = 2;       // Balmy Foresta
$DEPT_ID      = 6;       // F&B (Foresta)
$ROLE         = 'garson';
$FAULT_NOTIFY = 0;       // Arıza bildirimi kapalı

$users = [
    ['name' => 'CUMA ÇALIŞ',           'email' => 'fb1@balmyforesta.com',  'title' => 'BAR GARSON',                   'pass' => '482631'],
    ['name' => 'MURAT ÖZTÜRK',         'email' => 'fb2@balmyforesta.com',  'title' => 'BAR GARSON',                   'pass' => '739514'],
    ['name' => 'YELİZ SERTAKAN',       'email' => 'fb3@balmyforesta.com',  'title' => 'BAR KAPTAN 1',                 'pass' => '256890'],
    ['name' => 'HAYRİ CAN ALTINOK',    'email' => 'fb4@balmyforesta.com',  'title' => 'BAR KAPTAN 1',                 'pass' => '614273'],
    ['name' => 'HASAN KARADAĞ',        'email' => 'fb5@balmyforesta.com',  'title' => 'ASST. BAR MÜDÜRÜ',             'pass' => '893047'],
    ['name' => 'UMUTCAN KİMİŞİR',      'email' => 'fb6@balmyforesta.com',  'title' => 'BAR KAPTAN 1',                 'pass' => '371582'],
    ['name' => 'MEHMET ERAY ÖZTÜRK',   'email' => 'fb7@balmyforesta.com',  'title' => 'BARMEN 1',                     'pass' => '947263'],
    ['name' => 'DOĞUKAN DENİZ',        'email' => 'fb8@balmyforesta.com',  'title' => 'BAR GARSON',                   'pass' => '128574'],
    ['name' => 'İBRAHİM HALİL YAMAN',  'email' => 'fb9@balmyforesta.com',  'title' => 'BAR GARSON',                   'pass' => '563918'],
    ['name' => 'GÜRSEL CAN GÜNAYDIN',  'email' => 'fb10@balmyforesta.com', 'title' => 'BARMEN 1',                     'pass' => '742036'],
    ['name' => 'SEMİHA ÜNLÜ',          'email' => 'fb11@balmyforesta.com', 'title' => 'BAR GARSON',                   'pass' => '195847'],
    ['name' => 'GÖKHAN KIRMIZI',       'email' => 'fb12@balmyforesta.com', 'title' => 'BAR MÜDÜRÜ',                   'pass' => '683524'],
    ['name' => 'UMUT DURMAZ',          'email' => 'fb13@balmyforesta.com', 'title' => 'BARMEN 1',                     'pass' => '417960'],
    ['name' => 'ULAŞ YONDEM',          'email' => 'fb14@balmyforesta.com', 'title' => 'BARMEN 1',                     'pass' => '852341'],
    ['name' => 'MUSTAFA AKDOĞAN',      'email' => 'fb15@balmyforesta.com', 'title' => 'BARMEN 1',                     'pass' => '274619'],
    ['name' => 'SERHAN KAÇAK',         'email' => 'fb16@balmyforesta.com', 'title' => 'BARMEN 1',                     'pass' => '936185'],
    ['name' => 'SELİNNUR KİNA',        'email' => 'fb17@balmyforesta.com', 'title' => 'BAR GARSON',                   'pass' => '581473'],
    ['name' => 'EREN ADAK',            'email' => 'fb18@balmyforesta.com', 'title' => 'GARSON 1',                     'pass' => '748392'],
    ['name' => 'YUNUS EMRE TIRPAN',    'email' => 'fb19@balmyforesta.com', 'title' => 'GARSON 1',                     'pass' => '362715'],
    ['name' => 'BARIŞ ÖZATA',          'email' => 'fb20@balmyforesta.com', 'title' => 'BUSBOY / BUSGİRL',             'pass' => '814597'],
    ['name' => 'MEVLÜT KURT',          'email' => 'fb21@balmyforesta.com', 'title' => 'GARSON 1',                     'pass' => '593741'],
    ['name' => 'YAVUZ GÜL',            'email' => 'fb22@balmyforesta.com', 'title' => 'GARSON 1',                     'pass' => '167834'],
    ['name' => 'EMRAH FİDAN',          'email' => 'fb23@balmyforesta.com', 'title' => 'SENIOR KAPTAN',                'pass' => '945268'],
    ['name' => 'MURAT OTUK',           'email' => 'fb24@balmyforesta.com', 'title' => 'GARSON 1',                     'pass' => '328156'],
    ['name' => 'AHMET KARAHAN',        'email' => 'fb25@balmyforesta.com', 'title' => 'RESTAURANT KAPTAN 1',          'pass' => '716483'],
    ['name' => 'ÖMER MOLLAÖMEROĞLU',   'email' => 'fb26@balmyforesta.com', 'title' => 'RESTAURANT KAPTAN 1',          'pass' => '459217'],
    ['name' => 'ÖZER DOĞAN',           'email' => 'fb27@balmyforesta.com', 'title' => 'RESTAURANT KAPTAN 1',          'pass' => '871396'],
    ['name' => 'MUSTAFA ASLAN',        'email' => 'fb28@balmyforesta.com', 'title' => 'GARSON 1',                     'pass' => '234578'],
    ['name' => 'FATMA ALEYNA BAŞAK',   'email' => 'fb29@balmyforesta.com', 'title' => 'GARSON 1',                     'pass' => '618042'],
    ['name' => 'ÖKKEŞ BOY',            'email' => 'fb30@balmyforesta.com', 'title' => 'SENIOR KAPTAN',                'pass' => '793516'],
    ['name' => 'AYŞE MERAL ÖZTÜRK',   'email' => 'fb31@balmyforesta.com', 'title' => 'RESTORAN MÜDÜRÜ',              'pass' => '452873'],
    ['name' => 'MÜMİN KILIÇ',          'email' => 'fb32@balmyforesta.com', 'title' => 'GARSON 1',                     'pass' => '187364'],
    ['name' => 'MAHMUT YALÇINKAYA',    'email' => 'fb33@balmyforesta.com', 'title' => 'ASST. YİYECEK İÇECEK MÜDÜRÜ', 'pass' => '926451'],
    ['name' => 'MUSTAFA SÖKE',         'email' => 'fb34@balmyforesta.com', 'title' => 'RESTAURANT KAPTAN 2',          'pass' => '573819'],
    ['name' => 'MUHAMMED SAİN',        'email' => 'fb35@balmyforesta.com', 'title' => 'GARSON 1',                     'pass' => '341697'],
    ['name' => 'CANDAN SEÇİL YALÇIN', 'email' => 'fb36@balmyforesta.com', 'title' => 'GARSON 1',                     'pass' => '862534'],
    ['name' => 'ZEKİ BURDUR',          'email' => 'fb37@balmyforesta.com', 'title' => 'SENIOR KAPTAN',                'pass' => '715280'],
    ['name' => 'İBRAHİM ARSLAN',       'email' => 'fb38@balmyforesta.com', 'title' => 'RESTAURANT KAPTAN 1',          'pass' => '463927'],
    ['name' => 'AZİZ ÖZER',            'email' => 'fb39@balmyforesta.com', 'title' => 'F&B SUPERVISOR',               'pass' => '589174'],
    ['name' => 'MURAT BAHRİ YÜCEL',   'email' => 'fb40@balmyforesta.com', 'title' => 'YİYECEK & İÇECEK MÜDÜRÜ',     'pass' => '247361'],
    ['name' => 'İNANÇ EKMEKÇİ',       'email' => 'fb41@balmyforesta.com', 'title' => 'RESTORAN MÜDÜRÜ',              'pass' => '934815'],
    ['name' => 'CENGİZ ÇİMEN',         'email' => 'fb42@balmyforesta.com', 'title' => 'GARSON 1',                     'pass' => '671293'],
    ['name' => 'GÜLBAHAR DÜNDAR',      'email' => 'fb43@balmyforesta.com', 'title' => 'RESTAURANT KAPTAN 2',          'pass' => '385046'],
];

$stmtUser = $pdo->prepare("
    INSERT INTO users (name, email, password, role, branch_id, department_id, title, is_active, fault_notify, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, NOW(), NOW())
    ON DUPLICATE KEY UPDATE
        name=VALUES(name), password=VALUES(password), role=VALUES(role),
        branch_id=VALUES(branch_id), department_id=VALUES(department_id),
        title=VALUES(title), is_active=1, fault_notify=VALUES(fault_notify), updated_at=NOW()
");

$stmtUserId = $pdo->prepare("SELECT id FROM users WHERE email=?");

$stmtRole = $pdo->prepare("
    INSERT IGNORE INTO user_roles (user_id, role_name) VALUES (?, ?)
");

echo str_pad('ADI SOYADI', 28) . str_pad('EMAIL', 30) . str_pad('ÜNVANı', 35) . "ŞİFRE\n";
echo str_repeat('-', 100) . "\n";

foreach ($users as $u) {
    $hash = $hasher->make($u['pass']);

    $stmtUser->execute([$u['name'], $u['email'], $hash, $ROLE, $BRANCH_ID, $DEPT_ID, $u['title'], $FAULT_NOTIFY]);

    $stmtUserId->execute([$u['email']]);
    $userId = $stmtUserId->fetchColumn();

    $stmtRole->execute([$userId, $ROLE]);

    echo str_pad($u['name'], 28) . str_pad($u['email'], 30) . str_pad($u['title'], 35) . $u['pass'] . "\n";
}

echo "\nToplam " . count($users) . " kullanıcı eklendi/güncellendi.\n";
