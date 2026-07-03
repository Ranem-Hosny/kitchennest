<?php
// Fetch badge counts for sidebar
try {
    $db = adminDB();
    $pendingCount = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
    $unreadCount  = (int)$db->query("SELECT COUNT(*) FROM contacts WHERE is_read=0")->fetchColumn();
} catch (Exception $e) {
    $pendingCount = 0;
    $unreadCount  = 0;
}
$page  = $currentPage  ?? '';
$title = $pageTitle    ?? 'لوحة التحكم';
$sub   = $pageSubtitle ?? '';
$topActions = $topbarActions ?? '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title) ?> — بيت العوضي</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- ── Sidebar ──────────────────────────────────────────── -->
<aside class="sidebar" id="sidebar">

  <div class="sidebar-logo">
    <div class="sidebar-logo-icon">🏠</div>
    <div>
      <div class="sidebar-logo-name">بيت العوضي</div>
      <div class="sidebar-logo-sub">لوحة التحكم</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section-label">الرئيسية</div>

    <a href="index.php" class="nav-item <?= $page === 'dashboard' ? 'active' : '' ?>">
      <span class="nav-icon"><i class="fas fa-chart-pie"></i></span>
      نظرة عامة
    </a>

    <a href="editor.php" class="nav-item <?= $page === 'editor' ? 'active' : '' ?>">
      <span class="nav-icon"><i class="fas fa-wand-magic-sparkles"></i></span>
      محرر الموقع
    </a>

    <a href="orders.php" class="nav-item <?= $page === 'orders' ? 'active' : '' ?>">
      <span class="nav-icon"><i class="fas fa-box-open"></i></span>
      الطلبات
      <span class="nav-badge orange" id="ordersBadge" style="display:<?= $pendingCount > 0 ? 'inline-flex' : 'none' ?>"><?= $pendingCount ?></span>
    </a>

    <a href="products.php" class="nav-item <?= $page === 'products' ? 'active' : '' ?>">
      <span class="nav-icon"><i class="fas fa-shopping-bag"></i></span>
      المنتجات
    </a>

    <a href="categories.php" class="nav-item <?= $page === 'categories' ? 'active' : '' ?>">
      <span class="nav-icon"><i class="fas fa-layer-group"></i></span>
      التصنيفات
    </a>

    <a href="offers.php" class="nav-item <?= $page === 'offers' ? 'active' : '' ?>">
      <span class="nav-icon"><i class="fas fa-fire"></i></span>
      العروض
    </a>

    <a href="collections.php" class="nav-item <?= $page === 'collections' ? 'active' : '' ?>">
      <span class="nav-icon"><i class="fas fa-th-large"></i></span>
      المجموعات المختارة
    </a>

    <a href="contacts.php" class="nav-item <?= $page === 'contacts' ? 'active' : '' ?>">
      <span class="nav-icon"><i class="fas fa-envelope"></i></span>
      الرسائل
      <span class="nav-badge" id="messagesBadge" style="display:<?= $unreadCount > 0 ? 'inline-flex' : 'none' ?>"><?= $unreadCount ?></span>
    </a>

    <div class="sidebar-section-label">إعدادات</div>

    <a href="settings.php" class="nav-item <?= $page === 'settings' ? 'active' : '' ?>">
      <span class="nav-icon"><i class="fas fa-cog"></i></span>
      إعدادات المتجر
    </a>

    <a href="../index.html" target="_blank" class="nav-item">
      <span class="nav-icon"><i class="fas fa-external-link-alt"></i></span>
      عرض الموقع
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="logout.php" class="nav-item" style="color:#F87171;">
      <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
      تسجيل الخروج
    </a>
  </div>

</aside>

<!-- ── Main Wrapper ──────────────────────────────────────── -->
<div class="main-wrapper">

  <header class="topbar">
    <button class="sidebar-toggle" onclick="toggleSidebar()">
      <i class="fas fa-bars"></i>
    </button>
    <div class="topbar-title">
      <?= esc($title) ?>
      <?php if ($sub): ?>
        <div class="topbar-subtitle"><?= esc($sub) ?></div>
      <?php endif; ?>
    </div>
    <div class="topbar-actions">
      <?= $topActions ?>
    </div>
  </header>

  <main class="page-content">
