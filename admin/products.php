<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/admin-config.php';
require_once 'includes/auth.php';

$db = adminDB();

// Ensure products table exists
try {
    $db->query('SELECT 1 FROM products LIMIT 1');
} catch (Exception $e) {
    header('Location: setup.php?redir=products');
    exit;
}

// Delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    $delId = (int)($_POST['id'] ?? 0);
    if ($delId) {
        $db->prepare('DELETE FROM products WHERE id=?')->execute([$delId]);
        header('Location: products.php?msg=deleted');
        exit;
    }
}

$filterCat = $_GET['cat'] ?? '';
$search    = trim($_GET['q'] ?? '');
$page_num  = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 20;
$offset    = ($page_num - 1) * $perPage;

$where  = '1=1';
$params = [];

if ($filterCat) {
    $where .= ' AND category = ?';
    $params[] = $filterCat;
}
if ($search) {
    $where .= ' AND (name LIKE ? OR category LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$cnt = $db->prepare("SELECT COUNT(*) FROM products WHERE $where");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pages = max(1, ceil($total / $perPage));

$st = $db->prepare("SELECT * FROM products WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$st->execute($params);
$products = $st->fetchAll();

$pageTitle   = 'المنتجات';
$currentPage = 'products';

$cats = getCategoryList();

include 'includes/layout-start.php';
?>

<div class="page-header">
  <div class="page-header-left">
    <div class="page-header-title">المنتجات</div>
    <div class="page-header-sub"><?= number_format($total) ?> منتج في قاعدة البيانات</div>
  </div>
  <a href="product-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> إضافة منتج</a>
</div>

<?php if (isset($_GET['msg'])): ?>
  <div class="alert alert-<?= $_GET['msg'] === 'deleted' ? 'danger' : 'success' ?>">
    <i class="fas fa-<?= $_GET['msg'] === 'deleted' ? 'trash' : 'check-circle' ?>"></i>
    <?= $_GET['msg'] === 'deleted' ? 'تم حذف المنتج.' : 'تم حفظ المنتج بنجاح.' ?>
  </div>
<?php endif; ?>

<div class="card">
  <div class="filter-bar">
    <form method="GET" style="display:flex;gap:8px;flex:1;flex-wrap:wrap;align-items:center;">
      <input type="search" name="q" class="filter-search" placeholder="بحث باسم المنتج…"
             value="<?= esc($search) ?>">
      <select name="cat" class="filter-select" onchange="this.form.submit()">
        <option value="">كل الفئات</option>
        <?php foreach ($cats as $key => $label): ?>
          <option value="<?= $key ?>" <?= $filterCat === $key ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-search"></i></button>
      <?php if ($search || $filterCat): ?>
        <a href="products.php" class="btn btn-ghost btn-sm"><i class="fas fa-times"></i></a>
      <?php endif; ?>
    </form>
  </div>

  <div class="table-wrap">
    <?php if (empty($products)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">🛍️</div>
        <div class="empty-state-title">لا توجد منتجات بعد</div>
        <div class="empty-state-sub" style="margin-bottom:16px">أضف أول منتج الآن</div>
        <a href="product-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> إضافة منتج</a>
      </div>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>المنتج</th>
            <th>الفئة</th>
            <th>السعر</th>
            <th>الحالة</th>
            <th>المخزون</th>
            <th>مميزات</th>
            <th>تاريخ الإضافة</th>
            <th>إجراءات</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
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
                  <div>
                    <div style="font-weight:600;font-size:13px"><?= esc($p['name']) ?></div>
                    <?php if ($p['short_desc']): ?>
                      <div class="text-muted text-sm" style="margin-top:2px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <?= esc($p['short_desc']) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td><span class="text-muted"><?= esc($cats[$p['category']] ?? $p['category']) ?></span></td>
              <td>
                <span style="font-weight:700"><?= number_format($p['price']) ?> EGP</span>
                <?php if ($p['old_price'] && $p['old_price'] > $p['price']): ?>
                  <div class="text-muted text-sm" style="text-decoration:line-through"><?= number_format($p['old_price']) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($p['in_stock']): ?>
                  <span class="badge badge-instock">متوفر</span>
                <?php else: ?>
                  <span class="badge badge-outstock">نفذ</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ((int)$p['stock_qty'] <= 0): ?>
                  <span style="font-weight:700;color:var(--danger)">0</span>
                <?php elseif ((int)$p['stock_qty'] <= 5): ?>
                  <span class="badge badge-lowstock" title="مخزون منخفض"><?= (int)$p['stock_qty'] ?> قطعة</span>
                <?php else: ?>
                  <span style="font-weight:600"><?= (int)$p['stock_qty'] ?> قطعة</span>
                <?php endif; ?>
              </td>
              <td style="font-size:16px;letter-spacing:2px;">
                <?= $p['is_new']        ? '🆕' : '' ?>
                <?= $p['is_bestseller'] ? '⭐' : '' ?>
                <?= $p['is_featured']   ? '✨' : '' ?>
                <?= $p['is_offer']      ? '🏷️' : '' ?>
              </td>
              <td class="text-muted text-sm"><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
              <td>
                <div style="display:flex;gap:6px;">
                  <a href="product-form.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm btn-icon" title="تعديل">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form method="POST" onsubmit="return confirm('هل تريد حذف هذا المنتج نهائياً؟')">
                    <input type="hidden" name="_action" value="delete">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm btn-icon" title="حذف">
                      <i class="fas fa-trash"></i>
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

  <!-- Pagination -->
  <?php if ($pages > 1): ?>
    <div style="display:flex;justify-content:center;gap:6px;padding:14px;border-top:1px solid var(--border);">
      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a href="?<?= http_build_query(array_filter(['cat'=>$filterCat,'q'=>$search,'page'=>$i])) ?>"
           style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:var(--radius-sm);border:1px solid var(--border);text-decoration:none;font-size:13px;font-weight:600;background:<?= $i === $page_num ? 'var(--primary)' : 'var(--card)' ?>;color:<?= $i === $page_num ? '#fff' : 'var(--text-secondary)' ?>;">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>

</div>

<?php include 'includes/layout-end.php'; ?>
