<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/admin-config.php';
require_once 'includes/auth.php';

$db = adminDB();

// Delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    $delId = (int)($_POST['id'] ?? 0);
    if ($delId) {
        $slug = $db->prepare('SELECT slug FROM categories WHERE id=?');
        $slug->execute([$delId]);
        $slugVal = $slug->fetchColumn();
        $inUse = 0;
        if ($slugVal) {
            $chk = $db->prepare('SELECT COUNT(*) FROM products WHERE category=?');
            $chk->execute([$slugVal]);
            $inUse = (int)$chk->fetchColumn();
        }
        if ($inUse > 0) {
            header('Location: categories.php?msg=inuse&count=' . $inUse);
            exit;
        }
        $db->prepare('DELETE FROM categories WHERE id=?')->execute([$delId]);
        header('Location: categories.php?msg=deleted');
        exit;
    }
}

$categories = $db->query(
    "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category = c.slug) AS product_count
     FROM categories c ORDER BY c.sort_order, c.name"
)->fetchAll();

$pageTitle     = 'التصنيفات';
$currentPage   = 'categories';

include 'includes/layout-start.php';
?>

<div class="page-header">
  <div class="page-header-left">
    <div class="page-header-title">التصنيفات</div>
    <div class="page-header-sub"><?= number_format(count($categories)) ?> فئة</div>
  </div>
  <a href="category-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> إضافة فئة</a>
</div>

<?php if (isset($_GET['msg'])): ?>
  <?php if ($_GET['msg'] === 'inuse'): ?>
    <div class="alert alert-danger">
      <i class="fas fa-exclamation-circle"></i>
      لا يمكن حذف هذه الفئة لأنها مستخدمة في <?= (int)($_GET['count'] ?? 0) ?> منتج. غيّر فئة هذه المنتجات أولاً.
    </div>
  <?php else: ?>
    <div class="alert alert-<?= $_GET['msg'] === 'deleted' ? 'danger' : 'success' ?>">
      <i class="fas fa-<?= $_GET['msg'] === 'deleted' ? 'trash' : 'check-circle' ?>"></i>
      <?= $_GET['msg'] === 'deleted' ? 'تم حذف الفئة.' : 'تم حفظ الفئة بنجاح.' ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<div class="card">
  <div class="table-wrap">
    <?php if (empty($categories)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">🗂️</div>
        <div class="empty-state-title">لا توجد فئات بعد</div>
        <div class="empty-state-sub" style="margin-bottom:16px">أضف أول فئة الآن</div>
        <a href="category-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> إضافة فئة</a>
      </div>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>الفئة</th>
            <th>المعرف (slug)</th>
            <th>عدد المنتجات</th>
            <th>الترتيب</th>
            <th>إجراءات</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $c): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <div style="width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:<?= esc($c['color'] ?: '#FF6B00') ?>1A;color:<?= esc($c['color'] ?: '#FF6B00') ?>;flex-shrink:0;">
                    <i class="fas <?= esc($c['icon'] ?: 'fa-tag') ?>"></i>
                  </div>
                  <span style="font-weight:600;font-size:13px;"><?= esc($c['name']) ?></span>
                </div>
              </td>
              <td class="text-muted"><code><?= esc($c['slug']) ?></code></td>
              <td>
                <span class="badge <?= $c['product_count'] > 0 ? 'badge-instock' : 'badge-read' ?>">
                  <?= (int)$c['product_count'] ?> منتج
                </span>
              </td>
              <td class="text-muted"><?= (int)$c['sort_order'] ?></td>
              <td>
                <div style="display:flex;gap:6px;">
                  <a href="category-form.php?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm btn-icon" title="تعديل">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form method="POST" onsubmit="return confirm('هل تريد حذف هذه الفئة؟')">
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
