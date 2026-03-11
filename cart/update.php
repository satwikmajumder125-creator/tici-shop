<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

$itemId   = (int)($_POST['item_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 0);

if (!$itemId) { echo json_encode(['success'=>false,'message'=>'Invalid item.']); exit; }

$db  = db();
$uid = $_SESSION['user_id'] ?? null;
$sid = getSessionId();

// Verify ownership
if ($uid) {
    $check = $db->prepare("SELECT id FROM cart_items WHERE id=? AND user_id=?");
    $check->execute([$itemId, $uid]);
} else {
    $check = $db->prepare("SELECT id FROM cart_items WHERE id=? AND session_id=?");
    $check->execute([$itemId, $sid]);
}
if (!$check->fetch()) { echo json_encode(['success'=>false,'message'=>'Not found.']); exit; }

if ($quantity <= 0) {
    $db->prepare("DELETE FROM cart_items WHERE id=?")->execute([$itemId]);
} else {
    $db->prepare("UPDATE cart_items SET quantity=? WHERE id=?")->execute([$quantity, $itemId]);
}

// Recalculate totals
if ($uid) {
    $items = $db->prepare("SELECT ci.quantity, COALESCE(pv.price, ci.price) AS unit_price FROM cart_items ci LEFT JOIN product_variants pv ON pv.id=ci.variant_id WHERE ci.user_id=?");
    $items->execute([$uid]);
} else {
    $items = $db->prepare("SELECT ci.quantity, COALESCE(pv.price, ci.price) AS unit_price FROM cart_items ci LEFT JOIN product_variants pv ON pv.id=ci.variant_id WHERE ci.session_id=?");
    $items->execute([$sid]);
}
$rows = $items->fetchAll();
$subtotal = array_sum(array_map(fn($r) => $r['unit_price'] * $r['quantity'], $rows));
$discount = (float)($_SESSION['cart_discount'] ?? 0);
$shipping = $subtotal > 0 ? ($subtotal - $discount >= 999 ? 0 : 120) : 0;
$total    = max(0, $subtotal - $discount + $shipping);

echo json_encode([
    'success'    => true,
    'cart_count' => getCartCount(),
    'subtotal'   => $subtotal,
    'shipping'   => $shipping,
    'discount'   => $discount,
    'total'      => $total,
]);
