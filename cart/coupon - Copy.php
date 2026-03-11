<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

$code = strtoupper(trim($_POST['code'] ?? ''));
if (!$code) { echo json_encode(['success'=>false,'message'=>'Enter a coupon code.']); exit; }

$db  = db();
$uid = $_SESSION['user_id'] ?? null;
$sid = getSessionId();

// Cart subtotal
if ($uid) {
    $s = $db->prepare("SELECT COALESCE(SUM(COALESCE(pv.price,ci.price)*ci.quantity),0) FROM cart_items ci LEFT JOIN product_variants pv ON pv.id=ci.variant_id WHERE ci.user_id=?");
    $s->execute([$uid]);
} else {
    $s = $db->prepare("SELECT COALESCE(SUM(COALESCE(pv.price,ci.price)*ci.quantity),0) FROM cart_items ci LEFT JOIN product_variants pv ON pv.id=ci.variant_id WHERE ci.session_id=?");
    $s->execute([$sid]);
}
$subtotal = (float)$s->fetchColumn();

// Validate coupon
$cs = $db->prepare("SELECT * FROM coupons WHERE code=? AND status=1 AND (expiry_date IS NULL OR expiry_date >= CURDATE()) AND (usage_limit IS NULL OR used_count < usage_limit)");
$cs->execute([$code]);
$coupon = $cs->fetch();

if (!$coupon) {
    echo json_encode(['success'=>false,'message'=>'Invalid or expired coupon code.']); exit;
}
if ($coupon['minimum_order'] > $subtotal) {
    echo json_encode(['success'=>false,'message'=>'Minimum order ₹'.number_format($coupon['minimum_order'],2).' required for this coupon.']); exit;
}

// Calculate discount
if ($coupon['discount_type'] === 'percentage') {
    $discount = $subtotal * $coupon['discount_value'] / 100;
} else {
    $discount = min($coupon['discount_value'], $subtotal);
}

$shipping = ($subtotal - $discount >= 999) ? 0 : 120;
$total    = max(0, $subtotal - $discount + $shipping);

$_SESSION['cart_discount']  = $discount;
$_SESSION['coupon_code']    = $code;
$_SESSION['coupon_id']      = $coupon['id'];

echo json_encode([
    'success'  => true,
    'message'  => $coupon['discount_type'] === 'percentage'
                    ? $coupon['discount_value'].'% off applied! You saved '.price($discount)
                    : '₹'.(int)$discount.' off applied!',
    'subtotal' => $subtotal,
    'discount' => $discount,
    'shipping' => $shipping,
    'total'    => $total,
]);

function price($p) { return '₹'.number_format($p,2); }
