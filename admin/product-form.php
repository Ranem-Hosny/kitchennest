<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/admin-config.php';
require_once 'includes/auth.php';

$db    = adminDB();
$editId = (int)($_GET['id'] ?? 0);
$product = null;

if ($editId) {
    $st = $db->prepare('SELECT * FROM products WHERE id=?');
    $st->execute([$editId]);
    $product = $st->fetch();
    if (!$product) { header('Location: products.php'); exit; }
}

$errors = [];
$flash  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $subcategory = trim($_POST['subcategory'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $old_price   = $_POST['old_price'] !== '' ? (float)$_POST['old_price'] : null;
    $discount    = (int)($_POST['discount'] ?? 0);
    $short_desc  = trim($_POST['short_desc'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $material    = trim($_POST['material'] ?? '');
    $size        = trim($_POST['size'] ?? '');
    $color       = trim($_POST['color'] ?? '');
    $pieces      = (int)($_POST['pieces'] ?? 1);
    $image_url   = trim($_POST['image_url'] ?? '');

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
            $uploadDir = __DIR__ . '/../uploads/products/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $filename = 'p-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $filename)) {
                $image_url = '/uploads/products/' . $filename;
            } else {
                $errors[] = 'فشل حفظ الصورة على الخادم.';
            }
        }
    }

    $stock_qty     = max(0, (int)($_POST['stock_qty'] ?? 0));
    $in_stock      = isset($_POST['in_stock'])    ? 1 : 0;
    $is_new        = isset($_POST['is_new'])      ? 1 : 0;
    $is_bestseller = isset($_POST['is_bestseller'])? 1 : 0;
    $is_featured   = isset($_POST['is_featured']) ? 1 : 0;
    $is_offer      = isset($_POST['is_offer'])    ? 1 : 0;

    if (!$name)                         $errors[] = 'اسم المنتج مطلوب.';
    if (!$category)                     $errors[] = 'الفئة مطلوبة.';
    if ($price <= 0)                    $errors[] = 'السعر يجب أن يكون أكبر من صفر.';

    if (empty($errors)) {
        $slug = 'product-' . time() . '-' . rand(100, 999);

        if ($editId) {
            $db->prepare("UPDATE products SET
                name=?, category=?, subcategory=?, price=?, old_price=?, discount=?,
                short_desc=?, description=?, material=?, size=?, color=?, pieces=?,
                image_url=?, in_stock=?, stock_qty=?, is_new=?, is_bestseller=?, is_featured=?, is_offer=?
                WHERE id=?")
               ->execute([$name, $category, $subcategory, $price, $old_price, $discount,
                          $short_desc, $description, $material, $size, $color, $pieces,
                          $image_url, $in_stock, $stock_qty, $is_new, $is_bestseller, $is_featured, $is_offer,
                          $editId]);
            header('Location: products.php?msg=saved');
            exit;
        } else {
            $db->prepare("INSERT INTO products
                (name, slug, category, subcategory, price, old_price, discount,
                 short_desc, description, material, size, color, pieces,
                 image_url, in_stock, stock_qty, is_new, is_bestseller, is_featured, is_offer, created_at)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW())")
               ->execute([$name, $slug, $category, $subcategory, $price, $old_price, $discount,
                          $short_desc, $description, $material, $size, $color, $pieces,
                          $image_url, $in_stock, $stock_qty, $is_new, $is_bestseller, $is_featured, $is_offer]);
            header('Location: products.php?msg=saved');
            exit;
        }
    }
    // Keep POST data on error
    $product = array_merge($product ?? [], $_POST);
}

$cats = getCategoryList();
$isEdit = $editId > 0 && $product;

$pageTitle    = $isEdit ? 'تعديل: ' . esc($product['name']) : 'إضافة منتج جديد';
$currentPage  = 'products';
$pageSubtitle = $isEdit ? 'تعديل بيانات المنتج' : 'أضف منتج جديد لقاعدة البيانات';
$topbarActions = '<a href="products.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-right"></i> العودة للمنتجات</a>';

