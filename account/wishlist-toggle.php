<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success'=>false,'redirect'=> SITE_URL . '/account/login.php']);
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
if (!$productId) { echo json_encode(['success'=>false,'message'=>'Invalid product.']); exit; }

$db  = db();
$uid = $_SESSION['user_id'];

$check = $db->prepare("SELECT id FROM wishlists WHERE user_id=? AND product_id=?");
$check->execute([$uid, $productId]);
$existing = $check->fetch();

if ($existing) {
    $db->prepare("DELETE FROM wishlists WHERE user_id=? AND product_id=?")->execute([$uid, $productId]);
    $added = false;
} else {
    $db->prepare("INSERT IGNORE INTO wishlists (user_id, product_id) VALUES (?,?)")->execute([$uid, $productId]);
    $added = true;
}

$count = $db->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id=?");
$count->execute([$uid]);

echo json_encode(['success'=>true, 'added'=>$added, 'count'=>(int)$count->fetchColumn()]);
