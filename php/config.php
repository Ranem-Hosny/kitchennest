<?php
// ============================================================
//  php/config.php  —  Database & store configuration
//  CHANGE THESE VALUES BEFORE GOING LIVE
// ============================================================

// ── Database credentials ────────────────────────────────────
// القيم تُقرأ من ملف واحد مشترك: php/db-credentials.php
$__db = require __DIR__ . '/db-credentials.php';
define('DB_HOST', $__db['host']);
define('DB_NAME', $__db['name']);
define('DB_USER', $__db['user']);
define('DB_PASS', $__db['pass']);
define('DB_CHARSET', $__db['charset']);

// ── Store WhatsApp number ───────────────────────────────────
define('WHATSAPP_NUMBER', '201234567890'); // ← CHANGE THIS

// ── InstaPay number ─────────────────────────────────────────
define('INSTAPAY_NUMBER', '01234567890'); // ← CHANGE THIS

// ── Shipping fee (EGP) ──────────────────────────────────────
define('SHIPPING_FEE', 50); // ← CHANGE THIS

// ── Create PDO connection ───────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
    } catch (PDOException $e) {
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
    }
    return $pdo;
}

// ── CORS / JSON helpers ─────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

function jsonOk(array $data = []): void {
    echo json_encode(['success' => true, ...$data]);
    exit;
}
function jsonErr(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}
function sanitize(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}
