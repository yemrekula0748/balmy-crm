<?php

declare(strict_types=1);

$dsn = 'mysql:host=192.168.7.131;port=3306;dbname=balmycrmdatabase;charset=utf8mb4';
$username = 'balmycrmusername';
$password = '8qdhZ1Rbtv1QxjlUdkjV';

$sourceMenuId = 18;
$targetMenuIds = [19, 15, 14, 13, 12, 11, 10, 8];
$run = in_array('--run', $argv ?? [], true);

$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function decodeJson(?string $value, $default = null)
{
    if ($value === null || $value === '') {
        return $default;
    }

    $decoded = json_decode($value, true);

    return json_last_error() === JSON_ERROR_NONE ? $decoded : $default;
}

function encodeJson($value): ?string
{
    if ($value === null) {
        return null;
    }

    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function getMenu(PDO $pdo, int $menuId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM qr_menus WHERE id = ?');
    $stmt->execute([$menuId]);

    return $stmt->fetch() ?: null;
}

function getSourceCategories(PDO $pdo, int $menuId): array
{
    $stmt = $pdo->prepare(
        'SELECT *
         FROM qr_menu_categories
         WHERE qr_menu_id = ? AND is_active = 1
         ORDER BY sort_order, id'
    );
    $stmt->execute([$menuId]);
    $categories = $stmt->fetchAll();

    $itemStmt = $pdo->prepare(
        'SELECT *
         FROM qr_menu_items
         WHERE category_id = ? AND is_active = 1
         ORDER BY sort_order, id'
    );

    $result = [];
    foreach ($categories as $category) {
        $itemStmt->execute([$category['id']]);
        $items = $itemStmt->fetchAll();

        if ($items === []) {
            continue;
        }

        $category['title_decoded'] = decodeJson($category['title'], []);
        $category['items'] = array_map(static function (array $item): array {
            $item['title_decoded'] = decodeJson($item['title'], []);
            $item['description_decoded'] = decodeJson($item['description']);
            $item['badges_decoded'] = decodeJson($item['badges']);

            return $item;
        }, $items);

        $result[] = $category;
    }

    return $result;
}

function findOrCreateTargetDrinkCategory(PDO $pdo, array $menu, array $sourceMenuTitle, bool $run): array
{
    $stmt = $pdo->prepare(
        "SELECT *
         FROM qr_menu_categories
         WHERE qr_menu_id = ?
           AND (
               JSON_UNQUOTE(JSON_EXTRACT(title, '$.tr')) = 'İçecekler'
               OR JSON_UNQUOTE(JSON_EXTRACT(title, '$.en')) = 'Beverages'
               OR JSON_UNQUOTE(JSON_EXTRACT(title, '$.de')) = 'Getränke'
           )
         ORDER BY sort_order, id
         LIMIT 1"
    );
    $stmt->execute([$menu['id']]);
    $existing = $stmt->fetch();

    if ($existing) {
        $existing['created_now'] = false;

        return $existing;
    }

    $sortStmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), -1) FROM qr_menu_categories WHERE qr_menu_id = ?');
    $sortStmt->execute([$menu['id']]);
    $nextSortOrder = ((int) $sortStmt->fetchColumn()) + 1;

    $newCategory = [
        'id' => null,
        'qr_menu_id' => (int) $menu['id'],
        'title' => encodeJson($sourceMenuTitle),
        'description' => null,
        'sub_headings' => null,
        'icon' => null,
        'image' => null,
        'sort_order' => $nextSortOrder,
        'is_active' => 1,
        'created_now' => true,
    ];

    if ($run) {
        $insert = $pdo->prepare(
            'INSERT INTO qr_menu_categories
                (qr_menu_id, title, description, sub_headings, icon, image, sort_order, is_active, created_at, updated_at)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $insert->execute([
            $newCategory['qr_menu_id'],
            $newCategory['title'],
            $newCategory['description'],
            $newCategory['sub_headings'],
            $newCategory['icon'],
            $newCategory['image'],
            $newCategory['sort_order'],
            $newCategory['is_active'],
        ]);
        $newCategory['id'] = (int) $pdo->lastInsertId();
    }

    return $newCategory;
}

$sourceMenu = getMenu($pdo, $sourceMenuId);
if (!$sourceMenu) {
    fwrite(STDERR, "Kaynak menu bulunamadi: {$sourceMenuId}\n");
    exit(1);
}

$sourceCategories = getSourceCategories($pdo, $sourceMenuId);
if ($sourceCategories === []) {
    fwrite(STDERR, "Kaynak menude aktif kategori/urun bulunamadi.\n");
    exit(1);
}

