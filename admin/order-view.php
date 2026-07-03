<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/admin-config.php';
require_once 'includes/auth.php';

$db  = adminDB();
$id  = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: orders.php'); exit; }

// ── Stock adjustment ────────────────────────────────────────
// Deduct stock the first time an order becomes active (confirmed/shipped/
// delivered); restore it if the order later moves back to pending/cancelled.
// The `stock_deducted` flag on the order prevents double counting.
// Note: only affects products managed from the dashboard (products table);
// demo products baked into js/data.js are not tracked here.
function adjustStockForStatus(PDO $db, array $order, string $newStatus): string {
    $active      = ['confirmed', 'shipped', 'delivered'];
    $shouldHold  = in_array($newStatus, $active, true);
    $isHeld      = (int)($order['stock_deducted'] ?? 0) === 1;

    if ($shouldHold === $isHeld) return ''; // no change needed

    $items = $db->prepare('SELECT product_id, qty FROM order_items WHERE order_id = ?');
    $items->execute([$order['id']]);
    $items = $items->fetchAll();

    $db->beginTransaction();
    try {
        if ($shouldHold) {
            // Deduct — never let stock go below zero
            $upd = $db->prepare('UPDATE products
                SET stock_qty = GREATEST(stock_qty - ?, 0),
                    in_stock  = (stock_qty - ? > 0)
                WHERE id = ?');
            foreach ($items as $it) {
                $q = (int)$it['qty'];
                if ($q > 0) $upd->execute([$q, $q, (int)$it['product_id']]);
            }
            $db->prepare('UPDATE orders SET stock_deducted = 1 WHERE id = ?')->execute([$order['id']]);
            $msg = ' وتم خصم الكميات من المخزون.';
        } else {
            // Restore
            $upd = $db->prepare('UPDATE products
                SET stock_qty = stock_qty + ?,
                    in_stock  = 1
                WHERE id = ?');
            foreach ($items as $it) {
                $q = (int)$it['qty'];
                if ($q > 0) $upd->execute([$q, (int)$it['product_id']]);
            }
            $db->prepare('UPDATE orders SET stock_deducted = 0 WHERE id = ?')->execute([$order['id']]);
            $msg = ' وتمت إعادة الكميات إلى المخزون.';
        }
        $db->commit();
        return $msg;
    } catch (Exception $e) {
        $db->rollBack();
        return ' (تعذّر تحديث المخزون)';
    }
}

$order = $db->prepare('SELECT * FROM orders WHERE id = ?');
$order->execute([$id]);
$order = $order->fetch();
if (!$order) { header('Location: orders.php'); exit; }

$items = $db->prepare('SELECT * FROM order_items WHERE order_id = ?');
$items->execute([$id]);
$items = $items->fetchAll();

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['_action'] ?? '') === 'toggle_payment') {
        $newVal = $order['payment_verified'] ? 0 : 1;
        $db->prepare('UPDATE orders SET payment_verified=?, payment_verified_at=? WHERE id=?')
           ->execute([$newVal, $newVal ? date('Y-m-d H:i:s') : null, $id]);
        $order['payment_verified']    = $newVal;
        $order['payment_verified_at'] = $newVal ? date('Y-m-d H:i:s') : null;
        $flash = $newVal ? 'success:تم تأكيد استلام الدفع.' : 'danger:تم إلغاء تأكيد الدفع.';
    } elseif (isset($_POST['status'])) {
        $newStatus = $_POST['status'];
        $allowed   = ['pending','confirmed','shipped','delivered','cancelled'];
        if (in_array($newStatus, $allowed)) {
            // Adjust stock: deduct once the order becomes active (confirmed/shipped/
            // delivered), and restore it if the order goes back to pending/cancelled.
            $stockMsg = adjustStockForStatus($db, $order, $newStatus);
            $db->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$newStatus, $id]);
            $order['status'] = $newStatus;
            $flash = 'success:تم تحديث حالة الطلب بنجاح.' . $stockMsg;
        }
    }
}

$statusLabels = [
    'pending'   => ['label' => 'قيد الانتظار', 'class' => 'badge-pending'],
    'confirmed' => ['label' => 'مؤكد',         'class' => 'badge-confirmed'],
    'shipped'   => ['label' => 'تم الشحن',     'class' => 'badge-shipped'],
    'delivered' => ['label' => 'تم التسليم',   'class' => 'badge-delivered'],
    'cancelled' => ['label' => 'ملغي',          'class' => 'badge-cancelled'],
];

