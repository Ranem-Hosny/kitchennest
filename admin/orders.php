<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/admin-config.php';
require_once 'includes/auth.php';

$db = adminDB();

$filterStatus = $_GET['status'] ?? '';
$search       = trim($_GET['q'] ?? '');
$page_num     = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 15;
$offset       = ($page_num - 1) * $perPage;

$where  = '1=1';
$params = [];

if ($filterStatus) {
    $where .= ' AND status = ?';
    $params[] = $filterStatus;
}
if ($search) {
    $where .= ' AND (order_num LIKE ? OR customer_name LIKE ? OR phone LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$total = (int)$db->prepare("SELECT COUNT(*) FROM orders WHERE $where")->execute($params) ?
         $db->prepare("SELECT COUNT(*) FROM orders WHERE $where")->execute($params) : 0;

$cnt = $db->prepare("SELECT COUNT(*) FROM orders WHERE $where");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pages = ceil($total / $perPage);

$st = $db->prepare("SELECT * FROM orders WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$st->execute($params);
$orders = $st->fetchAll();

$statusLabels = [
    'pending'   => ['label' => 'قيد الانتظار', 'class' => 'badge-pending'],
    'confirmed' => ['label' => 'مؤكد',         'class' => 'badge-confirmed'],
    'shipped'   => ['label' => 'تم الشحن',     'class' => 'badge-shipped'],
    'delivered' => ['label' => 'تم التسليم',   'class' => 'badge-delivered'],
    'cancelled' => ['label' => 'ملغي',          'class' => 'badge-cancelled'],
];

$pageTitle   = 'الطلبات';
$currentPage = 'orders';

// Stat counts
$counts = [];
foreach (array_keys($statusLabels) as $s) {
    $counts[$s] = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status='$s'")->fetchColumn();
}

include 'includes/layout-start.php';
?>

<div class="page-header">
  <div class="page-header-left">
    <div class="page-header-title">الطلبات</div>
    <div class="page-header-sub">إجمالي <?= number_format($total) ?> طلب</div>
  </div>
</div>

<!-- Status Tabs -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
  <a href="orders.php" class="btn btn-sm <?= !$filterStatus ? 'btn-primary' : 'btn-outline' ?>">
    الكل <span style="font-weight:400;opacity:.7">(<?= array_sum($counts) ?>)</span>
  </a>
  <?php foreach ($statusLabels as $key => $info): ?>
    <a href="orders.php?status=<?= $key ?><?= $search ? '&q='.urlencode($search) : '' ?>"
       class="btn btn-sm <?= $filterStatus === $key ? 'btn-primary' : 'btn-outline' ?>">
      <?= $info['label'] ?>
      <span style="font-weight:400;opacity:.7">(<?= $counts[$key] ?>)</span>
    </a>
  <?php endforeach; ?>
</div>

<div class="card">
  <!-- Search bar -->
  <div class="filter-bar">
    <form method="GET" style="display:flex;gap:8px;flex:1;flex-wrap:wrap;">
      <?php if ($filterStatus): ?><input type="hidden" name="status" value="<?= esc($filterStatus) ?>"><?php endif; ?>
      <input type="search" name="q" class="filter-search" placeholder="بحث برقم الطلب، الاسم، أو الهاتف…"
             value="<?= esc($search) ?>">
      <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-search"></i> بحث</button>
      <?php if ($search || $filterStatus): ?>
        <a href="orders.php" class="btn btn-ghost btn-sm"><i class="fas fa-times"></i> مسح</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="table-wrap">
    <?php if (empty($orders)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">📦</div>
        <div class="empty-state-title">لا توجد طلبات مطابقة</div>
        <div class="empty-state-sub">جرب تغيير فلتر البحث</div>
      </div>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>رقم الطلب</th>
            <th>العميل</th>
            <th>الهاتف</th>
            <th>المدينة</th>
            <th>الإجمالي</th>
            <th>طريقة التوصيل</th>
            <th>الحالة</th>
            <th>التاريخ</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <?php $st2 = $statusLabels[$o['status']] ?? ['label'=>$o['status'],'class'=>'']; ?>
            <tr>
              <td><span style="font-weight:700;color:var(--primary)">#<?= esc($o['order_num']) ?></span></td>
              <td style="font-weight:600"><?= esc($o['customer_name']) ?></td>
              <td class="text-muted"><?= esc($o['phone']) ?></td>
              <td><?= esc($o['city']) ?></td>
              <td style="font-weight:700"><?= number_format($o['total']) ?> EGP</td>
              <td>
                <?= $o['delivery_method'] === 'pickup' ? '🏪 استلام' : '🚚 توصيل' ?>
              </td>
              <td>
                <span class="badge <?= $st2['class'] ?>"><?= $st2['label'] ?></span>
                <?php if ($o['delivery_method'] === 'delivery'): ?>
                  <?php if ($o['payment_verified']): ?>
                    <span class="badge badge-instock" title="الدفع محقق"><i class="fas fa-check-circle"></i></span>
                  <?php else: ?>
                    <span class="badge badge-lowstock" title="الدفع غير محقق"><i class="fas fa-triangle-exclamation"></i></span>
                  <?php endif; ?>
                <?php endif; ?>
              </td>
              <td class="text-muted text-sm">
                <?= date('d/m/Y', strtotime($o['created_at'])) ?>
                <div style="font-size:10.5px"><?= date('H:i', strtotime($o['created_at'])) ?></div>
              </td>
              <td>
                <a href="order-view.php?id=<?= $o['id'] ?>" class="btn btn-outline btn-sm btn-icon" title="عرض">
                  <i class="fas fa-eye"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <?php if ($pages > 1): ?>
    <div style="display:flex;justify-content:center;gap:6px;padding:14px;border-top:1px solid var(--border);">
      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a href="?<?= http_build_query(array_filter(['status'=>$filterStatus,'q'=>$search,'page'=>$i])) ?>"
           style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:var(--radius-sm);border:1px solid var(--border);text-decoration:none;font-size:13px;font-weight:600;background:<?= $i === $page_num ? 'var(--primary)' : 'var(--card)' ?>;color:<?= $i === $page_num ? '#fff' : 'var(--text-secondary)' ?>;">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>

<?php include 'includes/layout-end.php'; ?>
