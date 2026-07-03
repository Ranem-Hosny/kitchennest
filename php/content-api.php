<?php
// ============================================================
//  php/content-api.php — Public site-content overrides as JSON
//  The visual editor stores per-element overrides (text/image/
//  section visibility); the site applies them via content-sync.js
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$db   = require __DIR__ . '/db-credentials.php';
$page = preg_replace('/[^a-z0-9_-]/i', '', $_GET['page'] ?? 'index');

try {
    $pdo = new PDO(
        'mysql:host='.$db['host'].';dbname='.$db['name'].';charset='.$db['charset'],
        $db['user'], $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    $st = $pdo->prepare('SELECT content_key, value, type FROM site_content WHERE page = ?');
    $st->execute([$page]);
    echo json_encode(['success' => true, 'content' => $st->fetchAll()]);
} catch (Exception $e) {
    echo json_encode(['success' => true, 'content' => []]);
}
