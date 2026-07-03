<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/admin-config.php';
require_once 'includes/auth.php';

$db     = adminDB();
$editId = (int)($_GET['id'] ?? 0);
$col    = null;

if ($editId) {
    $st = $db->prepare('SELECT * FROM collections WHERE id=?');
    $st->execute([$editId]);
    $col = $st->fetch();
    if (!$col) { header('Location: collections.php'); exit; }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title'] ?? '');
    $catSlug    = trim($_POST['category'] ?? '');           // DB category slug from the dropdown
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active  = isset($_POST['is_active']) ? 1 : 0;
    $image_url  = trim($_POST['image_url'] ?? '');

    // Link + product count are derived from the chosen category — no manual entry
    $category   = $catSlug ? dbCatToSite($catSlug) : '';
    $link       = $category ? 'category.html?cat=' . rawurlencode($category) : '';
    $item_count = 0; // computed live on the homepage from the actual products

    if (!$title)   $errors[] = 'عنوان المجموعة مطلوب.';
    if (!$catSlug) $errors[] = 'اختر الفئة التي تفتحها المجموعة.';

    // Uploaded file (from device) takes priority over the URL field
    if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowedExt = ['jpg' => true, 'jpeg' => true, 'png' => true, 'webp' => true, 'gif' => true];
        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));

        if ($_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'فشل رفع الصورة، حاول مرة أخرى.';
        } elseif (!isset($allowedExt[$ext])) {
            $errors[] = 'صيغة الصورة غير مدعومة. المسموح: JPG, PNG, WEBP, GIF.';
        } elseif ($_FILES['image_file']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'حجم الصورة أكبر من 5 ميجابايت.';
        } else {
            $uploadDir = __DIR__ . '/../uploads/collections/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $filename = 'c-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $filename)) {
                $image_url = '/uploads/collections/' . $filename;
            } else {
                $errors[] = 'فشل حفظ الصورة على الخادم.';
            }
        }
    }

    if (empty($errors)) {
        if ($editId) {
            $db->prepare('UPDATE collections SET title=?, image_url=?, link=?, category=?, item_count=?, sort_order=?, is_active=? WHERE id=?')
               ->execute([$title, $image_url, $link, $category, $item_count, $sort_order, $is_active, $editId]);
        } else {
            $db->prepare('INSERT INTO collections (title, image_url, link, category, item_count, sort_order, is_active, created_at) VALUES (?,?,?,?,?,?,?, NOW())')
               ->execute([$title, $image_url, $link, $category, $item_count, $sort_order, $is_active]);
        }
        header('Location: collections.php?msg=saved');
        exit;
    }
    $col = array_merge($col ?? [], $_POST);
}

$isEdit = $editId > 0 && $col;
$cats   = getCategories();

