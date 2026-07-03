<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/admin-config.php';
require_once 'includes/auth.php';

$db = adminDB();

// Owner-added products always get a slug like "product-<timestamp>-<rand>".
// Demo/sample products (seeded from data.js) have descriptive slugs.
$KEEP_COND = "slug LIKE 'product-%'";
$DEL_COND  = "(slug IS NULL OR slug NOT LIKE 'product-%')";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'cleanup') {
    $n = $db->exec("DELETE FROM products WHERE $DEL_COND");
    header('Location: product-cleanup.php?msg=done&n=' . (int)$n);
    exit;
}

$keep = $db->query("SELECT id,name,image_url,slug FROM products WHERE $KEEP_COND ORDER BY id")->fetchAll();
$del  = $db->query("SELECT id,name,image_url,slug FROM products WHERE $DEL_COND ORDER BY id")->fetchAll();

$pageTitle    = 'تنظيف المنتجات';
$currentPage  = 'products';
$pageSubtitle = 'حذف المنتجات التجريبية والإبقاء على منتجاتك';
$topbarActions = '<a href="products.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-right"></i> المنتجات</a>';

include 'includes/layout-start.php';

function thumb($url, $name) {
    $u = trim((string)$url);
    if ($u !== '') return '<img src="'.esc($u).'" alt="'.esc($name).'" style="width:34px;height:34px;border-radius:8px;object-fit:cover;flex-shrink:0">';
    return '<div style="width:34px;height:34px;border-radius:8px;background:var(--bg);display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-image" style="color:var(--text-muted)"></i></div>';
}
?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'done'): ?>
  <div class="alert alert-success"><i class="fas fa-check-circle"></i>
    تم حذف <?= (int)($_GET['n'] ?? 0) ?> منتج تجريبي. المتجر أصبح يعرض منتجاتك فقط.</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

  <div class="card">
    <div class="card-header">
      <span class="card-title" style="color:#15803D"><i class="fas fa-circle-check"></i> هتفضل (منتجاتك) — <?= count($keep) ?></span>
    </div>
    <div class="card-body" style="padding:0">
      <?php if (empty($keep)): ?>
        <div class="empty-state" style="padding:24px"><div class="empty-state-sub">لا توجد منتجات مضافة من اللوحة بعد.</div></div>
      <?php else: foreach ($keep as $p): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid var(--border-light)">
          <?= thumb($p['image_url'], $p['name']) ?>
          <span style="font-weight:600;font-size:13px"><?= esc($p['name']) ?></span>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title" style="color:#B91C1C"><i class="fas fa-trash"></i> هتتمسح (تجريبية) — <?= count($del) ?></span>
    </div>
    <div class="card-body" style="padding:0;max-height:420px;overflow:auto">
      <?php if (empty($del)): ?>
        <div class="empty-state" style="padding:24px"><div class="empty-state-sub">لا توجد منتجات تجريبية — المتجر نظيف بالفعل ✓</div></div>
      <?php else: foreach ($del as $p): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid var(--border-light);opacity:.75">
          <?= thumb($p['image_url'], $p['name']) ?>
          <span style="font-size:13px"><?= esc($p['name']) ?></span>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

</div>

<?php if (!empty($del)): ?>
  <div class="card" style="margin-top:20px;border:1px solid #FECACA;background:#FEF2F2">
    <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
      <div style="font-size:14px;color:#991B1B">
        <strong><i class="fas fa-triangle-exclamation"></i> تنبيه:</strong>
        الحذف نهائي ولا يمكن التراجع عنه. سيتم حذف <?= count($del) ?> منتج تجريبي والإبقاء على <?= count($keep) ?> من منتجاتك.
      </div>
      <form method="POST" onsubmit="return confirm('تأكيد نهائي: حذف <?= count($del) ?> منتج تجريبي؟ لا يمكن التراجع.')">
        <input type="hidden" name="_action" value="cleanup">
        <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> احذف المنتجات التجريبية (<?= count($del) ?>)</button>
      </form>
    </div>
  </div>
<?php endif; ?>

<?php include 'includes/layout-end.php'; ?>
