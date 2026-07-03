<?php
// ============================================================
//  php/settings-api.php — Public store settings as JSON
//  Lets the site read live settings (shipping fee, WhatsApp,
//  store name, socials) from the dashboard instead of config.js
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
    $s = [];
    foreach ($pdo->query('SELECT `key`, value FROM settings') as $r) {
        $s[$r['key']] = $r['value'];
    }
    echo json_encode(['success' => true, 'settings' => $s]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'settings' => []]);
}
