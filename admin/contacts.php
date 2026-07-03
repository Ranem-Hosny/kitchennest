<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/admin-config.php';
require_once 'includes/auth.php';

$db = adminDB();

// Mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';
    $mid    = (int)($_POST['id'] ?? 0);
    if ($action === 'mark_read' && $mid) {
        $db->prepare('UPDATE contacts SET is_read=1 WHERE id=?')->execute([$mid]);
    } elseif ($action === 'mark_all_read') {
        $db->query('UPDATE contacts SET is_read=1');
    } elseif ($action === 'delete' && $mid) {
        $db->prepare('DELETE FROM contacts WHERE id=?')->execute([$mid]);
    }
    header('Location: contacts.php');
    exit;
}

// View single message
$viewId = (int)($_GET['view'] ?? 0);
$viewMsg = null;
if ($viewId) {
    $st = $db->prepare('SELECT * FROM contacts WHERE id=?');
    $st->execute([$viewId]);
    $viewMsg = $st->fetch();
    if ($viewMsg && !$viewMsg['is_read']) {
        $db->prepare('UPDATE contacts SET is_read=1 WHERE id=?')->execute([$viewId]);
        $viewMsg['is_read'] = 1;
    }
}

$filterRead = $_GET['filter'] ?? '';
$page_num   = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 15;
$offset     = ($page_num - 1) * $perPage;

$where  = '1=1';
$params = [];
if ($filterRead === 'unread') {
    $where .= ' AND is_read = 0';
} elseif ($filterRead === 'read') {
    $where .= ' AND is_read = 1';
}

$cnt = $db->prepare("SELECT COUNT(*) FROM contacts WHERE $where");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pages = max(1, ceil($total / $perPage));