$st = $statusLabels[$order['status']] ?? ['label'=>$order['status'],'class'=>''];

$pageTitle    = 'طلب #' . $order['order_num'];
$currentPage  = 'orders';
$pageSubtitle = 'تفاصيل الطلب';

$topbarActions = '<a href="orders.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-right"></i> العودة للطلبات</a>';

include 'includes/layout-start.php';
?>

<?php if ($flash): ?>
  <?php [$type, $msg] = explode(':', $flash, 2); ?>
  <div class="alert alert-<?= $type ?>">
    <i class="fas fa-<?= $type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= esc($msg) ?>
  </div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

  <!-- Main Info -->
  <div>
    <!-- Customer Info -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-user" style="color:var(--primary)"></i> بيانات العميل</span>
        <div style="display:flex;gap:6px;">
          <span class="badge <?= $st['class'] ?>"><?= $st['label'] ?></span>
          <?php if ($order['delivery_method'] === 'delivery'): ?>
            <?php if ($order['payment_verified']): ?>
              <span class="badge badge-instock"><i class="fas fa-check-circle"></i> الدفع محقق</span>
            <?php else: ?>
              <span class="badge badge-lowstock"><i class="fas fa-triangle-exclamation"></i> الدفع غير محقق</span>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body">
        <div class="info-grid">
          <div class="info-row">
            <div class="info-label">الاسم</div>
            <div class="info-value"><?= esc($order['customer_name']) ?></div>
          </div>
          <div class="info-row">
            <div class="info-label">الهاتف</div>
            <div class="info-value">
              <a href="tel:<?= esc($order['phone']) ?>" style="color:var(--primary);text-decoration:none;">
                <?= esc($order['phone']) ?>
              </a>
            </div>
          </div>
          <div class="info-row">
            <div class="info-label">واتساب</div>
            <div class="info-value">
              <a href="https://wa.me/<?= preg_replace('/\D/','',$order['whatsapp']) ?>" target="_blank"
                 style="color:#25D366;text-decoration:none;">
                <i class="fab fa-whatsapp"></i> <?= esc($order['whatsapp']) ?>
              </a>
            </div>
          </div>
          <div class="info-row">
            <div class="info-label">رقم الطلب</div>
            <div class="info-value" style="font-weight:800;color:var(--primary)">#<?= esc($order['order_num']) ?></div>
          </div>
          <div class="info-row">
            <div class="info-label">المدينة</div>
            <div class="info-value"><?= esc($order['city']) ?></div>
          </div>
          <div class="info-row">
            <div class="info-label">المنطقة</div>
            <div class="info-value"><?= esc($order['area']) ?></div>
          </div>
          <div class="info-row full" style="grid-column:1/-1">
            <div class="info-label">العنوان</div>
            <div class="info-value"><?= esc($order['address']) ?></div>
          </div>
          <?php if ($order['notes']): ?>
          <div class="info-row" style="grid-column:1/-1">
            <div class="info-label">ملاحظات</div>
            <div class="info-value"><?= esc($order['notes']) ?></div>
          </div>
          <?php endif; ?>
          <?php if ($order['trans_ref']): ?>
          <div class="info-row">
            <div class="info-label">مرجع إنستاباي</div>
            <div class="info-value" style="font-family:monospace"><?= esc($order['trans_ref']) ?></div>
          </div>
          <?php endif; ?>
          <div class="info-row">
            <div class="info-label">طريقة التوصيل</div>
            <div class="info-value"><?= $order['delivery_method'] === 'pickup' ? '🏪 استلام من المحل' : '🚚 توصيل للمنزل' ?></div>
          </div>
          <div class="info-row">
            <div class="info-label">تاريخ الطلب</div>
            <div class="info-value"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Order Items -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-list" style="color:var(--info)"></i> المنتجات المطلوبة</span>
      </div>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>المنتج</th>
              <th>الكمية</th>
              <th>سعر الوحدة</th>
              <th>الإجمالي</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
              <tr>
                <td style="font-weight:600"><?= esc($item['product_name']) ?></td>
                <td><?= (int)$item['qty'] ?></td>
                <td><?= number_format($item['unit_price']) ?> EGP</td>
                <td style="font-weight:700"><?= number_format($item['total_price']) ?> EGP</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Right: Summary + Status Update -->
  <div>
    <?php if ($order['delivery_method'] === 'delivery'): ?>
    <!-- Payment Verification -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-shield-halved" style="color:<?= $order['payment_verified'] ? 'var(--success)' : 'var(--warning)' ?>"></i> التحقق من الدفع</span>
      </div>
      <div class="card-body">
        <?php if ($order['trans_ref']): ?>
          <div class="info-row" style="margin-bottom:10px;">
            <div class="info-label">مرجع المعاملة</div>
            <div class="info-value" style="font-family:monospace"><?= esc($order['trans_ref']) ?></div>
          </div>
        <?php endif; ?>

        <?php if ($order['payment_proof']): ?>
          <a href="<?= esc($order['payment_proof']) ?>" target="_blank">
            <img src="<?= esc($order['payment_proof']) ?>" alt="إثبات التحويل"
                 style="width:100%;border-radius:var(--radius-sm);border:1px solid var(--border);margin-bottom:12px;">
          </a>
        <?php else: ?>
          <div class="empty-state" style="padding:20px;">
            <div class="empty-state-sub">لم يرفق العميل صورة إثبات تحويل.</div>
          </div>
        <?php endif; ?>

        <?php if ($order['payment_verified']): ?>
          <div class="alert alert-success" style="margin-bottom:12px;">
            <i class="fas fa-check-circle"></i>
            تم التأكيد في <?= date('d/m/Y H:i', strtotime($order['payment_verified_at'])) ?>
          </div>
        <?php endif; ?>

        <form method="POST">
          <input type="hidden" name="_action" value="toggle_payment">
          <button type="submit" class="btn <?= $order['payment_verified'] ? 'btn-outline' : 'btn-primary' ?>" style="width:100%;justify-content:center;">
            <?php if ($order['payment_verified']): ?>
              <i class="fas fa-rotate-left"></i> إلغاء تأكيد الدفع
            <?php else: ?>
              <i class="fas fa-check"></i> تأكيد استلام الدفع
            <?php endif; ?>
          </button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <!-- Order Summary -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-receipt" style="color:var(--success)"></i> ملخص الطلب</span>
      </div>
      <div class="card-body">
        <div style="display:flex;flex-direction:column;gap:10px;">
          <div style="display:flex;justify-content:space-between;font-size:13px;">
            <span style="color:var(--text-secondary)">المجموع الفرعي</span>
            <span style="font-weight:600"><?= number_format($order['subtotal']) ?> EGP</span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:13px;">
            <span style="color:var(--text-secondary)">رسوم الشحن</span>
            <span style="font-weight:600"><?= number_format($order['shipping_fee']) ?> EGP</span>
          </div>
          <hr class="divider">
          <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;">
            <span>الإجمالي</span>
            <span style="color:var(--primary)"><?= number_format($order['total']) ?> EGP</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Status Update -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-edit" style="color:var(--warning)"></i> تحديث الحالة</span>
      </div>
      <div class="card-body">
        <form method="POST">
          <div class="form-group" style="margin-bottom:14px;">
            <label class="form-label">الحالة الجديدة</label>
            <select name="status" class="form-control">
              <?php foreach ($statusLabels as $key => $info): ?>
                <option value="<?= $key ?>" <?= $order['status'] === $key ? 'selected' : '' ?>>
                  <?= $info['label'] ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
            <i class="fas fa-save"></i> حفظ التغيير
          </button>
        </form>

        <hr class="divider">

        <a href="https://wa.me/<?= preg_replace('/\D/','',$order['whatsapp']) ?>?text=<?= urlencode('مرحباً '.$order['customer_name'].'، بخصوص طلبك رقم #'.$order['order_num']) ?>"
           target="_blank" class="btn btn-success" style="width:100%;justify-content:center;">
          <i class="fab fa-whatsapp"></i> تواصل عبر واتساب
        </a>
      </div>
    </div>
  </div>

</div>

<?php include 'includes/layout-end.php'; ?>