function val(array|null $p, string $key, string $default = ''): string {
    return esc($p[$key] ?? $default);
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

  <!-- Left: Main Info -->
  <div>

    <!-- Basic Info -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-tag" style="color:var(--primary)"></i> المعلومات الأساسية</span>
      </div>
      <div class="card-body">
        <div class="form-grid">
          <div class="form-group full">
            <label class="form-label">اسم المنتج <span style="color:var(--danger)">*</span></label>
            <input type="text" name="name" class="form-control" required
                   placeholder="مثال: طقم أواني طهي من الستانلس ستيل"
                   value="<?= val($product, 'name') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">
              الفئة <span style="color:var(--danger)">*</span>
              <a href="categories.php" style="font-size:11.5px;font-weight:500;color:var(--primary);margin-right:6px;">إدارة الفئات</a>
            </label>
            <select name="category" class="form-control" required>
              <option value="">اختر الفئة…</option>
              <?php foreach ($cats as $key => $label): ?>
                <option value="<?= $key ?>" <?= ($product['category'] ?? '') === $key ? 'selected' : '' ?>>
                  <?= $label ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">الفئة الفرعية</label>
            <input type="text" name="subcategory" class="form-control"
                   placeholder="مثال: طناجر، مقالي تيفال…"
                   value="<?= val($product, 'subcategory') ?>">
          </div>
        </div>
      </div>
    </div>

    <!-- Pricing -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-tag" style="color:var(--success)"></i> السعر</span>
      </div>
      <div class="card-body">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">السعر الحالي (EGP) <span style="color:var(--danger)">*</span></label>
            <input type="number" name="price" class="form-control" required min="0" step="0.01"
                   placeholder="0.00" value="<?= val($product, 'price') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">السعر قبل الخصم (EGP)</label>
            <input type="number" name="old_price" class="form-control" min="0" step="0.01"
                   placeholder="اتركه فارغاً إن لم يكن هناك خصم"
                   value="<?= val($product, 'old_price') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">نسبة الخصم (%)</label>
            <input type="number" name="discount" class="form-control" min="0" max="100"
                   placeholder="0" value="<?= val($product, 'discount', '0') ?>">
            <span class="form-hint">سيُحسب تلقائياً من الفارق إن لم تدخله</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Description -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-align-right" style="color:var(--info)"></i> الوصف</span>
      </div>
      <div class="card-body">
        <div class="form-grid">
          <div class="form-group full">
            <label class="form-label">وصف مختصر</label>
            <input type="text" name="short_desc" class="form-control"
                   placeholder="وصف قصير يظهر في بطاقة المنتج (جملة واحدة أو جملتين)"
                   value="<?= val($product, 'short_desc') ?>">
          </div>
          <div class="form-group full">
            <label class="form-label">الوصف الكامل</label>
            <textarea name="description" class="form-control" rows="4"
                      placeholder="اكتب وصفاً تفصيلياً للمنتج…"><?= val($product, 'description') ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- Specs -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-list-ul" style="color:var(--warning)"></i> المواصفات</span>
      </div>
      <div class="card-body">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">الخامة / المادة</label>
            <input type="text" name="material" class="form-control"
                   placeholder="ستانلس ستيل، ألومنيوم، سيراميك…"
                   value="<?= val($product, 'material') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">الحجم / المقاس</label>
            <input type="text" name="size" class="form-control"
                   placeholder="24 سم، 5 لتر، متعدد…"
                   value="<?= val($product, 'size') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">اللون</label>
            <input type="text" name="color" class="form-control"
                   placeholder="أسود، فضي، متعدد…"
                   value="<?= val($product, 'color') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">عدد القطع</label>
            <input type="number" name="pieces" class="form-control" min="1"
                   placeholder="1" value="<?= val($product, 'pieces', '1') ?>">
          </div>
        </div>
      </div>
    </div>

    <!-- Inventory -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-boxes-stacked" style="color:var(--success)"></i> المخزون</span>
      </div>
      <div class="card-body">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">الكمية المتوفرة بالمخزن</label>
            <input type="number" name="stock_qty" class="form-control" min="0" step="1"
                   placeholder="0" value="<?= val($product, 'stock_qty', '0') ?>">
            <span class="form-hint">عدد القطع الفعلي المتاح للبيع الآن</span>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Right Column -->
  <div>
    <!-- Image -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-image" style="color:var(--info)"></i> صورة المنتج</span>
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
                 value="<?= val($product, 'image_url') ?>"
                 oninput="previewImage(this.value)">
          <span class="form-hint">ضع رابط صورة المنتج (Unsplash، الموقع، أي رابط مباشر)</span>
        </div>

        <div id="imgPreviewWrap" style="margin-top:12px;display:<?= ($product['image_url'] ?? '') ? 'block' : 'none' ?>">
          <img id="imgPreview"
               src="<?= val($product, 'image_url') ?>"
               alt="معاينة"
               style="width:100%;max-height:200px;object-fit:cover;border-radius:var(--radius-sm);border:1px solid var(--border);">
        </div>
      </div>
    </div>

    <!-- Flags -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-flag" style="color:var(--primary)"></i> تمييز المنتج</span>
      </div>
      <div class="card-body">
        <div class="flags-grid">
          <label class="flag-label">
            <input type="checkbox" name="in_stock" <?= ($product['in_stock'] ?? 1) ? 'checked' : '' ?>>
            <i class="fas fa-check-circle" style="color:var(--success)"></i> متوفر في المخزن
          </label>
          <label class="flag-label">
            <input type="checkbox" name="is_new" <?= ($product['is_new'] ?? 0) ? 'checked' : '' ?>>
            🆕 جديد
          </label>
          <label class="flag-label">
            <input type="checkbox" name="is_bestseller" <?= ($product['is_bestseller'] ?? 0) ? 'checked' : '' ?>>
            ⭐ الأكثر مبيعاً
          </label>
          <label class="flag-label">
            <input type="checkbox" name="is_featured" <?= ($product['is_featured'] ?? 0) ? 'checked' : '' ?>>
            ✨ مميز
          </label>
          <label class="flag-label">
            <input type="checkbox" name="is_offer" <?= ($product['is_offer'] ?? 0) ? 'checked' : '' ?>>
            🏷️ عرض خاص
          </label>
        </div>
      </div>
    </div>

    <!-- Submit -->
    <div class="card">
      <div class="card-body">
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;font-size:15px;padding:11px;">
          <i class="fas fa-save"></i>
          <?= $isEdit ? 'حفظ التغييرات' : 'إضافة المنتج' ?>
        </button>
        <a href="products.php" class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:8px;">
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
    if (url) {
        img.src = url;
        wrap.style.display = 'block';
    } else {
        wrap.style.display = 'none';
    }
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
