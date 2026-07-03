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
    return strlen($slug) >= 3 ? $slug : 'cat-' . substr(md5($text), 0, 8);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $slugInput  = trim($_POST['slug'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $image_url  = trim($_POST['image_url'] ?? '');

    // Category image (uploaded file takes priority over the URL field)
    if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowedExt = ['jpg'=>1,'jpeg'=>1,'png'=>1,'webp'=>1,'gif'=>1];
        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        if ($_FILES['image_file']['error'] !== UPLOAD_ERR_OK)      $errors[] = 'فشل رفع الصورة، حاول مرة أخرى.';
        elseif (!isset($allowedExt[$ext]))                        $errors[] = 'صيغة الصورة غير مدعومة. المسموح: JPG, PNG, WEBP, GIF.';
        elseif ($_FILES['image_file']['size'] > 5 * 1024 * 1024)  $errors[] = 'حجم الصورة أكبر من 5 ميجابايت.';
        else {
            $uploadDir = __DIR__ . '/../uploads/categories/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $filename = 'cat-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $filename)) $image_url = '/uploads/categories/' . $filename;
            else $errors[] = 'فشل حفظ الصورة على الخادم.';
        }
    }

    if (!$name) $errors[] = 'اسم الفئة مطلوب.';
    $slug = $slugInput ? slugify($slugInput) : slugify($name);

    if (empty($errors)) {
        $chk = $db->prepare('SELECT id FROM categories WHERE slug=? AND id != ?');
        $chk->execute([$slug, $editId]);
        if ($chk->fetch()) $errors[] = 'المعرف (slug) مستخدم بالفعل لفئة أخرى، جرّب اسماً مختلفاً.';
    }

    if (empty($errors)) {
        if ($editId) {
            $db->prepare('UPDATE categories SET name=?, slug=?, image_url=?, sort_order=? WHERE id=?')
               ->execute([$name, $slug, $image_url, $sort_order, $editId]);
        } else {
            $db->prepare('INSERT INTO categories (name, slug, image_url, sort_order, created_at) VALUES (?,?,?,?, NOW())')
               ->execute([$name, $slug, $image_url, $sort_order]);
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
    return esc((string)($c[$key] ?? $default));
}

include 'includes/layout-start.php';
?>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <?= implode(' &nbsp;·&nbsp; ', array_map('esc', $errors)) ?>
  </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
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
                   placeholder="مثال: أواني الطهي" value="<?= cval($cat, 'name') ?>">
          </div>
          <div class="form-group full">
            <label class="form-label">المعرف (slug)</label>
            <input type="text" name="slug" class="form-control"
                   placeholder="اتركه فارغاً ليُنشأ تلقائياً من الاسم" value="<?= cval($cat, 'slug') ?>">
            <span class="form-hint">يُستخدم داخلياً لربط المنتجات بالفئة — تجنب تغييره بعد إضافة منتجات</span>
          </div>

          <div class="form-group full">
            <label class="form-label">صورة الفئة</label>
            <input type="file" name="image_file" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif"
                   onchange="catPreviewFile(this)" style="margin-bottom:6px;">
            <input type="url" name="image_url" id="catImgUrl" class="form-control"
                   placeholder="أو الصق رابط صورة (https://...)" value="<?= cval($cat, 'image_url') ?>"
                   oninput="catPreviewUrl(this.value)">
            <span class="form-hint">ارفع صورة معبّرة عن الفئة — سنعرضها بحجم أيقونة دائرية تلقائياً. (بدون الحاجة لأي أكواد)</span>
          </div>

          <div class="form-group">
            <label class="form-label">ترتيب الظهور</label>
            <input type="number" name="sort_order" class="form-control" min="0"
                   value="<?= cval($cat, 'sort_order', '0') ?>">
            <span class="form-hint">الأصغر يظهر أولاً على الموقع.</span>
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
      <div class="card-body" style="display:flex;flex-direction:column;align-items:center;gap:12px;text-align:center;">
        <div style="width:84px;height:84px;border-radius:50%;overflow:hidden;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;">
          <img id="catImgPreview" src="<?= cval($cat, 'image_url') ?>" alt="معاينة"
               style="width:100%;height:100%;object-fit:cover;<?= ($cat['image_url'] ?? '') ? '' : 'display:none' ?>">
          <i id="catImgPlaceholder" class="fas fa-image" style="color:var(--text-muted);font-size:26px;<?= ($cat['image_url'] ?? '') ? 'display:none' : '' ?>"></i>
        </div>
        <span style="font-weight:700;font-size:14px;"><?= esc($cat['name'] ?? 'اسم الفئة') ?></span>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;font-size:15px;padding:11px;">
          <i class="fas fa-save"></i> <?= $isEdit ? 'حفظ التغييرات' : 'إضافة الفئة' ?>
        </button>
        <a href="categories.php" class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:8px;">إلغاء</a>
      </div>
    </div>
  </div>

</div>
</form>

<script>
function catShow(url){
  var img=document.getElementById('catImgPreview'), ph=document.getElementById('catImgPlaceholder');
  if(url){ img.src=url; img.style.display='block'; ph.style.display='none'; }
  else { img.style.display='none'; ph.style.display='block'; }
}
function catPreviewUrl(url){ catShow(url); }
function catPreviewFile(input){
  var f=input.files && input.files[0]; if(!f) return;
  var r=new FileReader(); r.onload=function(e){ catShow(e.target.result); }; r.readAsDataURL(f);
}
</script>

<?php include 'includes/layout-end.php'; ?>