$pageTitle     = $isEdit ? 'تعديل مجموعة: ' . esc($col['title']) : 'إضافة مجموعة جديدة';
$currentPage   = 'collections';
$pageSubtitle  = $isEdit ? 'تعديل بيانات المجموعة' : 'أضف مجموعة لقسم «مجموعات مختارة»';
$topbarActions = '<a href="collections.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-right"></i> العودة للمجموعات</a>';

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
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

  <div>
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-th-large" style="color:var(--primary)"></i> بيانات المجموعة</span>
      </div>
      <div class="card-body">
        <div class="form-grid">
          <div class="form-group full">
            <label class="form-label">عنوان المجموعة <span style="color:var(--danger)">*</span></label>
            <input type="text" name="title" class="form-control" required
                   placeholder="مثال: أساسيات مطبخ رمضان"
                   value="<?= cval($col, 'title') ?>">
          </div>

          <div class="form-group full">
            <label class="form-label">الفئة <span style="color:var(--danger)">*</span></label>
            <select name="category" class="form-control" required>
              <option value="">— اختر الفئة —</option>
              <?php foreach ($cats as $c): $siteId = dbCatToSite($c['slug']); ?>
                <option value="<?= esc($c['slug']) ?>" <?= (($col['category'] ?? '') === $siteId) ? 'selected' : '' ?>>
                  <?= esc($c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <span class="form-hint">عند الضغط على المجموعة ستفتح صفحة هذه الفئة، وعدد المنتجات يظهر تلقائياً حسب عدد منتجاتها الفعلي.</span>
          </div>

          <div class="form-group">
            <label class="form-label">ترتيب الظهور</label>
            <input type="number" name="sort_order" class="form-control" min="0"
                   value="<?= cval($col, 'sort_order', '0') ?>">
            <span class="form-hint">الأصغر يظهر أولاً</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div>
    <!-- Image -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-image" style="color:var(--info)"></i> صورة المجموعة</span>
      </div>
      <div class="card-body">
        <div style="display:flex;gap:8px;margin-bottom:14px;">
          <button type="button" class="btn btn-outline btn-sm" id="tabUploadBtn" style="flex:1;justify-content:center;" onclick="switchImageTab('upload')">
            <i class="fas fa-upload"></i> رفع من الجهاز
          </button>
          <button type="button" class="btn btn-outline btn-sm" id="tabUrlBtn" style="flex:1;justify-content:center;" onclick="switchImageTab('url')">
            <i class="fas fa-link"></i> رابط
          </button>
        </div>

        <div id="imageUploadTab" class="form-group">
          <label class="form-label">اختر صورة من جهازك</label>
          <input type="file" name="image_file" id="imageFile" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif"
                 onchange="previewFile(this)">
          <span class="form-hint">JPG, PNG, WEBP أو GIF — بحد أقصى 5 ميجابايت</span>
        </div>

        <div id="imageUrlTab" class="form-group" style="display:none;">
          <label class="form-label">رابط الصورة (URL)</label>
          <input type="url" name="image_url" id="imageUrl" class="form-control"
                 placeholder="https://..."
                 value="<?= cval($col, 'image_url') ?>"
                 oninput="previewImage(this.value)">
          <span class="form-hint">ضع رابط صورة مباشر (Unsplash أو أي رابط)</span>
        </div>

        <div id="imgPreviewWrap" style="margin-top:12px;display:<?= ($col['image_url'] ?? '') ? 'block' : 'none' ?>">
          <img id="imgPreview"
               src="<?= cval($col, 'image_url') ?>"
               alt="معاينة"
               style="width:100%;max-height:200px;object-fit:cover;border-radius:var(--radius-sm);border:1px solid var(--border);">
        </div>
      </div>
    </div>

    <!-- Visibility -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-eye" style="color:var(--primary)"></i> الظهور</span>
      </div>
      <div class="card-body">
        <label class="flag-label">
          <input type="checkbox" name="is_active" <?= (($col['is_active'] ?? 1)) ? 'checked' : '' ?>>
          <i class="fas fa-check-circle" style="color:var(--success)"></i> ظاهرة على الموقع
        </label>
      </div>
    </div>

    <!-- Submit -->
    <div class="card">
      <div class="card-body">
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;font-size:15px;padding:11px;">
          <i class="fas fa-save"></i>
          <?= $isEdit ? 'حفظ التغييرات' : 'إضافة المجموعة' ?>
        </button>
        <a href="collections.php" class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:8px;">
          إلغاء
        </a>
      </div>
    </div>
  </div>

</div>
</form>

<script>
function previewImage(url) {
    const wrap = document.getElementById('imgPreviewWrap');
    const img  = document.getElementById('imgPreview');
    if (url) { img.src = url; wrap.style.display = 'block'; }
    else { wrap.style.display = 'none'; }
}

function previewFile(input) {
    const file = input.files && input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => previewImage(e.target.result);
    reader.readAsDataURL(file);
}

function switchImageTab(tab) {
    const uploadTab = document.getElementById('imageUploadTab');
    const urlTab    = document.getElementById('imageUrlTab');
    const uploadBtn = document.getElementById('tabUploadBtn');
    const urlBtn    = document.getElementById('tabUrlBtn');

    uploadTab.style.display = tab === 'upload' ? 'block' : 'none';
    urlTab.style.display    = tab === 'url'    ? 'block' : 'none';
    uploadBtn.classList.toggle('btn-primary', tab === 'upload');
    uploadBtn.classList.toggle('btn-outline', tab !== 'upload');
    urlBtn.classList.toggle('btn-primary', tab === 'url');
    urlBtn.classList.toggle('btn-outline', tab !== 'url');
}

document.addEventListener('DOMContentLoaded', () => {
    const existingUrl = document.getElementById('imageUrl').value.trim();
    switchImageTab(existingUrl ? 'url' : 'upload');
});
</script>

<?php include 'includes/layout-end.php'; ?>
