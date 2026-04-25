<?php
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Hashing\BcryptHasher;

$hasher = new BcryptHasher();
$pdo = new PDO('mysql:host=192.168.7.131;port=3306;dbname=balmycrmdatabase;charset=utf8mb4', 'balmycrmusername', '8qdhZ1Rbtv1QxjlUdkjV');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$BRANCH_ID = 2;  // Balmy Foresta
$DEPT_ID   = 4;  // HK (Foresta)
$ROLE      = 'teknik_ariza_personeli';

$users = [
    ['name' => 'ARZU AHISKALI',       'email' => 'hk1@balmyforesta.com',  'title' => 'ÇAMAŞIRHANE ŞEFİ'],
    ['name' => 'ÜMİTCAN ARSLAN',      'email' => 'hk2@balmyforesta.com',  'title' => 'MEYDAN ŞEFİ'],
    ['name' => 'FATİH KALAY',         'email' => 'hk3@balmyforesta.com',  'title' => 'MEYDAN ŞEFİ'],
    ['name' => 'EMİNE SAYAR',         'email' => 'hk4@balmyforesta.com',  'title' => 'MEYDAN ŞEFİ'],
    ['name' => 'SEVAL ÖZER',          'email' => 'hk5@balmyforesta.com',  'title' => 'KAT ŞEFİ'],
    ['name' => 'NURCAN PARPAR',       'email' => 'hk6@balmyforesta.com',  'title' => 'KAT ŞEFİ'],
    ['name' => 'ZERİF YOLOĞLU',       'email' => 'hk7@balmyforesta.com',  'title' => 'KAT ŞEFİ'],
    ['name' => 'ZİYA KILIÇ',          'email' => 'hk8@balmyforesta.com',  'title' => 'MEYDANCI'],
    ['name' => 'MURAT GÖNCÜ',         'email' => 'hk9@balmyforesta.com',  'title' => 'MEYDANCI'],
    ['name' => 'MURAT GEZİCİ',        'email' => 'hk10@balmyforesta.com', 'title' => 'MEYDANCI'],
    ['name' => 'SONER CAN',           'email' => 'hk11@balmyforesta.com', 'title' => 'MEYDANCI'],
    ['name' => 'GÜLSÜM YAMAN',        'email' => 'hk12@balmyforesta.com', 'title' => 'MEYDANCI'],
    ['name' => 'ALİ BÜYÜKTOSUN',      'email' => 'hk13@balmyforesta.com', 'title' => 'MEYDANCI'],
    ['name' => 'DERYA SULAR',         'email' => 'hk14@balmyforesta.com', 'title' => 'MEYDANCI'],
    ['name' => 'KADRİYE ÇETİNKAYA',  'email' => 'hk15@balmyforesta.com', 'title' => 'KAT GÖREVLİSİ'],
    ['name' => 'EMSAL BERBER',        'email' => 'hk16@balmyforesta.com', 'title' => 'KAT GÖREVLİSİ'],
    ['name' => 'EMİNE KARAHAN',       'email' => 'hk17@balmyforesta.com', 'title' => 'KAT GÖREVLİSİ'],
    ['name' => 'EMETİ ÇEVİK',         'email' => 'hk18@balmyforesta.com', 'title' => 'KAT GÖREVLİSİ'],
    ['name' => 'İMREN UYAROĞLU',      'email' => 'hk19@balmyforesta.com', 'title' => 'KAT GÖREVLİSİ'],
    ['name' => 'NURELLA IMANOLİEVA',  'email' => 'hk20@balmyforesta.com', 'title' => 'KAT GÖREVLİSİ'],
    ['name' => 'NAZYM KAZHDAROVA',    'email' => 'hk21@balmyforesta.com', 'title' => 'KAT GÖREVLİSİ'],
    ['name' => 'TÜRKAN ASRIK',        'email' => 'hk22@balmyforesta.com', 'title' => 'KAT GÖREVLİSİ'],
    ['name' => 'HATİCE AYIK',         'email' => 'hk23@balmyforesta.com', 'title' => 'KAT GÖREVLİSİ'],
    ['name' => 'ALİYE KILIÇ',         'email' => 'hk24@balmyforesta.com', 'title' => 'KAT GÖREVLİSİ'],
    ['name' => 'YASİN ARSLAN',        'email' => 'hk25@balmyforesta.com', 'title' => 'MİNİBAR ŞEFİ'],
    ['name' => 'NECMETTİN',           'email' => 'hk26@balmyforesta.com', 'title' => 'MİNİBARCI'],
    ['name' => 'İSMAİL ÇETİNKAYA',   'email' => 'hk27@balmyforesta.com', 'title' => 'ÜTÜCÜ'],
    ['name' => 'MESUT ÇAPAR',         'email' => 'hk28@balmyforesta.com', 'title' => 'Ç.HANE PERSONELİ'],
    ['name' => 'ERDAL TÜLEK',         'email' => 'hk29@balmyforesta.com', 'title' => 'Ç.HANE PERSONELİ'],
    ['name' => 'HATİCE İŞLER',        'email' => 'hk30@balmyforesta.com', 'title' => 'TERZİ'],
    ['name' => 'MUSTAFA KANIK',       'email' => 'hk31@balmyforesta.com', 'title' => 'ÜTÜCÜ'],
];

$stmtUser = $pdo->prepare("
    INSERT INTO users (name, email, password, role, branch_id, department_id, title, is_active, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
    ON DUPLICATE KEY UPDATE
        name=VALUES(name), password=VALUES(password), role=VALUES(role),
        branch_id=VALUES(branch_id), department_id=VALUES(department_id),
        title=VALUES(title), is_active=1, updated_at=NOW()
");

$stmtUserId = $pdo->prepare("SELECT id FROM users WHERE email=?");

$stmtRole = $pdo->prepare("
    INSERT IGNORE INTO user_roles (user_id, role_name) VALUES (?, ?)
");

echo str_pad('ADI SOYADI', 25) . str_pad('EMAIL', 28) . "ŞİFRE\n";
echo str_repeat('-', 65) . "\n";

foreach ($users as $u) {
    $plain = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    $hash  = $hasher->make($plain);

    $stmtUser->execute([$u['name'], $u['email'], $hash, $ROLE, $BRANCH_ID, $DEPT_ID, $u['title']]);

    $stmtUserId->execute([$u['email']]);
    $userId = $stmtUserId->fetchColumn();

    $stmtRole->execute([$userId, $ROLE]);

    echo str_pad($u['name'], 25) . str_pad($u['email'], 28) . $plain . "\n";
}

echo "\nToplam " . count($users) . " kullanıcı eklendi.\n";
