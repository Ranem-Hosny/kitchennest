<?php
// ============================================================
//  php/collections-api.php
//  Public API — returns active "Featured Collections" as JSON
//  Consumed by js/home.js to render the collections grid
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
    echo json_encode(['success' => false, 'collections' => []]);
    exit;
}

try {
    $rows = $pdo->query(
        'SELECT id, title, image_url, link, category, item_count
         FROM collections
         WHERE is_active = 1
         ORDER BY sort_order ASC, id ASC'
    )->fetchAll();

    $collections = array_map(function ($r) {
        $cat  = $r['category'] ?: '';
        $link = $r['link'] ?: ($cat ? 'category.html?cat=' . rawurlencode($cat) : '#');
        return [
            'id'    => (int)$r['id'],
            'title' => $r['title'],
            'img'   => $r['image_url'] ?: '',
            'link'  => $link,
            'cat'   => $cat,                 // site category id — homepage counts products live
            'count' => (int)$r['item_count'], // fallback only (when no category)
        ];
    }, $rows);

    echo json_encode(['success' => true, 'collections' => $collections, 'count' => count($collections)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'collections' => [], 'error' => $e->getMessage()]);
}
