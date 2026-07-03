<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/admin-config.php';
require_once 'includes/auth.php';

$db = adminDB();

// Stats
$totalOrders   = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$totalRevenue  = (float)$db->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$totalMessages = (int)$db->query("SELECT COUNT(*) FROM contacts WHERE is_read=0")->fetchColumn();
$totalProducts = 0;
try { $totalProducts = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn(); } catch(Exception $e){}

// Recent orders
$recentOrders = $db->query(
    "SELECT id, order_num, customer_name, total, status, created_at
     FROM orders ORDER BY created_at DESC LIMIT 8"
)->fetchAll();

// Recent messages
$recentMessages = $db->query(
    "SELECT id, name, subject, is_read, created_at
     FROM contacts ORDER BY created_at DESC LIMIT 5"
)->fetchAll();

$pageTitle    = 'نظرة عامة';
$currentPage  = 'dashboard';
$pageSubtitle = 'مرحباً، ' . esc($_SESSION['admin_user'] ?? 'المشرف');

$statusLabels = [
    'pending'   => ['label' => 'قيد الانتظار', 'class' => 'badge-pending'],
    'confirmed' => ['label' => 'مؤكد',         'class' => 'badge-confirmed'],
    'shipped'   => ['label' => 'تم الشحن',     'class' => 'badge-shipped'],
    'delivered' => ['label' => 'تم التسليم',   'class' => 'badge-delivered'],
    'cancelled' => ['label' => 'ملغي',          'class' => 'badge-cancelled'],
];

include 'includes/layout-start.php';
?>

<!-- Stats Grid -->
<div class="stats-grid">
  <div class="stat-card orange">
    <div class="stat-icon"><i class="fas fa-box-open"></i></div>
    <div>
      <div class="stat-value"><?= number_format($totalOrders) ?></div>
      <div class="stat-label">إجمالي الطلبات</div>
    </div>
  </div>
  <div class="stat-card yellow">
    <div class="stat-icon"><i class="fas fa-clock"></i></div>
    <div>
      <div class="stat-value"><?= number_format($pendingOrders) ?></div>
      <div class="stat-label">طلبات معلقة</div>
    </div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon"><i class="fas fa-wallet"></i></div>
    <div>
      <div class="stat-value"><?= number_format($totalRevenue) ?></div>
      <div class="stat-label">الإيرادات (EGP)</div>
    </div>
  </div>
  <div class="stat-card blue">
    <div class="stat-icon"><i class="fas fa-envelope"></i></div>
    <div>
      <div class="stat-value"><?= number_format($totalMessages) ?></div>
      <div class="stat-label">رسائل غير مقروءة</div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

  <!-- Recent Orders -->
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fas fa-box-open" style="color:var(--primary)"></i> آخر الطلبات</span>
      <a href="orders.php" class="btn btn-ghost btn-sm">عرض الكل <i class="fas fa-arrow-left"></i></a>
    </div>
    <div class="table-wrap">
      <?php if (empty($recentOrders)): ?>
        <div class="empty-state">
          <div class="empty-state-icon">📦</div>
          <div class="empty-state-title">لا توجد طلبات بعد</div>
        </div>
      <?php else: ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th>رقم الطلب</th>
              <th>العميل</th>
              <th>الإجمالي</th>
              <th>الحالة</th>
              <th>التاريخ</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentOrders as $o): ?>
              <?php $st = $statusLabels[$o['status']] ?? ['label'=>$o['status'],'class'=>'']; ?>
              <tr>
                <td><span style="font-weight:700;color:var(--primary)">#<?= esc($o['order_num']) ?></span></td>
                <td><?= esc($o['customer_name']) ?></td>
                <td style="font-weight:600"><?= number_format($o['total']) ?> EGP</td>
                <td><span class="badge <?= $st['class'] ?>"><?= $st['label'] ?></span></td>
                <td class="text-muted text-sm"><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
                <td>
                  <a href="order-view.php?id=<?= $o['id'] ?>" class="btn btn-outline btn-sm btn-icon">
                    <i class="fas fa-eye"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right Column -->
  <div>
    <!-- Quick Actions -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-bolt" style="color:var(--warning)"></i> إجراءات سريعة</span>
      </div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">
        <a href="product-form.php" class="btn btn-primary" style="justify-content:center;">
          <i class="fas fa-plus"></i> إضافة منتج جديد
        </a>
        <a href="orders.php?status=pending" class="btn btn-outline" style="justify-content:center;">
          <i class="fas fa-clock"></i> الطلبات المعلقة
          <?php if ($pendingOrders > 0): ?>
            <span class="nav-badge orange" style="margin-right:4px"><?= $pendingOrders ?></span>
          <?php endif; ?>
        </a>
        <a href="contacts.php?filter=unread" class="btn btn-outline" style="justify-content:center;">
          <i class="fas fa-envelope"></i> الرسائل غير المقروءة
          <?php if ($totalMessages > 0): ?>
            <span class="nav-badge" style="margin-right:4px"><?= $totalMessages ?></span>
          <?php endif; ?>
        </a>
      </div>
    </div>

    <!-- Recent Messages -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-envelope" style="color:var(--info)"></i> آخر الرسائل</span>
        <a href="contacts.php" class="btn btn-ghost btn-sm">الكل</a>
      </div>
      <?php if (empty($recentMessages)): ?>
        <div class="empty-state" style="padding:30px 20px;">
          <div class="empty-state-title">لا توجد رسائل</div>
        </div>
      <?php else: ?>
        <div style="padding:8px 0;">
          <?php foreach ($recentMessages as $m): ?>
            <a href="contacts.php?view=<?= $m['id'] ?>"
               style="display:flex;align-items:center;gap:10px;padding:10px 16px;text-decoration:none;color:inherit;border-bottom:1px solid var(--border-light);transition:background var(--t);"
               onmouseover="this.style.background='#FAFBFC'" onmouseout="this.style.background=''">
              <div style="width:34px;height:34px;border-radius:50%;background:var(--primary-bg);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:14px;flex-shrink:0;">
                <i class="fas fa-user"></i>
              </div>
              <div style="flex:1;min-width:0;">
                <div style="font-size:12.5px;font-weight:<?= $m['is_read'] ? '500' : '700' ?>;color:var(--text);"><?= esc($m['name']) ?></div>
                <div style="font-size:11.5px;color:var(--text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= esc($m['subject']) ?></div>
              </div>
              <?php if (!$m['is_read']): ?>
                <span style="width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0;"></span>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php include 'includes/layout-end.php'; ?>
