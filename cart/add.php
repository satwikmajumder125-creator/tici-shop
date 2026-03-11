<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

$productId = (int)($_POST['product_id'] ?? 0);
$variantId = (int)($_POST['variant_id'] ?? 0) ?: null;
$quantity  = max(1, (int)($_POST['quantity'] ?? 1));

if (!$productId) {
    echo json_encode(['success'=>false,'message'=>'Invalid product.']);
    exit;
}

$db  = db();
$uid = $_SESSION['user_id'] ?? null;
$sid = getSessionId();

// Get product price
$stmt = $db->prepare("SELECT id, name, price, sale_price, stock FROM products WHERE id=? AND status=1");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success'=>false,'message'=>'Product not found.']);
    exit;
}
if ($product['stock'] < $quantity) {
    echo json_encode(['success'=>false,'message'=>'Insufficient stock.']);
    exit;
}

$price = $product['sale_price'] ?: $product['price'];
if ($variantId) {
    $vs = $db->prepare("SELECT price FROM product_variants WHERE id=? AND product_id=?");
    $vs->execute([$variantId, $productId]);
    $vrow = $vs->fetch();
    if ($vrow) $price = $vrow['price'];
}

// Check if already in cart
if ($uid) {
    $check = $db->prepare("SELECT id, quantity FROM cart_items WHERE user_id=? AND product_id=? AND (variant_id=? OR (variant_id IS NULL AND ? IS NULL))");
    $check->execute([$uid, $productId, $variantId, $variantId]);
} else {
    $check = $db->prepare("SELECT id, quantity FROM cart_items WHERE session_id=? AND product_id=? AND (variant_id=? OR (variant_id IS NULL AND ? IS NULL))");
    $check->execute([$sid, $productId, $variantId, $variantId]);
}
$existing = $check->fetch();

if ($existing) {
    $newQty = min($existing['quantity'] + $quantity, $product['stock']);
    $db->prepare("UPDATE cart_items SET quantity=? WHERE id=?")->execute([$newQty, $existing['id']]);
} else {
    if ($uid) {
        $db->prepare("INSERT INTO cart_items (user_id, product_id, variant_id, quantity, price) VALUES (?,?,?,?,?)")
           ->execute([$uid, $productId, $variantId, $quantity, $price]);
    } else {
        $db->prepare("INSERT INTO cart_items (session_id, product_id, variant_id, quantity, price) VALUES (?,?,?,?,?)")
           ->execute([$sid, $productId, $variantId, $quantity, $price]);
    }
}

echo json_encode(['success'=>true, 'cart_count'=>getCartCount(), 'message'=>'Added to cart']);
