<?php
// ============================================================
//  php/products-api.php
//  Public API — returns admin-added products as JSON
//  Used by the frontend to merge DB products with data.js
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Shared DB credentials (php/db-credentials.php)
$__db = require __DIR__ . '/db-credentials.php';

try {
    $pdo = new PDO(
        'mysql:host='.$__db['host'].';dbname='.$__db['name'].';charset='.$__db['charset'],
        $__db['user'], $__db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (Exception $e) {
    echo json_encode(['success' => false, 'products' => []]);
    exit;
}

$category = $_GET['category'] ?? '';
$where    = '1=1';
$params   = [];

if ($category) {
    $where .= ' AND category = ?';
    $params[] = $category;
}
if (isset($_GET['inStock']) && $_GET['inStock'] === '1') {
    $where .= ' AND in_stock = 1';
}

$featured   = isset($_GET['featured'])   && $_GET['featured']   === '1';
$bestseller = isset($_GET['bestseller']) && $_GET['bestseller'] === '1';
$is_new     = isset($_GET['new'])        && $_GET['new']        === '1';
$is_offer   = isset($_GET['offer'])      && $_GET['offer']      === '1';

if ($featured)   { $where .= ' AND is_featured=1'; }
if ($bestseller) { $where .= ' AND is_bestseller=1'; }
if ($is_new)     { $where .= ' AND is_new=1'; }
if ($is_offer)   { $where .= ' AND is_offer=1'; }

$limit  = min(100, max(1, (int)($_GET['limit'] ?? 100)));
$offset = max(0, (int)($_GET['offset'] ?? 0));

try {
    $st = $pdo->prepare("SELECT * FROM products WHERE $where ORDER BY sort_order ASC, created_at DESC LIMIT $limit OFFSET $offset");
    $st->execute($params);
    $products = $st->fetchAll();

    // Convert to frontend format (matching data.js structure)
    $formatted = array_map(function($p) {
        return [
            'id'          => (int)$p['id'],
            'name'        => $p['name'],
            'slug'        => $p['slug'] ?: 'product-' . $p['id'],
            'category'    => $p['category'],
            'subcategory' => $p['subcategory'] ?: '',
            'price'       => (float)$p['price'],
            'oldPrice'    => $p['old_price'] ? (float)$p['old_price'] : null,
            'discount'    => (int)$p['discount'],
            'shortDesc'   => $p['short_desc'] ?: '',
            'description' => $p['description'] ?: '',
            'material'    => $p['material'] ?: '',
            'size'        => $p['size'] ?: '',
            'color'       => $p['color'] ?: '',
            'pieces'      => (int)$p['pieces'],
            'image'       => $p['image_url'] ?: '',
            'images'      => array_values(array_filter([$p['image_url'] ?? '', $p['image_url2'] ?? '', $p['image_url3'] ?? ''])),
            'inStock'     => (bool)$p['in_stock'],
            'stockQty'    => isset($p['stock_qty']) ? (int)$p['stock_qty'] : null,
            'collectionId'=> !empty($p['collection_id']) ? (int)$p['collection_id'] : null,
            'isNew'       => (bool)$p['is_new'],
            'isBestSeller'=> (bool)$p['is_bestseller'],
            'isFeatured'  => (bool)$p['is_featured'],
            'isOffer'     => (bool)$p['is_offer'],
            'rating'      => 4.5,
            'reviewCount' => 0,
            'features'    => [],
            'specs'       => [],
            'reviews'     => [],
            'source'      => 'db',
        ];
    }, $products);

    echo json_encode(['success' => true, 'products' => $formatted, 'count' => count($formatted)]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'products' => [], 'error' => $e->getMessage()]);
}