$st = $db->prepare("SELECT * FROM contacts WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$st->execute($params);
$messages = $st->fetchAll();

$unread = (int)$db->query("SELECT COUNT(*) FROM contacts WHERE is_read=0")->fetchColumn();

$pageTitle   = 'الرسائل';
$currentPage = 'contacts';

include 'includes/layout-start.php';
?>

<div class="page-header">
  <div class="page-header-left">
    <div class="page-header-title">الرسائل</div>
    <div class="page-header-sub"><?= number_format($total) ?> رسالة <?= $unread > 0 ? "· <strong style='color:var(--danger)'>$unread غير مقروءة</strong>" : '' ?></div>
  </div>
  <?php if ($unread > 0): ?>
    <form method="POST" style="margin:0">
      <input type="hidden" name="_action" value="mark_all_read">
      <button type="submit" class="btn btn-outline btn-sm">
        <i class="fas fa-check-double"></i> تحديد الكل كمقروء
      </button>
    </form>
  <?php endif; ?>
</div>

<!-- Filter tabs -->
<div style="display:flex;gap:8px;margin-bottom:16px;">
  <a href="contacts.php" class="btn btn-sm <?= !$filterRead ? 'btn-primary' : 'btn-outline' ?>">الكل</a>
  <a href="contacts.php?filter=unread" class="btn btn-sm <?= $filterRead==='unread' ? 'btn-primary' : 'btn-outline' ?>">غير مقروء</a>
  <a href="contacts.php?filter=read"   class="btn btn-sm <?= $filterRead==='read'   ? 'btn-primary' : 'btn-outline' ?>">مقروء</a>
</div>

<div style="display:grid;grid-template-columns:<?= $viewMsg ? '380px 1fr' : '1fr' ?>;gap:20px;align-items:start;">

  <!-- Messages List -->
  <div class="card">
    <?php if (empty($messages)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">✉️</div>
        <div class="empty-state-title">لا توجد رسائل</div>
      </div>
    <?php else: ?>
      <div style="padding:8px 0;">
        <?php foreach ($messages as $m): ?>
          <a href="contacts.php?view=<?= $m['id'] ?><?= $filterRead ? '&filter='.$filterRead : '' ?>"
             style="display:flex;align-items:flex-start;gap:10px;padding:12px 16px;text-decoration:none;color:inherit;border-bottom:1px solid var(--border-light);transition:background var(--t);background:<?= ($viewId === (int)$m['id']) ? 'var(--primary-bg)' : 'transparent' ?>;"
             onmouseover="this.style.background='#FAFBFC'" onmouseout="this.style.background='<?= ($viewId === (int)$m['id']) ? 'var(--primary-bg)' : 'transparent' ?>'">
            <div style="width:36px;height:36px;border-radius:50%;background:<?= $m['is_read'] ? 'var(--bg)' : 'var(--primary-bg)' ?>;display:flex;align-items:center;justify-content:center;color:<?= $m['is_read'] ? 'var(--text-muted)' : 'var(--primary)' ?>;font-size:14px;flex-shrink:0;margin-top:2px;">
              <i class="fas fa-user"></i>
            </div>
            <div style="flex:1;min-width:0;">
              <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px;">
                <span style="font-size:13px;font-weight:<?= $m['is_read'] ? '500' : '700' ?>;"><?= esc($m['name']) ?></span>
                <?php if (!$m['is_read']): ?>
                  <span style="width:7px;height:7px;border-radius:50%;background:var(--primary);flex-shrink:0;"></span>
                <?php endif; ?>
              </div>
              <div style="font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                <?= esc($m['subject']) ?>
              </div>
              <div style="font-size:11px;color:var(--text-muted);">
                <?= date('d/m/Y H:i', strtotime($m['created_at'])) ?>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Message Detail -->
  <?php if ($viewMsg): ?>
    <div class="card" style="position:sticky;top:calc(var(--header-h) + 16px);">
      <div class="card-header">
        <span class="card-title"><i class="fas fa-envelope-open" style="color:var(--primary)"></i> تفاصيل الرسالة</span>
        <div style="display:flex;gap:6px;">
          <?php if ($viewMsg['phone']): ?>
            <a href="https://wa.me/<?= preg_replace('/\D/','',$viewMsg['phone']) ?>" target="_blank"
               class="btn btn-success btn-sm btn-icon" title="واتساب">
              <i class="fab fa-whatsapp"></i>
            </a>
          <?php endif; ?>
          <form method="POST" style="margin:0" onsubmit="return confirm('حذف هذه الرسالة؟')">
            <input type="hidden" name="_action" value="delete">
            <input type="hidden" name="id" value="<?= $viewMsg['id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm btn-icon" title="حذف">
              <i class="fas fa-trash"></i>
            </button>
          </form>
        </div>
      </div>
      <div class="card-body">
        <div class="info-grid" style="margin-bottom:16px;">
          <div class="info-row">
            <div class="info-label">المرسل</div>
            <div class="info-value" style="font-weight:700"><?= esc($viewMsg['name']) ?></div>
          </div>
          <?php if ($viewMsg['phone']): ?>
          <div class="info-row">
            <div class="info-label">الهاتف</div>
            <div class="info-value">
              <a href="tel:<?= esc($viewMsg['phone']) ?>" style="color:var(--primary)"><?= esc($viewMsg['phone']) ?></a>
            </div>
          </div>
          <?php endif; ?>
          <?php if ($viewMsg['email']): ?>
          <div class="info-row">
            <div class="info-label">البريد الإلكتروني</div>
            <div class="info-value">
              <a href="mailto:<?= esc($viewMsg['email']) ?>" style="color:var(--primary)"><?= esc($viewMsg['email']) ?></a>
            </div>
          </div>
          <?php endif; ?>
          <div class="info-row">
            <div class="info-label">التاريخ</div>
            <div class="info-value"><?= date('d/m/Y H:i', strtotime($viewMsg['created_at'])) ?></div>
          </div>
        </div>

        <div style="background:var(--bg);border-radius:var(--radius-sm);padding:14px;margin-bottom:14px;">
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:8px;">
            <?= esc($viewMsg['subject']) ?>
          </div>
          <div style="font-size:14px;line-height:1.7;color:var(--text);white-space:pre-wrap;"><?= esc($viewMsg['message']) ?></div>
        </div>

        <?php if ($viewMsg['email']): ?>
          <a href="mailto:<?= esc($viewMsg['email']) ?>?subject=رد: <?= urlencode($viewMsg['subject']) ?>"
             class="btn btn-info" style="width:100%;justify-content:center;">
            <i class="fas fa-reply"></i> رد عبر البريد الإلكتروني
          </a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

</div>

<?php include 'includes/layout-end.php'; ?>
