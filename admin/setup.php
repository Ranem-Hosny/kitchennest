<?php
// ============================================================
//  admin/setup.php — One-time database setup
//  Visit this page once to create all admin tables
//  DELETE or PROTECT this file after setup!
// ============================================================
require_once 'includes/admin-config.php';

$messages = [];
$errors   = [];

function runSQL(PDO $db, string $sql, string $label): string {
    try {
        $db->exec($sql);
        return "<li style='color:#15803D'>✅ $label</li>";
    } catch (PDOException $e) {
        return "<li style='color:#B91C1C'>❌ $label: " . htmlspecialchars($e->getMessage()) . "</li>";
    }
}

$db = adminDB();
$log = '';

// ── admin_users table ───────────────────────────────────────
$log .= runSQL($db, "
    CREATE TABLE IF NOT EXISTS `admin_users` (
      `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
      `username`      VARCHAR(50)     NOT NULL UNIQUE,
      `password_hash` VARCHAR(255)    NOT NULL,
      `created_at`    DATETIME        NOT NULL DEFAULT NOW(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", "جدول admin_users");

// ── products table ──────────────────────────────────────────
$log .= runSQL($db, "
    CREATE TABLE IF NOT EXISTS `products` (
      `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
      `name`          VARCHAR(255)    NOT NULL,
      `slug`          VARCHAR(255)    DEFAULT NULL,
      `category`      VARCHAR(50)     NOT NULL,
      `subcategory`   VARCHAR(100)    DEFAULT NULL,
      `price`         DECIMAL(10,2)   NOT NULL,
      `old_price`     DECIMAL(10,2)   DEFAULT NULL,
      `discount`      TINYINT         NOT NULL DEFAULT 0,
      `short_desc`    TEXT            DEFAULT NULL,
      `description`   TEXT            DEFAULT NULL,
      `material`      VARCHAR(100)    DEFAULT NULL,
      `size`          VARCHAR(50)     DEFAULT NULL,
      `color`         VARCHAR(50)     DEFAULT NULL,
      `pieces`        TINYINT         NOT NULL DEFAULT 1,
      `image_url`     VARCHAR(500)    DEFAULT NULL,
      `image_url2`    VARCHAR(500)    DEFAULT NULL,
      `image_url3`    VARCHAR(500)    DEFAULT NULL,
      `in_stock`      TINYINT(1)      NOT NULL DEFAULT 1,
      `is_new`        TINYINT(1)      NOT NULL DEFAULT 0,
      `is_bestseller` TINYINT(1)      NOT NULL DEFAULT 0,
      `is_featured`   TINYINT(1)      NOT NULL DEFAULT 0,
      `is_offer`      TINYINT(1)      NOT NULL DEFAULT 0,
      `sort_order`    INT             NOT NULL DEFAULT 0,
      `created_at`    DATETIME        NOT NULL DEFAULT NOW(),
      `updated_at`    DATETIME        DEFAULT NULL ON UPDATE NOW(),
      PRIMARY KEY (`id`),
      KEY `idx_category`  (`category`),
      KEY `idx_in_stock`  (`in_stock`),
      KEY `idx_is_new`    (`is_new`),
      KEY `idx_is_bestseller` (`is_bestseller`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", "جدول products");

// ── settings table ──────────────────────────────────────────
$log .= runSQL($db, "
    CREATE TABLE IF NOT EXISTS `settings` (
      `key`   VARCHAR(100)  NOT NULL,
      `value` TEXT          DEFAULT NULL,
      PRIMARY KEY (`key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", "جدول settings");

// ── collections table (المجموعات المختارة) ──────────────────
$log .= runSQL($db, "
    CREATE TABLE IF NOT EXISTS `collections` (
      `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
      `title`      VARCHAR(150)  NOT NULL,
      `image_url`  VARCHAR(500)  DEFAULT NULL,
      `link`       VARCHAR(255)  DEFAULT NULL,
      `category`   VARCHAR(50)   DEFAULT NULL,
      `item_count` INT           NOT NULL DEFAULT 0,
      `sort_order` INT           NOT NULL DEFAULT 0,
      `is_active`  TINYINT(1)    NOT NULL DEFAULT 1,
      `created_at` DATETIME      NOT NULL DEFAULT NOW(),
      PRIMARY KEY (`id`),
      KEY `idx_active_sort` (`is_active`, `sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", "جدول collections");

// ── site_content table (المحرر المرئي) ─────────────────────
$log .= runSQL($db, "
    CREATE TABLE IF NOT EXISTS `site_content` (
      `content_key` VARCHAR(255) NOT NULL,
      `page`        VARCHAR(60)  NOT NULL DEFAULT 'index',
      `value`       LONGTEXT     DEFAULT NULL,
      `type`        VARCHAR(20)  NOT NULL DEFAULT 'text',
      `updated_at`  DATETIME     NOT NULL DEFAULT NOW() ON UPDATE NOW(),
      PRIMARY KEY (`page`, `content_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", "جدول site_content");

// ── Default admin user ──────────────────────────────────────
$exists = $db->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
if (!$exists) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $db->prepare("INSERT INTO admin_users (username, password_hash) VALUES (?, ?)")
       ->execute(['admin', $hash]);
    $log .= "<li style='color:#15803D'>✅ تم إنشاء المستخدم الافتراضي: <strong>admin</strong> / <strong>admin123</strong></li>";
} else {
    $log .= "<li style='color:#555'>ℹ️ المستخدم الإداري موجود مسبقاً</li>";
}

// ── Default settings ─────────────────────────────────────────
$defaultSettings = [
    'store_name'       => 'بيت العوضي',
    'store_email'      => 'info@beitAlawady.com',
    'store_phone'      => '+20 155 167 7016',
    'store_address'    => 'القاهرة، مصر',
    'whatsapp_number'  => '201551677016',
    'instapay_number'  => '01551677016',
    'shipping_fee'     => '50',
    'social_instagram' => '#',
    'social_facebook'  => '#',
    'social_tiktok'    => '#',
];

$upsert = $db->prepare("INSERT IGNORE INTO settings (`key`, value) VALUES (?,?)");
foreach ($defaultSettings as $k => $v) {
    $upsert->execute([$k, $v]);
}
$log .= "<li style='color:#15803D'>✅ الإعدادات الافتراضية</li>";

$redir = $_GET['redir'] ?? '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>إعداد قاعدة البيانات — بيت العوضي</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="login-body" style="align-items:flex-start;padding:40px 20px;">
  <div style="max-width:560px;width:100%;margin:0 auto;">
    <div style="text-align:center;margin-bottom:28px;">
      <div style="width:60px;height:60px;background:linear-gradient(135deg,#FF6B00,#FF8C2B);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 12px;">🏠</div>
      <div style="font-size:20px;font-weight:800;color:#fff;">بيت العوضي</div>
      <div style="font-size:13px;color:#94A3B8;margin-top:4px;">إعداد قاعدة البيانات</div>
    </div>

    <div style="background:#fff;border-radius:16px;padding:28px;box-shadow:0 24px 64px rgba(0,0,0,0.3);">
      <h2 style="font-size:16px;font-weight:700;margin-bottom:16px;color:#111827;">نتيجة الإعداد</h2>
      <ul style="list-style:none;display:flex;flex-direction:column;gap:8px;font-size:13.5px;margin-bottom:20px;">
        <?= $log ?>
      </ul>

      <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:8px;padding:14px;margin-bottom:16px;font-size:13px;color:#15803D;">
        ✅ اكتمل الإعداد! يمكنك الآن تسجيل الدخول.
      </div>

      <div style="background:#FFFBEB;border:1px solid #FDE68A;border-radius:8px;padding:14px;font-size:12.5px;color:#92400E;margin-bottom:20px;">
        ⚠️ <strong>مهم:</strong> احذف هذا الملف أو امنع الوصول إليه بعد الإعداد لأسباب أمنية.
        <br>الاسم: <code style="background:#FEF3C7;padding:2px 6px;border-radius:4px;">admin/setup.php</code>
      </div>

      <a href="login.php" style="display:block;text-align:center;background:#FF6B00;color:#fff;padding:12px;border-radius:8px;font-size:15px;font-weight:700;text-decoration:none;">
        الذهاب لتسجيل الدخول →
      </a>
    </div>
  </div>
</body>
</html>
