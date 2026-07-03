<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/admin-config.php';
require_once 'includes/auth.php';

$db = adminDB();

// Delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    $delId = (int)($_POST['id'] ?? 0);
    if ($delId) {
        $db->prepare('DELETE FROM collections WHERE id=?')->execute([$delId]);
        header('Location: collections.php?msg=deleted');
        exit;
    }
}

// Quick active toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $db->prepare('UPDATE collections SET is_active = 1 - is_active WHERE id=?')->execute([$id]);
        header('Location: collections.php?msg=toggled');
        exit;
    }
}

$collections = $db->query('SELECT * FROM collections ORDER BY sort_order, id')->fetchAll();
$activeCount = 0;
foreach ($collections as $c) { if ($c['is_active']) $activeCount++; }

// Live product count per site-category (admin-added products only).
$catCounts = [];
foreach ($db->query('SELECT category, COUNT(*) c FROM products GROUP BY category') as $r) {
    $site = dbCatToSite($r['category']);
    $catCounts[$site] = ($catCounts[$site] ?? 0) + (int)$r['c'];
}
// Map a site-category id back to its Arabic name for display.
$catNames = [];
foreach (getCategories() as $c) { $catNames[dbCatToSite($c['slug'])] = $c['name']; }

$pageTitle   = 'المجموعات المختارة';
$currentPage = 'collections';

include 'includes/layout-start.php';
?>

<div class="page-header">
  <div class="page-header-left">
    <div class="page-header-title">المجموعات المختارة</div>
    <div class="page-header-sub"><?= number_format(count($collections)) ?> مجموعة · <?= number_format($activeCount) ?> ظاهرة على الموقع</div>
  </div>
  <a href="collection-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> إضافة مجموعة</a>
</div>

<?php if (isset($_GET['msg'])): ?>
  <?php
    $msgMap = [
      'deleted' => ['danger', 'trash', 'تم حذف المجموعة.'],
      'saved'   => ['success', 'check-circle', 'تم حفظ المجموعة بنجاح.'],
      'toggled' => ['success', 'eye', 'تم تحديث حالة الظهور.'],
    ];
    $m = $msgMap[$_GET['msg']] ?? null;
  ?>
  <?php if ($m): ?>
    <div class="alert alert-<?= $m[0] ?>"><i class="fas fa-<?= $m[1] ?>"></i> <?= $m[2] ?></div>
  <?php endif; ?>
<?php endif; ?>

<div class="card">
  <div class="table-wrap">
    <?php if (empty($collections)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">🖼️</div>
        <div class="empty-state-title">لا توجد مجموعات بعد</div>
        <div class="empty-state-sub" style="margin-bottom:16px">أضف أول مجموعة لتظهر في قسم «مجموعات مختارة» بالصفحة الرئيسية</div>
        <a href="collection-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> إضافة مجموعة</a>
      </div>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>المجموعة</th>
            <th>الفئة</th>
            <th>عدد المنتجات</th>
            <th>الترتيب</th>
            <th>الظهور</th>
            <th>إجراءات</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($collections as $c): ?>
            <tr>
              <td>
                <div class="product-thumb">
                  <?php if ($c['image_url']): ?>
                    <img src="<?= esc($c['image_url']) ?>" alt="<?= esc($c['title']) ?>"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="product-thumb-placeholder" style="display:none"><i class="fas fa-image"></i></div>
                  <?php else: ?>
                    <div class="product-thumb-placeholder"><i class="fas fa-image"></i></div>
                  <?php endif; ?>
                  <div style="font-weight:600;font-size:13px"><?= esc($c['title']) ?></div>
                </div>
              </td>
              <td class="text-muted"><?= esc($catNames[$c['category']] ?? ($c['category'] ?: '—')) ?></td>
              <td>
                <span class="badge badge-instock"><?= (int)($catCounts[$c['category']] ?? 0) ?> منتج</span>
              </td>
              <td class="text-muted"><?= (int)$c['sort_order'] ?></td>
              <td>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="_action" value="toggle">
                  <input type="hidden" name="id" value="<?= $c['id'] ?>">
                  <button type="submit" class="badge <?= $c['is_active'] ? 'badge-instock' : 'badge-read' ?>"
                          style="border:none;cursor:pointer" title="اضغط لتغيير الظهور">
                    <i class="fas fa-<?= $c['is_active'] ? 'eye' : 'eye-slash' ?>"></i>
                    <?= $c['is_active'] ? 'ظاهرة' : 'مخفية' ?>
                  </button>
                </form>
              </td>
              <td>
                <div style="display:flex;gap:6px;">
                  <a href="collection-form.php?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm btn-icon" title="تعديل">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form method="POST" onsubmit="return confirm('هل تريد حذف هذه المجموعة؟')">
                    <input type="hidden" name="_action" value="delete">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
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
</div>

<?php include 'includes/layout-end.php'; ?>
