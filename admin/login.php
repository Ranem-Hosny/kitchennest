<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'includes/admin-config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        try {
            $db  = adminDB();
            $st  = $db->prepare('SELECT id, password_hash FROM admin_users WHERE username = ? LIMIT 1');
            $st->execute([$username]);
            $row = $st->fetch();
            if ($row && password_verify($password, $row['password_hash'])) {
                $_SESSION['admin_id']   = $row['id'];
                $_SESSION['admin_user'] = $username;
                header('Location: index.php');
                exit;
            } else {
                $error = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
            }
        } catch (Exception $e) {
            $error = 'خطأ في قاعدة البيانات. تأكد من تشغيل setup.php أولاً.';
        }
    } else {
        $error = 'يرجى إدخال اسم المستخدم وكلمة المرور.';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تسجيل الدخول — بيت العوضي</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="login-body">

  <div class="login-card">
    <div class="login-logo">
      <div class="login-logo-icon">🏠</div>
      <div class="login-logo-name">بيت العوضي</div>
      <div class="login-logo-sub">لوحة تحكم المتجر</div>
    </div>

    <div class="login-title">تسجيل الدخول</div>

    <?php if ($error): ?>
      <div class="alert alert-danger" style="margin-bottom:14px;">
        <i class="fas fa-exclamation-circle"></i> <?= esc($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="login-group">
        <label class="login-label">اسم المستخدم</label>
        <input type="text" name="username" class="login-input" placeholder="admin"
               value="<?= esc($_POST['username'] ?? '') ?>" required autofocus>
      </div>
      <div class="login-group">
        <label class="login-label">كلمة المرور</label>
        <input type="password" name="password" class="login-input" placeholder="••••••••" required>
      </div>
      <button type="submit" class="login-btn">
        دخول إلى لوحة التحكم
      </button>
    </form>

    <p style="text-align:center;margin-top:18px;font-size:12px;color:#9CA3AF;">
      كلمة المرور الافتراضية: <strong>admin123</strong>
      &nbsp;·&nbsp;
      <a href="setup.php" style="color:#FF6B00;">إعداد قاعدة البيانات</a>
    </p>
  </div>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</body>
</html>
