<?php
// Admin database config — does NOT set JSON headers (unlike php/config.php)
// القيم تُقرأ من ملف واحد مشترك: php/db-credentials.php
$__db = require __DIR__ . '/../../php/db-credentials.php';
define('ADMIN_DB_HOST',    $__db['host']);
define('ADMIN_DB_NAME',    $__db['name']);
define('ADMIN_DB_USER',    $__db['user']);
define('ADMIN_DB_PASS',    $__db['pass']);
define('ADMIN_DB_CHARSET', $__db['charset']);

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

// ── Self-healing schema ─────────────────────────────────────
// Applies pending column/table additions automatically the first
// time the dashboard is opened after an update — so the store owner
// never has to run a migration script by hand. Gated by a version
// flag in `settings` so it runs its work only once.
function ensureSchema(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        $db = adminDB();
        $current = 0;
        try { $current = (int)$db->query("SELECT value FROM settings WHERE `key`='schema_version'")->fetchColumn(); } catch (Exception $e) {}
        if ($current >= 2) return;

        $alters = [
            "ALTER TABLE products   ADD COLUMN image_url2 VARCHAR(500) DEFAULT NULL",
            "ALTER TABLE products   ADD COLUMN image_url3 VARCHAR(500) DEFAULT NULL",
            "ALTER TABLE categories ADD COLUMN image_url  VARCHAR(500) DEFAULT NULL",
            "ALTER TABLE orders     ADD COLUMN stock_deducted TINYINT(1) NOT NULL DEFAULT 0",
            "ALTER TABLE collections ADD COLUMN category VARCHAR(50) DEFAULT NULL",
        ];
        foreach ($alters as $sql) { try { $db->exec($sql); } catch (Exception $e) { /* already exists — fine */ } }

        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `site_content` (
                `content_key` VARCHAR(255) NOT NULL,
                `page`        VARCHAR(60)  NOT NULL DEFAULT 'index',
                `value`       LONGTEXT     DEFAULT NULL,
                `type`        VARCHAR(20)  NOT NULL DEFAULT 'text',
                `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`page`, `content_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Exception $e) {}

        try {
            $db->prepare("INSERT INTO settings (`key`, value) VALUES ('schema_version','2')
                          ON DUPLICATE KEY UPDATE value='2'")->execute();
        } catch (Exception $e) {}
    } catch (Exception $e) { /* never block the page on migration issues */ }
}

// Run the schema check whenever the admin bootstrap is loaded.
ensureSchema();
