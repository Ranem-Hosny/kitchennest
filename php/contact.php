<?php
// ============================================================
//  php/contact.php  —  Save contact form message to DB
//  POST: { name, phone, email, subject, message }
// ============================================================

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonErr('Method not allowed', 405);

$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!$data) {
    // Also accept form-encoded
    $data = $_POST;
}

$name    = sanitize($data['name'] ?? '');
$phone   = sanitize($data['phone'] ?? '');
$email   = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
$subject = sanitize($data['subject'] ?? '');
$message = sanitize($data['message'] ?? '');

if (!$name || !$subject || !$message) jsonErr('Name, subject, and message are required.');
if (strlen($message) < 10) jsonErr('Message must be at least 10 characters.');

$db = getDB();

$stmt = $db->prepare("
    INSERT INTO contacts (name, phone, email, subject, message, created_at)
    VALUES (:name, :phone, :email, :subject, :message, NOW())
");
$stmt->execute([
    ':name'    => $name,
    ':phone'   => $phone,
    ':email'   => $email,
    ':subject' => $subject,
    ':message' => $message,
]);

jsonOk(['message' => 'Your message has been received. We\'ll reply soon!']);
