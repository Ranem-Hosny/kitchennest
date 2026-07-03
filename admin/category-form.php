<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/admin-config.php';
require_once 'includes/auth.php';

$db     = adminDB();
$editId = (int)($_GET['id'] ?? 0);
$cat    = null;

if ($editId) {
    $st = $db->prepare('SELECT * FROM categories WHERE id=?');
    $st->execute([$editId]);
    $cat = $st->fetch();
    if (!$cat) { header('Location: categories.php'); exit; }
}

function slugify(string $text): string {
    $text = trim($text);
    $map  = ['أ'=>'a','إ'=>'a','آ'=>'a','ة'=>'h','ى'=>'a'];
    $text = strtr($text, $map);
    $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', $text);
    $slug = strtolower(trim($slug, '-'));
    // Arabic (or other non-Latin) names strip down to near-nothing — fall back to a stable hash slug
    return strlen($slug) >= 3 ? $slug : 'cat-' . substr(md5($text), 0, 8);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $slugInput  = trim($_POST['slug'] ?? '');
    $icon       = trim($_POST['icon'] ?? '') ?: 'fa-tag';
    $color      = trim($_POST['color'] ?? '') ?: '#FF6B00';
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (!$name) $errors[] = 'اسم الفئة مطلوب.';

    $slug = $slugInput ? slugify($slugInput) : slugify($name);

    // Ensure slug uniqueness
    if (empty($errors)) {
        $chk = $db->prepare('SELECT id FROM categories WHERE slug=? AND id != ?');
        $chk->execute([$slug, $editId]);
        if ($chk->fetch()) $errors[] = 'المعرف (slug) مستخدم بالفعل لفئة أخرى، جرّب اسماً مختلفاً.';
    }

    if (empty($errors)) {
        if ($editId) {
            $db->prepare('UPDATE categories SET name=?, slug=?, icon=?, color=?, sort_order=? WHERE id=?')
               ->execute([$name, $slug, $icon, $color, $sort_order, $editId]);
        } else {
            $db->prepare('INSERT INTO categories (name, slug, icon, color, sort_order, created_at) VALUES (?,?,?,?,?, NOW())')
               ->execute([$name, $slug, $icon, $color, $sort_order]);
        }
        header('Location: categories.php?msg=saved');
        exit;
    }
    $cat = array_merge($cat ?? [], $_POST);
}

$isEdit = $editId > 0 && $cat;

$pageTitle     = $isEdit ? 'تعديل فئة: ' . esc($cat['name']) : 'إضافة فئة جديدة';
$currentPage   = 'categories';
$pageSubtitle  = $isEdit ? 'تعديل بيانات الفئة' : 'أضف فئة جديدة للمنتجات';
$topbarActions = '<a href="categories.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-right"></i> العودة للتصنيفات</a>';

function cval(array|null $c, string $key, string $default = ''): string {
    return esc($c[$key] ?? $default);
}

include 'includes/layout-start.php';
?>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <?= implode(' &nbsp;·&nbsp; ', array_map('esc', $errors)) ?>
  </div>
<?php endif; ?>

<form method="POST">
<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

  <div>
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-tag" style="color:var(--primary)"></i> بيانات الفئة</span>
      </div>
      <div class="card-body">
        <div class="form-grid">
          <div class="form-group full">
            <label class="form-label">اسم الفئة <span style="color:var(--danger)">*</span></label>
            <input type="text" name="name" class="form-control" required
                   placeholder="مثال: أواني الطهي"
                   value="<?= cval($cat, 'name') ?>">
          </div>
          <div class="form-group full">
            <label class="form-label">المعرف (slug)</label>
            <input type="text" name="slug" class="form-control"
                   placeholder="اتركه فارغاً ليُنشأ تلقائياً من الاسم"
                   value="<?= cval($cat, 'slug') ?>">
            <span class="form-hint">يُستخدم داخلياً لربط المنتجات بالفئة — تجنب تغييره بعد إضافة منتجات</span>
          </div>
          <div class="form-group">
            <label class="form-label">أيقونة (Font Awesome)</label>
            <input type="text" name="icon" id="iconInput" class="form-control"
                   placeholder="fa-utensils" value="<?= cval($cat, 'icon', 'fa-tag') ?>"
                   oninput="document.getElementById('iconPreview').className = 'fas ' + this.value">
            <span class="form-hint">اسم أيقونة من Font Awesome بدون "fa-solid"، مثال: fa-utensils</span>
          </div>
          <div class="form-group">
            <label class="form-label">اللون</label>
            <input type="color" name="color" class="form-control" style="height:42px;padding:4px;"
                   value="<?= cval($cat, 'color', '#FF6B00') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">ترتيب الظهور</label>
            <input type="number" name="sort_order" class="form-control" min="0"
                   value="<?= cval($cat, 'sort_order', '0') ?>">
          </div>
        </div>
      </div>
    </div>
  </div>

  <div>
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-eye" style="color:var(--info)"></i> معاينة</span>
      </div>
      <div class="card-body" style="display:flex;align-items:center;gap:12px;">
        <div style="width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:<?= cval($cat,'color','#FF6B00') ?>1A;color:<?= cval($cat,'color','#FF6B00') ?>;font-size:20px;">
          <i class="fas <?= cval($cat, 'icon', 'fa-tag') ?>" id="iconPreview"></i>
        </div>
        <span style="font-weight:700;font-size:14px;"><?= $cat['name'] ?? 'اسم الفئة' ?></span>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;font-size:15px;padding:11px;">
          <i class="fas fa-save"></i>
          <?= $isEdit ? 'حفظ التغييرات' : 'إضافة الفئة' ?>
        </button>
        <a href="categories.php" class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:8px;">
          إلغاء
        </a>
      </div>
    </div>
  </div>

</div>
</form>

<?php include 'includes/layout-end.php'; ?>
