<?php
// Buffer ALL output so any PHP warnings don't corrupt JSON
ob_start();
require_once __DIR__ . '/../includes/config.php';
ob_end_clean(); // discard any buffered warnings

header('Content-Type: application/json');

function jsonOut($arr) { echo json_encode($arr); exit; }
function fmt($p) { return '₹'.number_format((float)$p, 2); }

$code = strtoupper(trim($_POST['code'] ?? ''));

// ── REMOVE action ────────────────────────────────────────────
if (($_POST['action'] ?? '') === 'remove') {
    unset($_SESSION['cart_discount'], $_SESSION['coupon_code'], $_SESSION['coupon_id']);

    // Recalculate subtotal for accurate totals
    $db  = db();
    $uid = $_SESSION['user_id'] ?? null;
    $sid = getSessionId();
    try {
        if ($uid) {
            $s = $db->prepare("SELECT COALESCE(SUM(COALESCE(pv.price,ci.price,0)*ci.quantity),0) FROM cart_items ci LEFT JOIN product_variants pv ON pv.id=ci.variant_id WHERE ci.user_id=?");
            $s->execute([$uid]);
        } else {
            $s = $db->prepare("SELECT COALESCE(SUM(COALESCE(pv.price,ci.price,0)*ci.quantity),0) FROM cart_items ci LEFT JOIN product_variants pv ON pv.id=ci.variant_id WHERE ci.session_id=?");
            $s->execute([$sid]);
        }
        $subtotal = (float)$s->fetchColumn();
    } catch (Exception $e) { $subtotal = 0; }

    $shipping = $subtotal >= 999 ? 0 : ($subtotal > 0 ? 120 : 0);
    echo json_encode([
        'success'  => true,
        'subtotal' => $subtotal,
        'discount' => 0,
        'shipping' => $shipping,
        'total'    => $subtotal + $shipping,
    ]);
    exit;
}

if (!$code) jsonOut(['success'=>false,'message'=>'Please enter a coupon code.']);

$db  = db();
$uid = $_SESSION['user_id'] ?? null;
$sid = getSessionId();

// ── Cart subtotal ────────────────────────────────────────────
try {
    if ($uid) {
        $s = $db->prepare("SELECT COALESCE(SUM(COALESCE(pv.price, ci.price, 0) * ci.quantity), 0)
                           FROM cart_items ci
                           LEFT JOIN product_variants pv ON pv.id = ci.variant_id
                           WHERE ci.user_id = ?");
        $s->execute([$uid]);
    } else {
        $s = $db->prepare("SELECT COALESCE(SUM(COALESCE(pv.price, ci.price, 0) * ci.quantity), 0)
                           FROM cart_items ci
                           LEFT JOIN product_variants pv ON pv.id = ci.variant_id
                           WHERE ci.session_id = ?");
        $s->execute([$sid]);
    }
    $subtotal = (float)$s->fetchColumn();
} catch (Exception $e) {
    jsonOut(['success'=>false,'message'=>'Could not read cart. Please refresh.']);
}

// ── Validate coupon ──────────────────────────────────────────
try {
    // Check if coupon_usage table exists
    $hasUsageTable = false;
    try {
        $db->query("SELECT 1 FROM coupon_usage LIMIT 1");
        $hasUsageTable = true;
    } catch (Exception $e) {}

    if ($hasUsageTable) {
        $cs = $db->prepare("
            SELECT c.*,
                   (SELECT COUNT(*) FROM coupon_usage WHERE coupon_id = c.id) AS used_count
            FROM coupons c
            WHERE UPPER(c.code) = ?
              AND c.status = 1
              AND (c.expiry_date IS NULL OR c.expiry_date = '' OR c.expiry_date >= CURDATE())
        ");
    } else {
        $cs = $db->prepare("
            SELECT c.*, 0 AS used_count
            FROM coupons c
            WHERE UPPER(c.code) = ?
              AND c.status = 1
              AND (c.expiry_date IS NULL OR c.expiry_date = '' OR c.expiry_date >= CURDATE())
        ");
    }
    $cs->execute([$code]);
    $coupon = $cs->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    jsonOut(['success'=>false,'message'=>'Coupon lookup failed: '.$e->getMessage()]);
}

if (!$coupon) {
    jsonOut(['success'=>false,'message'=>'Invalid or expired coupon code.']);
}

// Usage limit check
if (!empty($coupon['usage_limit']) && (int)($coupon['used_count'] ?? 0) >= (int)$coupon['usage_limit']) {
    jsonOut(['success'=>false,'message'=>'This coupon has reached its usage limit.']);
}

// Minimum order check
$minOrder = (float)($coupon['minimum_order'] ?? 0);
if ($minOrder > 0 && $subtotal < $minOrder) {
    jsonOut(['success'=>false,'message'=>'Minimum order '.fmt($minOrder).' required for this coupon.']);
}

// ── Calculate discount ───────────────────────────────────────
$discType  = strtolower($coupon['discount_type'] ?? 'percentage');
$discValue = (float)($coupon['discount_value'] ?? 0);

if ($discType === 'percentage') {
    $discount = round($subtotal * $discValue / 100, 2);
} else {
    $discount = min($discValue, $subtotal);
}

$afterDiscount = max(0, $subtotal - $discount);
$shipping      = $afterDiscount >= 999 ? 0 : 120;
$total         = $afterDiscount + $shipping;

// Save to session
$_SESSION['cart_discount'] = $discount;
$_SESSION['coupon_code']   = $code;
$_SESSION['coupon_id']     = $coupon['id'];

$msg = $discType === 'percentage'
    ? $discValue.'% discount applied! You saved '.fmt($discount)
    : fmt($discount).' off applied!';

jsonOut([
    'success'  => true,
    'message'  => $msg,
    'subtotal' => $subtotal,
    'discount' => $discount,
    'shipping' => $shipping,
    'total'    => $total,
]);
