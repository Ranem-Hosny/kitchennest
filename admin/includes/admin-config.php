<?php
// Admin database config — does NOT set JSON headers (unlike php/config.php)
define('ADMIN_DB_HOST',    'localhost');
define('ADMIN_DB_NAME',    'kitchennest_db');
define('ADMIN_DB_USER',    'root');
define('ADMIN_DB_PASS',    '');
define('ADMIN_DB_CHARSET', 'utf8mb4');

// Product categories are stored in the `categories` table (see admin/categories.php)
function getCategoryList(): array {
    static $cats = null;
    if ($cats !== null) return $cats;
    $cats = [];
    try {
        $rows = adminDB()->query('SELECT slug, name FROM categories ORDER BY sort_order, name')->fetchAll();
        foreach ($rows as $r) $cats[$r['slug']] = $r['name'];
    } catch (Exception $e) {
        // categories table not ready yet
    }
    return $cats;
}

function getCategories(): array {
    static $rows = null;
    if ($rows !== null) return $rows;
    try {
        $rows = adminDB()->query('SELECT * FROM categories ORDER BY sort_order, name')->fetchAll();
    } catch (Exception $e) {
        $rows = [];
    }
    return $rows;
}

function adminDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    $dsn = 'mysql:host=' . ADMIN_DB_HOST . ';dbname=' . ADMIN_DB_NAME . ';charset=' . ADMIN_DB_CHARSET;
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, ADMIN_DB_USER, ADMIN_DB_PASS, $opts);
    } catch (PDOException $e) {
        die('<div style="font-family:Cairo,sans-serif;text-align:center;padding:80px;color:#DC2626;direction:rtl;">
              <h2>❌ خطأ في الاتصال بقاعدة البيانات</h2>
              <p style="margin-top:12px;color:#555;">' . htmlspecialchars($e->getMessage()) . '</p>
              <p style="margin-top:8px;font-size:13px;color:#888;">تأكد من تشغيل قاعدة البيانات وصحة بيانات الاتصال في admin/includes/admin-config.php</p>
             </div>');
    }
    return $pdo;
}

function esc(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

// The site pages (data.js) use slightly different category ids than the
// admin `categories` table for a few slugs. Keep this in sync with
// CATEGORY_DB_TO_SITE in js/products-sync.js.
function dbCatToSite(string $slug): string {
    static $map = [
        'spoons'     => 'spoons-forks',
        'dinnersets' => 'dinner-sets',
        'tools'      => 'kitchen-tools',
    ];
    return $map[$slug] ?? $slug;
}

function getSettingVal(string $key, string $default = ''): string {
    try {
        $db = adminDB();
        $s  = $db->prepare('SELECT value FROM settings WHERE `key` = ?');
        $s->execute([$key]);
        $row = $s->fetch();
        return $row ? $row['value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}
