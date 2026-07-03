<?php
// ============================================================
//  admin/ajax-status.php — Lightweight polling endpoint
//  Returns current pending-order / unread-message counts and
//  the latest order id, so the dashboard can toast new orders.
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/admin-config.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false]);
    exit;
}

$db = adminDB();

try {
    $pendingOrders = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
    $unreadMessages = (int)$db->query("SELECT COUNT(*) FROM contacts WHERE is_read=0")->fetchColumn();
    $latest = $db->query("SELECT id, order_num, customer_name, total FROM orders ORDER BY id DESC LIMIT 1")->fetch();
} catch (Exception $e) {
    echo json_encode(['success' => false]);
    exit;
}

echo json_encode([
    'success'        => true,
    'pendingOrders'  => $pendingOrders,
    'unreadMessages' => $unreadMessages,
    'latestOrderId'  => $latest['id'] ?? 0,
    'latestOrderNum' => $latest['order_num'] ?? '',
    'latestCustomer' => $latest['customer_name'] ?? '',
    'latestTotal'    => $latest['total'] ?? 0,
]);
