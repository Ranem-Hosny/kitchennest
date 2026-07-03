<?php
// ============================================================
//  php/categories-api.php — Public categories as JSON
//  Lets the site show categories added/edited in the dashboard
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$db = require __DIR__ . '/db-credentials.php';
try {
    $pdo = new PDO(
        'mysql:host='.$db['host'].';dbname='.$db['name'].';charset='.$db['charset'],
        $db['user'], $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    $rows = $pdo->query('SELECT slug, name, icon, color, image_url FROM categories ORDER BY sort_order, name')->fetchAll();
    echo json_encode(['success' => true, 'categories' => $rows]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'categories' => []]);
}
