<?php
// ============================================================
//  php/save-content.php — Save visual-editor changes (admin only)
//  - ?action=upload  : multipart image → stores in /uploads/content, returns url
//  - default (POST JSON): upsert {page, items:[{key,value,type}]}
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'unauthorized']);
    exit;
}

$db = require __DIR__ . '/db-credentials.php';
try {
    $pdo = new PDO(
        'mysql:host='.$db['host'].';dbname='.$db['name'].';charset='.$db['charset'],
        $db['user'], $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'db error']);
    exit;
}

// ── Image upload ────────────────────────────────────────────
if (($_GET['action'] ?? '') === 'upload') {
    if (empty($_FILES['image']['name']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'لم يتم استلام الصورة']); exit;
    }
    $allowed = ['jpg' => 1, 'jpeg' => 1, 'png' => 1, 'webp' => 1, 'gif' => 1];
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (!isset($allowed[$ext]))                   { echo json_encode(['success' => false, 'message' => 'صيغة غير مدعومة']); exit; }
    if ($_FILES['image']['size'] > 5*1024*1024)   { echo json_encode(['success' => false, 'message' => 'الحجم أكبر من 5 ميجا']); exit; }
    $dir = __DIR__ . '/../uploads/content/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $fn = 'c-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $dir . $fn)) {
        echo json_encode(['success' => true, 'url' => '/uploads/content/' . $fn]);
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل حفظ الصورة']);
    }
    exit;
}

// ── Save content items ──────────────────────────────────────
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['items']) || !is_array($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'لا توجد بيانات']); exit;
}
$page = preg_replace('/[^a-z0-9_-]/i', '', $data['page'] ?? 'index');
$allowedTypes = ['text' => 1, 'image' => 1, 'section' => 1];

$up  = $pdo->prepare('INSERT INTO site_content (page, content_key, value, type) VALUES (?,?,?,?)
                      ON DUPLICATE KEY UPDATE value = VALUES(value), type = VALUES(type)');
$del = $pdo->prepare('DELETE FROM site_content WHERE page = ? AND content_key = ?');
$saved = 0;
foreach ($data['items'] as $it) {
    if (empty($it['key'])) continue;
    $key  = substr($it['key'], 0, 255);
    $type = isset($allowedTypes[$it['type'] ?? '']) ? $it['type'] : 'text';
    $val  = (string)($it['value'] ?? '');
    if (($it['reset'] ?? false) === true) { $del->execute([$page, $key]); $saved++; continue; }
    $up->execute([$page, $key, $val, $type]);
    $saved++;
}
echo json_encode(['success' => true, 'saved' => $saved]);
