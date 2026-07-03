<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/admin-config.php';
require_once 'includes/auth.php';

$db = adminDB();

// Quick toggle / remove-from-offers action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'remove_offer') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $db->prepare('UPDATE products SET is_offer=0, discount=0, old_price=NULL WHERE id=?')->execute([$id]);
        header('Location: offers.php?msg=removed');
        exit;
    }
}

$search = trim($_GET['q'] ?? '');
$where  = '(is_offer = 1 OR discount > 0)';
$params = [];
if ($search) {
    $where .= ' AND name LIKE ?';
    $params[] = "%$search%";
}

$st = $db->prepare("SELECT * FROM products WHERE $where ORDER BY discount DESC, created_at DESC");
$st->execute($params);
$offers = $st->fetchAll();

$total       = count($offers);
$avgDiscount = $total ? round(array_sum(array_column($offers, 'discount')) / $total) : 0;
$maxDiscount = $total ? max(array_column($offers, 'discount')) : 0;
$totalSavings = array_sum(array_map(fn($p) => max(0, ($p['old_price'] ?? 0) - $p['price']), $offers));

$cats = getCategoryList();

$pageTitle   = 'العروض';
$currentPage = 'offers';

include 'includes/layout-start.php';
?>

<div class="page-header">
  <div class="page-header-left">
    <div class="page-header-title">العروض</div>
    <div class="page-header-sub"><?= number_format($total) ?> منتج عليه عرض حالياً</div>
  </div>
  <a href="products.php" class="btn btn-outline"><i class="fas fa-shopping-bag"></i> كل المنتجات</a>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'removed'): ?>
  <div class="alert alert-danger">
    <i class="fas fa-tag"></i>
    تم إزالة المنتج من العروض.
  </div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card orange">
    <div class="stat-icon"><i class="fas fa-fire"></i></div>
    <div>
      <div class="stat-value"><?= number_format($total) ?></div>
      <div class="stat-label">منتج عليه عرض</div>
    </div>
  </div>
  <div class="stat-card yellow">
    <div class="stat-icon"><i class="fas fa-percent"></i></div>
    <div>
      <div class="stat-value"><?= $avgDiscount ?>%</div>
      <div class="stat-label">متوسط نسبة الخصم</div>
    </div>
  </div>
  <div class="stat-card red">
    <div class="stat-icon"><i class="fas fa-bolt"></i></div>
    <div>
      <div class="stat-value"><?= $maxDiscount ?>%</div>
      <div class="stat-label">أعلى نسبة خصم</div>
    </div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon"><i class="fas fa-sack-dollar"></i></div>
    <div>
      <div class="stat-value"><?= number_format($totalSavings) ?></div>
      <div class="stat-label">إجمالي التوفير (EGP)</div>
    </div>
  </div>
</div>

<div class="card">
  <div class="filter-bar">
    <form method="GET" style="display:flex;gap:8px;flex:1;flex-wrap:wrap;">
      <input type="search" name="q" class="filter-search" placeholder="بحث باسم المنتج…"
             value="<?= esc($search) ?>">
      <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-search"></i></button>
      <?php if ($search): ?>
        <a href="offers.php" class="btn btn-ghost btn-sm"><i class="fas fa-times"></i></a>
      <?php endif; ?>
    </form>
  </div>

  <div class="table-wrap">
    <?php if (empty($offers)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">🏷️</div>
        <div class="empty-state-title">لا توجد عروض حالياً</div>
        <div class="empty-state-sub" style="margin-bottom:16px">فعّل خصم على أي منتج من صفحة تعديل المنتج ليظهر هنا</div>
        <a href="products.php" class="btn btn-primary"><i class="fas fa-shopping-bag"></i> الذهاب للمنتجات</a>
      </div>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>المنتج</th>
            <th>الفئة</th>
            <th>السعر قبل</th>
            <th>السعر بعد</th>
            <th>الخصم</th>
            <th>إجراءات</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($offers as $p): ?>
            <tr>
              <td>
                <div class="product-thumb">
                  <?php if ($p['image_url']): ?>
                    <img src="<?= esc($p['image_url']) ?>" alt="<?= esc($p['name']) ?>"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="product-thumb-placeholder" style="display:none"><i class="fas fa-image"></i></div>
                  <?php else: ?>
                    <div class="product-thumb-placeholder"><i class="fas fa-image"></i></div>
                  <?php endif; ?>
                  <div style="font-weight:600;font-size:13px"><?= esc($p['name']) ?></div>
                </div>
              </td>
              <td><span class="text-muted"><?= esc($cats[$p['category']] ?? $p['category']) ?></span></td>
              <td class="text-muted" style="text-decoration:line-through">
                <?= $p['old_price'] ? number_format($p['old_price']) . ' EGP' : '—' ?>
              </td>
              <td style="font-weight:700"><?= number_format($p['price']) ?> EGP</td>
              <td>
                <span class="badge badge-pending"><?= (int)$p['discount'] ?>%-</span>
              </td>
              <td>
                <div style="display:flex;gap:6px;">
                  <a href="product-form.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm btn-icon" title="تعديل">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form method="POST" onsubmit="return confirm('هل تريد إزالة هذا المنتج من العروض؟')">
                    <input type="hidden" name="_action" value="remove_offer">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm btn-icon" title="إزالة من العروض">
                      <i class="fas fa-ban"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/layout-end.php'; ?>
