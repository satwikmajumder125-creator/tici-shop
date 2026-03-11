<?php
// Reusable product card partial
// Expects $p = product row with: id, name, slug, price, sale_price, stock, thumb,
//   featured, bestseller, new_arrival, difficulty, light_requirement, category_name (optional)
$isInWishlist = false;
if (isLoggedIn() && !empty($p['id'])) {
    $wq = db()->prepare("SELECT 1 FROM wishlists WHERE user_id=? AND product_id=?");
    $wq->execute([$_SESSION['user_id'], $p['id']]);
    $isInWishlist = (bool)$wq->fetchColumn();
}
$currentPrice = !empty($p['sale_price']) ? $p['sale_price'] : $p['price'];
$hasDiscount  = !empty($p['sale_price']) && $p['sale_price'] < $p['price'];
$discPct      = $hasDiscount ? discount($p['price'], $p['sale_price']) : 0;
$inStock      = (int)$p['stock'] > 0;
?>
<div class="product-card">
  <a href="<?= SITE_URL ?>/shop/product.php?slug=<?= h($p['slug']) ?>" class="product-img-wrap">
    <?php if (!empty($p['thumb'])): ?>
      <img src="<?= h(imgUrl($p['thumb'])) ?>" alt="<?= h($p['name']) ?>" loading="lazy">
    <?php else: ?>
      <div class="product-placeholder">🌿</div>
    <?php endif; ?>

    <!-- Badges -->
    <div class="product-badges">
      <?php if ($hasDiscount): ?>
        <span class="badge badge-sale"><?= $discPct ?>% OFF</span>
      <?php endif; ?>
      <?php if (!empty($p['new_arrival'])): ?>
        <span class="badge badge-new">New</span>
      <?php endif; ?>
      <?php if (!empty($p['bestseller'])): ?>
        <span class="badge badge-bestseller">⭐ Best Seller</span>
      <?php endif; ?>
      <?php if (!$inStock): ?>
        <span class="badge badge-oos">Out of Stock</span>
      <?php endif; ?>
      <?php if (!empty($p['difficulty'])): ?>
        <span class="badge badge-diff"><?= h($p['difficulty']) ?></span>
      <?php endif; ?>
    </div>

    <!-- Hover actions -->
    <div class="product-actions-hover">
      <button class="btn-icon-only btn-wishlist <?= $isInWishlist ? 'wished' : '' ?>"
              data-id="<?= (int)$p['id'] ?>"
              title="<?= $isInWishlist ? 'Remove from wishlist' : 'Add to wishlist' ?>">
        <i class="<?= $isInWishlist ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
      </button>
      <a href="<?= SITE_URL ?>/shop/product.php?slug=<?= h($p['slug']) ?>"
         class="btn-icon-only" title="Quick View">
        <i class="fa fa-eye"></i>
      </a>
    </div>
  </a>

  <div class="product-info">
    <?php if (!empty($p['category_name'])): ?>
      <div class="product-category"><?= h($p['category_name']) ?></div>
    <?php elseif (!empty($p['light_requirement'])): ?>
      <div class="product-category">💡 <?= h($p['light_requirement']) ?> Light</div>
    <?php endif; ?>

    <a href="<?= SITE_URL ?>/shop/product.php?slug=<?= h($p['slug']) ?>">
      <h3 class="product-name"><?= h($p['name']) ?></h3>
    </a>

    <?php if (!empty($p['difficulty']) || !empty($p['light_requirement'])): ?>
    <div class="product-props">
      <?php if (!empty($p['difficulty'])): ?>
        <span class="prop-tag">🌱 <?= h($p['difficulty']) ?></span>
      <?php endif; ?>
      <?php if (!empty($p['light_requirement'])): ?>
        <span class="prop-tag">☀️ <?= h($p['light_requirement']) ?></span>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="product-price-row">
      <span class="price-current"><?= price($currentPrice) ?></span>
      <?php if ($hasDiscount): ?>
        <span class="price-original"><?= price($p['price']) ?></span>
        <span class="price-off">-<?= $discPct ?>%</span>
      <?php endif; ?>
    </div>

    <?php if ($inStock): ?>
      <button class="product-add-btn btn-add-to-cart" data-id="<?= (int)$p['id'] ?>">
        <i class="fa fa-cart-plus"></i> Add to Cart
      </button>
    <?php else: ?>
      <button class="product-add-btn oos" disabled>
        <i class="fa fa-clock"></i> Out of Stock
      </button>
    <?php endif; ?>
  </div>
</div>