$sourceMenuTitle = decodeJson($sourceMenu['title'], ['tr' => 'İçecekler']);
$subHeadings = array_map(static fn (array $category) => $category['title_decoded'], $sourceCategories);

$sourceItems = [];
$globalSortOrder = 0;
foreach ($sourceCategories as $category) {
    foreach ($category['items'] as $item) {
        $sourceItems[] = [
            'food_product_id' => $item['food_product_id'],
            'title' => $item['title_decoded'],
            'description' => $item['description_decoded'],
            'price' => $item['price'],
            'price_override' => $item['price_override'],
            'price_glass' => $item['price_glass'],
            'price_bottle' => $item['price_bottle'],
            'cl_glass' => $item['cl_glass'],
            'cl_bottle' => $item['cl_bottle'],
            'image' => $item['image'],
            'is_active' => (int) $item['is_active'],
            'is_featured' => (int) $item['is_featured'],
            'badges' => $item['badges_decoded'],
            'sub_heading' => $category['title_decoded'],
            'sort_order' => $globalSortOrder++,
        ];
    }
}

echo "Kaynak menu #{$sourceMenuId}: " . ($sourceMenuTitle['tr'] ?? 'İçecekler') . PHP_EOL;
echo 'Kaynak kategori sayisi: ' . count($sourceCategories) . PHP_EOL;
echo 'Kaynak urun sayisi: ' . count($sourceItems) . PHP_EOL . PHP_EOL;

if (!$run) {
    echo "DRY RUN modundasin. Gercek islem icin: php tmp_sync_menu18_drinks_to_targets.php --run" . PHP_EOL . PHP_EOL;
}

$deleteStmt = $pdo->prepare('DELETE FROM qr_menu_items WHERE category_id = ?');
$updateCategoryStmt = $pdo->prepare(
    'UPDATE qr_menu_categories
     SET title = ?, sub_headings = ?, is_active = 1, updated_at = NOW()
     WHERE id = ?'
);
$insertItemStmt = $pdo->prepare(
    'INSERT INTO qr_menu_items
        (category_id, food_product_id, title, description, price, price_override, price_glass, price_bottle, cl_glass, cl_bottle, image, is_active, is_featured, badges, sub_heading, sort_order, created_at, updated_at)
     VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
);
$countItemsStmt = $pdo->prepare('SELECT COUNT(*) FROM qr_menu_items WHERE category_id = ?');

try {
    $pdo->beginTransaction();

    foreach ($targetMenuIds as $targetMenuId) {
        $targetMenu = getMenu($pdo, $targetMenuId);
        if (!$targetMenu) {
            throw new RuntimeException("Hedef menu bulunamadi: {$targetMenuId}");
        }

        $targetCategory = findOrCreateTargetDrinkCategory($pdo, $targetMenu, $sourceMenuTitle, $run);
        $targetCategoryId = $targetCategory['id'];

        $existingCount = 0;
        if ($targetCategoryId) {
            $countItemsStmt->execute([$targetCategoryId]);
            $existingCount = (int) $countItemsStmt->fetchColumn();
        }

        echo "Menu #{$targetMenuId} ({$targetMenu['name']}) -> ";
        echo $targetCategory['created_now']
            ? 'yeni İçecekler kategorisi olusturulacak'
            : "kategori #{$targetCategoryId} guncellenecek";
        echo ", mevcut urun: {$existingCount}, yeni urun: " . count($sourceItems) . PHP_EOL;

        if (!$run) {
            continue;
        }

        $updateCategoryStmt->execute([
            encodeJson($sourceMenuTitle),
            encodeJson($subHeadings),
            $targetCategoryId,
        ]);

        $deleteStmt->execute([$targetCategoryId]);

        foreach ($sourceItems as $item) {
            $insertItemStmt->execute([
                $targetCategoryId,
                $item['food_product_id'],
                encodeJson($item['title']),
                encodeJson($item['description']),
                $item['price'],
                $item['price_override'],
                $item['price_glass'],
                $item['price_bottle'],
                $item['cl_glass'],
                $item['cl_bottle'],
                $item['image'],
                $item['is_active'],
                $item['is_featured'],
                encodeJson($item['badges']),
                encodeJson($item['sub_heading']),
                $item['sort_order'],
            ]);
        }
    }

    if ($run) {
        $pdo->commit();
        echo PHP_EOL . "Senkronizasyon tamamlandi." . PHP_EOL;
    } else {
        $pdo->rollBack();
        echo PHP_EOL . "Dry run tamamlandi, veritabani degisikligi yapilmadi." . PHP_EOL;
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, 'HATA: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
