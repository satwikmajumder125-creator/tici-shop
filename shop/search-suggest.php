<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { echo json_encode(['suggestions'=>[],'products'=>[]]); exit; }

$db = db();
$like = '%' . $q . '%';

// ── Product name suggestions (distinct partial matches) ───────
$sug = $db->prepare("
    SELECT DISTINCT name FROM products
    WHERE status = 1 AND name LIKE ?
    ORDER BY
        CASE WHEN name LIKE ? THEN 0 ELSE 1 END,
        name
    LIMIT 6
");
$sug->execute([$like, $q . '%']);
$suggestions = $sug->fetchAll(PDO::FETCH_COLUMN);

// ── Actual product results with image + price ─────────────────
$prod = $db->prepare("
    SELECT p.id, p.name, p.slug, p.price, p.sale_price,
           COALESCE(pi.image_path, pi.image, '') AS thumb
    FROM products p
    LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
    WHERE p.status = 1 AND (p.name LIKE ? OR p.short_description LIKE ? OR p.sku LIKE ?)
    ORDER BY
        CASE WHEN p.name LIKE ? THEN 0 ELSE 1 END,
        p.featured DESC, p.name
    LIMIT 5
");
$prod->execute([$like, $like, $like, $q . '%']);
$products = $prod->fetchAll();

// Resolve image URLs
foreach ($products as &$p) {
    $p['thumb']     = imgUrl($p['thumb']);
    $p['price_fmt'] = '₹' . number_format((float)($p['sale_price'] ?: $p['price']), 2);
    $p['url']       = SITE_URL . '/shop/product.php?slug=' . urlencode($p['slug']);
}
unset($p);

echo json_encode([
    'suggestions' => $suggestions,
    'products'    => $products,
], JSON_UNESCAPED_UNICODE);
