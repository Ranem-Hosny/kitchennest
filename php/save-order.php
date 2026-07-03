<?php
// ============================================================
//  php/save-order.php  —  Save order to MySQL
//  POST body: multipart/form-data (order fields + optional
//  payment_proof image, since InstaPay orders need a screenshot
//  the admin can check before trusting the transaction reference)
// ============================================================

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonErr('Method not allowed', 405);

$data  = $_POST;
$items = isset($data['items']) ? json_decode($data['items'], true) : null;

$required = ['orderNum','name','phone','whatsapp','city','area','address','deliveryMethod','subtotal','shipping','total'];
foreach ($required as $f) {
    if (empty($data[$f])) jsonErr("Missing field: $f");
}
if (!is_array($items) || count($items) === 0) jsonErr('Order must have at least one item');

$orderNum      = sanitize($data['orderNum']);
$name          = sanitize($data['name']);
$phone         = sanitize($data['phone']);
$whatsapp      = sanitize($data['whatsapp']);
$city          = sanitize($data['city']);
$area          = sanitize($data['area']);
$address       = sanitize($data['address']);
$notes         = sanitize($data['notes'] ?? '');
$deliveryMethod= sanitize($data['deliveryMethod']);
$transRef      = sanitize($data['transRef'] ?? '');
$subtotal      = (float)$data['subtotal'];
$shipping      = (float)$data['shipping'];
$total         = (float)$data['total'];

// Payment proof screenshot (InstaPay transfer) — optional at the DB level,
// checkout.js enforces it client-side for delivery orders
$paymentProof = null;
if (!empty($_FILES['payment_proof']['name']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
    $allowedExt = ['jpg' => true, 'jpeg' => true, 'png' => true, 'webp' => true];
    $ext = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));

    if (isset($allowedExt[$ext]) && $_FILES['payment_proof']['size'] <= 5 * 1024 * 1024) {
        $uploadDir = __DIR__ . '/../uploads/payments/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $filename = 'pay-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $uploadDir . $filename)) {
            $paymentProof = '/uploads/payments/' . $filename;
        }
    }
}

$db = getDB();

try {
    $db->beginTransaction();

    // Insert order — payment_verified defaults to 0; an admin must confirm
    // the transfer manually from the dashboard before it counts as paid
    $stmt = $db->prepare("
        INSERT INTO orders
            (order_num, customer_name, phone, whatsapp, city, area, address, notes,
             delivery_method, shipping_fee, subtotal, total, trans_ref, payment_proof, status, created_at)
        VALUES
            (:order_num, :name, :phone, :whatsapp, :city, :area, :address, :notes,
             :delivery, :shipping, :subtotal, :total, :trans_ref, :payment_proof, 'pending', NOW())
    ");
    $stmt->execute([
        ':order_num'      => $orderNum,
        ':name'           => $name,
        ':phone'          => $phone,
        ':whatsapp'       => $whatsapp,
        ':city'           => $city,
        ':area'           => $area,
        ':address'        => $address,
        ':notes'          => $notes,
        ':delivery'       => $deliveryMethod,
        ':shipping'       => $shipping,
        ':subtotal'       => $subtotal,
        ':total'          => $total,
        ':trans_ref'      => $transRef,
        ':payment_proof'  => $paymentProof,
    ]);
    $orderId = $db->lastInsertId();

    // Insert order items
    $itemStmt = $db->prepare("
        INSERT INTO order_items (order_id, product_id, product_name, qty, unit_price, total_price)
        VALUES (:order_id, :product_id, :name, :qty, :unit_price, :total_price)
    ");
    foreach ($items as $item) {
        $itemStmt->execute([
            ':order_id'    => $orderId,
            ':product_id'  => (int)($item['id'] ?? 0),
            ':name'        => sanitize($item['name'] ?? ''),
            ':qty'         => (int)($item['qty'] ?? 1),
            ':unit_price'  => (float)($item['price'] ?? 0),
            ':total_price' => (float)(($item['price'] ?? 0) * ($item['qty'] ?? 1)),
        ]);
    }

    $db->commit();
    jsonOk(['orderId' => $orderId, 'orderNum' => $orderNum]);

} catch (PDOException $e) {
    $db->rollBack();
    jsonErr('Failed to save order: ' . $e->getMessage(), 500);
}
