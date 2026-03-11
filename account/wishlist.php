<?php
require_once __DIR__ . '/../includes/config.php';
if (!isLoggedIn()) { header('Location: ' . SITE_URL . '/account/login.php'); exit; }
$pageTitle   = 'Wishlist — TiCi NatureLab';
$accountPage = 'wishlist';
$db  = db();
$uid = $_SESSION['user_id'];

$items = $db->prepare("SELECT p.*, COALESCE(pi.image_path, pi.image, '') AS thumb, pp.difficulty, pp.light_requirement, c.name AS category_name FROM wishlists w JOIN products p ON p.id=w.product_id LEFT JOIN product_images pi ON pi.product_id=p.id AND pi.is_primary=1 LEFT JOIN plant_properties pp ON pp.product_id=p.id LEFT JOIN categories c ON c.id=p.category_id WHERE w.user_id=? ORDER BY w.created_at DESC");
$items->execute([$uid]); $items=$items->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding:32px 20px 60px">
  <div class="account-layout">
    <?php include __DIR__ . '/../includes/account-nav.php'; ?>
    <main>
      <h1 style="font-family:var(--font-display);font-size:1.8rem;color:var(--green-dark);margin-bottom:24px">
        ❤️ My Wishlist <span style="font-size:1rem;font-weight:400;color:var(--text-muted)">(<?= count($items) ?> items)</span>
      </h1>

      <?php if (empty($items)): ?>
      <div class="empty-state">
        <div class="empty-icon">💚</div>
        <h3>Your wishlist is empty</h3>
        <p>Save plants you love to come back to them later!</p>
        <a href="<?= SITE_URL ?>/shop/index.php" class="btn btn-primary" style="margin-top:16px">Browse Plants</a>
      </div>
      <?php else: ?>
      <div class="products-grid">
        <?php foreach ($items as $p):
          $isInWishlist = true; // already in wishlist page
          $currentPrice = $p['sale_price'] ?: $p['price'];
          $hasDiscount  = $p['sale_price'] && $p['sale_price'] < $p['price'];
          $discPct      = $hasDiscount ? discount($p['price'], $p['sale_price']) : 0;
          $inStock      = (int)$p['stock'] > 0;
        ?>
        <?php include __DIR__ . '/../includes/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </main>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
