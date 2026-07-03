<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/admin-config.php';
require_once 'includes/auth.php';

$db = adminDB();
$flash = '';

// Change admin password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'change_password') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $st = $db->prepare('SELECT password_hash FROM admin_users WHERE id=?');
    $st->execute([$_SESSION['admin_id']]);
    $row = $st->fetch();
    if (!$row || !password_verify($current, $row['password_hash'])) {
        $flash = 'error:كلمة المرور الحالية غير صحيحة.';
    } elseif (strlen($new) < 6) {
        $flash = 'error:كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل.';
    } elseif ($new !== $confirm) {
        $flash = 'error:كلمتا المرور الجديدتان غير متطابقتين.';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $db->prepare('UPDATE admin_users SET password_hash=? WHERE id=?')->execute([$hash, $_SESSION['admin_id']]);
        $flash = 'success:تم تغيير كلمة المرور بنجاح.';
    }
}

// Save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'save_settings') {
    $fields = ['store_name','store_email','store_phone','store_address',
               'whatsapp_number','instapay_number','shipping_fee','offer_hours',
               'social_instagram','social_facebook','social_tiktok'];
    try {
        $upsert = $db->prepare("INSERT INTO settings (`key`, value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)");
        foreach ($fields as $field) {
            $val = trim($_POST[$field] ?? '');
            $upsert->execute([$field, $val]);
        }
        $flash = 'success:تم حفظ الإعدادات بنجاح.';
    } catch (Exception $e) {
        $flash = 'error:حدث خطأ أثناء الحفظ.';
    }
}

// Load settings
$defaults = [
    'store_name'        => 'بيت العوضي',
    'store_email'       => 'info@beitAlawady.com',
    'store_phone'       => '+20 155 167 7016',
    'store_address'     => 'القاهرة، مصر',
    'whatsapp_number'   => '201551677016',
    'instapay_number'   => '01551677016',
    'shipping_fee'      => '50',
    'offer_hours'       => '24',
    'social_instagram'  => '#',
    'social_facebook'   => '#',
    'social_tiktok'     => '#',
];

$settings = $defaults;
try {
    $rows = $db->query('SELECT `key`, value FROM settings')->fetchAll();
    foreach ($rows as $row) {
        $settings[$row['key']] = $row['value'];
    }
} catch (Exception $e) {}

function sv(array $s, string $k): string {
    return htmlspecialchars($s[$k] ?? '', ENT_QUOTES, 'UTF-8');
}

$pageTitle   = 'إعدادات المتجر';
$currentPage = 'settings';

include 'includes/layout-start.php';
?>

<?php if ($flash): ?>
  <?php [$type, $msg] = explode(':', $flash, 2); ?>
  <div class="alert alert-<?= $type === 'success' ? 'success' : 'danger' ?>">
    <i class="fas fa-<?= $type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= esc($msg) ?>
  </div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

  <!-- Store Settings -->
  <div>
    <form method="POST">
      <input type="hidden" name="_action" value="save_settings">

      <div class="card">
        <div class="card-header">
          <span class="card-title"><i class="fas fa-store" style="color:var(--primary)"></i> بيانات المتجر</span>
        </div>
        <div class="card-body">
          <div class="form-grid">
            <div class="form-group full">
              <label class="form-label">اسم المتجر</label>
              <input type="text" name="store_name" class="form-control" value="<?= sv($settings,'store_name') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">البريد الإلكتروني</label>
              <input type="email" name="store_email" class="form-control" value="<?= sv($settings,'store_email') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">رقم الهاتف</label>
              <input type="text" name="store_phone" class="form-control" value="<?= sv($settings,'store_phone') ?>">
            </div>
            <div class="form-group full">
              <label class="form-label">العنوان</label>
              <input type="text" name="store_address" class="form-control" value="<?= sv($settings,'store_address') ?>">
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <span class="card-title"><i class="fab fa-whatsapp" style="color:#25D366"></i> التواصل والدفع</span>
        </div>
        <div class="card-body">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">رقم واتساب الطلبات</label>
              <input type="text" name="whatsapp_number" class="form-control"
                     placeholder="201XXXXXXXXX"
                     value="<?= sv($settings,'whatsapp_number') ?>">
              <span class="form-hint">مع كود الدولة بدون + مثال: 201551677016</span>
            </div>
            <div class="form-group">
              <label class="form-label">رقم إنستاباي</label>
              <input type="text" name="instapay_number" class="form-control"
                     placeholder="01XXXXXXXXX"
                     value="<?= sv($settings,'instapay_number') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">رسوم الشحن (EGP)</label>
              <input type="number" name="shipping_fee" class="form-control" min="0" step="0.01"
                     value="<?= sv($settings,'shipping_fee') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">مدة عدّاد العروض (بالساعات)</label>
              <input type="number" name="offer_hours" class="form-control" min="1" max="720"
                     value="<?= sv($settings,'offer_hours') ?: '24' ?>">
              <span class="form-hint">العدّاد التنازلي في صفحة العروض يبدأ من هذا العدد من الساعات.</span>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <span class="card-title"><i class="fas fa-share-alt" style="color:var(--info)"></i> وسائل التواصل</span>
        </div>
        <div class="card-body">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label"><i class="fab fa-instagram" style="color:#E1306C"></i> إنستاغرام</label>
              <input type="text" name="social_instagram" class="form-control"
                     placeholder="https://instagram.com/..."
                     value="<?= sv($settings,'social_instagram') ?>">
            </div>
            <div class="form-group">
              <label class="form-label"><i class="fab fa-facebook" style="color:#1877F2"></i> فيسبوك</label>
              <input type="text" name="social_facebook" class="form-control"
                     placeholder="https://facebook.com/..."
                     value="<?= sv($settings,'social_facebook') ?>">
            </div>
            <div class="form-group">
              <label class="form-label"><i class="fab fa-tiktok"></i> تيك توك</label>
              <input type="text" name="social_tiktok" class="form-control"
                     placeholder="https://tiktok.com/..."
                     value="<?= sv($settings,'social_tiktok') ?>">
            </div>
          </div>
        </div>
      </div>

      <div class="form-actions" style="border-top:none;padding-top:0;">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> حفظ الإعدادات
        </button>
      </div>
    </form>
  </div>

  <!-- Password Change + Info -->
  <div>
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-lock" style="color:var(--warning)"></i> تغيير كلمة المرور</span>
      </div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="_action" value="change_password">
          <div class="form-group" style="margin-bottom:12px;">
            <label class="form-label">كلمة المرور الحالية</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
          <div class="form-group" style="margin-bottom:12px;">
            <label class="form-label">كلمة المرور الجديدة</label>
            <input type="password" name="new_password" class="form-control" required minlength="6">
          </div>
          <div class="form-group" style="margin-bottom:16px;">
            <label class="form-label">تأكيد كلمة المرور الجديدة</label>
            <input type="password" name="confirm_password" class="form-control" required minlength="6">
          </div>
          <button type="submit" class="btn btn-warning" style="width:100%;justify-content:center;">
            <i class="fas fa-key"></i> تغيير كلمة المرور
          </button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-info-circle" style="color:var(--info)"></i> معلومات النظام</span>
      </div>
      <div class="card-body">
        <div style="display:flex;flex-direction:column;gap:10px;">
          <div class="info-row">
            <div class="info-label">المستخدم الحالي</div>
            <div class="info-value"><?= esc($_SESSION['admin_user'] ?? '-') ?></div>
          </div>
          <div class="info-row">
            <div class="info-label">قاعدة البيانات</div>
            <div class="info-value"><?= ADMIN_DB_NAME ?></div>
          </div>
          <div class="info-row">
            <div class="info-label">PHP Version</div>
            <div class="info-value"><?= PHP_VERSION ?></div>
          </div>
        </div>
        <hr class="divider">
        <a href="setup.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;">
          <i class="fas fa-database"></i> إعادة تشغيل إعداد قاعدة البيانات
        </a>
      </div>
    </div>

    <div class="card" style="border:1px solid #FED7AA;background:#FFFBF5;">
      <div class="card-header" style="border-color:#FED7AA;">
        <span class="card-title" style="color:var(--warning)"><i class="fas fa-exclamation-triangle"></i> ملاحظة مهمة</span>
      </div>
      <div class="card-body" style="font-size:12.5px;color:var(--text-secondary);line-height:1.8;">
        الإعدادات المحفوظة هنا تُستخدم بواسطة لوحة التحكم فقط حالياً.
        لتفعيلها في الموقع، يمكنك تحديث ملفي:
        <code style="background:var(--bg);padding:2px 6px;border-radius:4px;font-size:12px;">js/config.js</code>
        و
        <code style="background:var(--bg);padding:2px 6px;border-radius:4px;font-size:12px;">php/config.php</code>
        بنفس القيم.
      </div>
    </div>
  </div>

</div>

<?php include 'includes/layout-end.php'; ?>
