<?php
require_once __DIR__ . '/../includes/config.php';
$db = db();

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: ' . SITE_URL . '/shop/index.php'); exit; }

// Fetch product
$stmt = $db->prepare("
    SELECT p.*, c.name AS category_name, c.slug AS category_slug,
           b.name AS brand_name, pp.difficulty, pp.light_requirement,
           pp.growth_rate, pp.co2_requirement, pp.tank_size
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN brands b     ON b.id = p.brand_id
    LEFT JOIN plant_properties pp ON pp.product_id = p.id
    WHERE p.slug = ? AND p.status = 1
");
$stmt->execute([$slug]);
$product = $stmt->fetch();
if (!$product) { header('Location: ' . SITE_URL . '/shop/index.php'); exit; }

$pid = $product['id'];
$pageTitle = h($product['name']) . ' — TiCi NatureLab';

// Images
$images = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order");
$images->execute([$pid]);
$images = $images->fetchAll();

// Variants
$variants = $db->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id");
$variants->execute([$pid]);
$variants = $variants->fetchAll();

// Reviews
$reviews = $db->prepare("
    SELECT r.*, u.name AS user_name
    FROM product_reviews r
    LEFT JOIN users u ON u.id = r.user_id
    WHERE r.product_id = ? AND r.status = 'approved'
    ORDER BY r.created_at DESC LIMIT 20
");
$reviews->execute([$pid]);
$reviews = $reviews->fetchAll();

$avgRating = 0;
if ($reviews) {
    $avgRating = array_sum(array_column($reviews, 'rating')) / count($reviews);
}

// Related products
$related = $db->prepare("
    SELECT p.*, COALESCE(pi.image_path, pi.image, '') AS thumb, pp.difficulty, pp.light_requirement
    FROM products p
    LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
    LEFT JOIN plant_properties pp ON pp.product_id = p.id
    WHERE p.category_id = ? AND p.id != ? AND p.status = 1
    ORDER BY RAND() LIMIT 4
");
$related->execute([$product['category_id'], $pid]);
$related = $related->fetchAll();

// Wishlist check
$isWished = false;
if (isLoggedIn()) {
    $wq = $db->prepare("SELECT 1 FROM wishlists WHERE user_id=? AND product_id=?");
    $wq->execute([$_SESSION['user_id'], $pid]);
    $isWished = (bool)$wq->fetchColumn();
}

// Handle review POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        setFlash('error', 'Please login to submit a review.');
    } else {
        $rating = min(5, max(1, (int)$_POST['rating']));
        $review = trim($_POST['review'] ?? '');
        if (strlen($review) < 10) {
            setFlash('error', 'Review must be at least 10 characters.');
        } else {
            $db->prepare("INSERT INTO product_reviews (product_id, user_id, reviewer_name, rating, review, status)
                VALUES (?,?,?,?,?,'pending')")
               ->execute([$pid, $_SESSION['user_id'], currentUser()['name'], $rating, $review]);
            setFlash('success', 'Review submitted! It will appear after approval.');
        }
    }
    header('Location: ' . SITE_URL . '/shop/product.php?slug=' . $slug);
    exit;
}

$currentPrice = $product['sale_price'] ? $product['sale_price'] : $product['price'];
$hasDiscount  = $product['sale_price'] && $product['sale_price'] < $product['price'];
$discPct      = $hasDiscount ? discount($product['price'], $product['sale_price']) : 0;
$stockStatus  = $product['stock'] > 10 ? 'in' : ($product['stock'] > 0 ? 'low' : 'out');

include __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<div class="container">
  <nav class="breadcrumb">
    <a href="<?= SITE_URL ?>/index.php">Home</a>
    <span class="breadcrumb-sep">›</span>
    <a href="<?= SITE_URL ?>/shop/index.php">Shop</a>
    <?php if ($product['category_name']): ?>
    <span class="breadcrumb-sep">›</span>
    <a href="<?= SITE_URL ?>/shop/index.php?category=<?= h($product['category_slug']) ?>"><?= h($product['category_name']) ?></a>
    <?php endif; ?>
    <span class="breadcrumb-sep">›</span>
    <span><?= h($product['name']) ?></span>
  </nav>
</div>

<!-- ── Product Detail ───────────────────────────────────────── -->
<section class="section-sm">
  <div class="container">
    <div class="product-detail-layout">

      <!-- Gallery -->
      <div>
        <?php
          // Resolve all image URLs once
          $galleryImgs = array_map(function($img) {
              return imgUrl($img['image_path'] ?? $img['image'] ?? '');
          }, $images);
          $mainSrc = $galleryImgs[0] ?? '';
        ?>
        <div class="product-gallery-main">
          <?php if ($mainSrc): ?>
            <img src="<?= h($mainSrc) ?>" alt="<?= h($product['name']) ?>" id="mainGalleryImg">
          <?php else: ?>
            <span id="mainGalleryEmoji" style="font-size:6rem">🌿</span>
            <img id="mainGalleryImg" style="display:none" src="" alt="">
          <?php endif; ?>

          <?php if ($hasDiscount): ?>
          <div style="position:absolute;top:14px;left:14px;background:var(--terracotta);color:#fff;font-size:.8rem;font-weight:700;padding:4px 12px;border-radius:var(--radius-full)">
            <?= $discPct ?>% OFF
          </div>
          <?php endif; ?>

          <!-- Click to expand -->
          <button onclick="openLightbox(<?= htmlspecialchars(json_encode($galleryImgs), ENT_QUOTES) ?>, 0)"
                  style="position:absolute;bottom:12px;right:12px;background:rgba(0,0,0,.55);color:#fff;border:none;border-radius:6px;padding:6px 12px;font-size:.78rem;cursor:pointer;display:flex;align-items:center;gap:6px">
            <i class="fa fa-magnifying-glass-plus"></i> Click to expand
          </button>
        </div>

        <!-- Thumbnails -->
        <?php if (count($images) > 0): ?>
        <div class="product-gallery-thumbs">
          <?php foreach ($galleryImgs as $i => $src): ?>
          <div class="gallery-thumb <?= $i===0?'active':'' ?>"
               data-src="<?= h($src) ?>"
               onclick="switchGalleryImg(this, '<?= h($src) ?>')">
            <?php if ($src): ?>
              <img src="<?= h($src) ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:4px">
            <?php else: ?>
              <span>🌿</span>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="product-gallery-thumbs">
          <div class="gallery-thumb active"><span style="font-size:2rem">🌿</span></div>
        </div>
        <?php endif; ?>

        <!-- Lightbox -->
        <div id="lightbox" onclick="closeLightbox()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;align-items:center;justify-content:center;cursor:zoom-out">
          <img id="lightboxImg" style="max-width:90vw;max-height:90vh;border-radius:10px;box-shadow:0 8px 40px rgba(0,0,0,.6)" src="" alt="">
          <button onclick="lbPrev(event)" style="position:fixed;left:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.15);color:#fff;border:none;width:44px;height:44px;border-radius:50%;font-size:1.2rem;cursor:pointer">‹</button>
          <button onclick="lbNext(event)" style="position:fixed;right:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.15);color:#fff;border:none;width:44px;height:44px;border-radius:50%;font-size:1.2rem;cursor:pointer">›</button>
          <button onclick="closeLightbox()" style="position:fixed;top:16px;right:16px;background:rgba(255,255,255,.15);color:#fff;border:none;width:36px;height:36px;border-radius:50%;font-size:1rem;cursor:pointer">✕</button>
        </div>
      </div>

      <!-- Product Info -->
      <div>
        <?php if ($product['category_name']): ?>
        <div class="detail-category">
          <a href="<?= SITE_URL ?>/shop/index.php?category=<?= h($product['category_slug']) ?>" style="color:var(--green-bright)"><?= h($product['category_name']) ?></a>
        </div>
        <?php endif; ?>

        <h1 class="detail-title"><?= h($product['name']) ?></h1>

        <!-- Rating -->
        <?php if ($avgRating > 0): ?>
        <div class="detail-rating">
          <span class="detail-stars"><?= getStars($avgRating) ?></span>
          <span style="font-weight:600;font-size:.9rem"><?= number_format($avgRating, 1) ?></span>
          <span class="detail-reviews">(<?= count($reviews) ?> review<?= count($reviews)!==1?'s':'' ?>)</span>
        </div>
        <?php else: ?>
        <div class="detail-rating">
          <span class="detail-stars" style="color:var(--text-light)">☆☆☆☆☆</span>
          <span class="detail-reviews">No reviews yet</span>
        </div>
        <?php endif; ?>

        <!-- Price -->
        <div class="detail-price-row">
          <span class="detail-price" id="displayPrice"><?= price($currentPrice) ?></span>
          <?php if ($hasDiscount): ?>
          <span class="detail-price-orig"><?= price($product['price']) ?></span>
          <span class="detail-price-off">Save <?= $discPct ?>%</span>
          <?php endif; ?>
        </div>

        <!-- Stock -->
        <div class="stock-badge stock-<?= $stockStatus ?>">
          <?php if ($stockStatus === 'in'): ?>
            <i class="fa fa-circle-check"></i> In Stock (<?= $product['stock'] ?> available)
          <?php elseif ($stockStatus === 'low'): ?>
            <i class="fa fa-triangle-exclamation"></i> Only <?= $product['stock'] ?> left — Order soon!
          <?php else: ?>
            <i class="fa fa-circle-xmark"></i> Out of Stock
          <?php endif; ?>
        </div>

        <!-- Plant Properties -->
        <?php if ($product['difficulty'] || $product['light_requirement'] || $product['growth_rate'] || $product['co2_requirement']): ?>
        <div class="plant-props-grid" style="margin-bottom:20px">
          <?php if ($product['difficulty']): ?>
          <div class="plant-prop">
            <div class="plant-prop-icon">🌱</div>
            <span class="plant-prop-label">Difficulty</span>
            <span class="plant-prop-val"><?= h($product['difficulty']) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($product['light_requirement']): ?>
          <div class="plant-prop">
            <div class="plant-prop-icon">☀️</div>
            <span class="plant-prop-label">Light</span>
            <span class="plant-prop-val"><?= h($product['light_requirement']) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($product['growth_rate']): ?>
          <div class="plant-prop">
            <div class="plant-prop-icon">📈</div>
            <span class="plant-prop-label">Growth Rate</span>
            <span class="plant-prop-val"><?= h($product['growth_rate']) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($product['co2_requirement']): ?>
          <div class="plant-prop">
            <div class="plant-prop-icon">💨</div>
            <span class="plant-prop-label">CO₂</span>
            <span class="plant-prop-val"><?= h($product['co2_requirement']) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($product['tank_size']): ?>
          <div class="plant-prop">
            <div class="plant-prop-icon">🐠</div>
            <span class="plant-prop-label">Tank Size</span>
            <span class="plant-prop-val"><?= h($product['tank_size']) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($product['brand_name']): ?>
          <div class="plant-prop">
            <div class="plant-prop-icon">🏷️</div>
            <span class="plant-prop-label">Brand</span>
            <span class="plant-prop-val"><?= h($product['brand_name']) ?></span>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Variants -->
        <?php if (!empty($variants)): ?>
        <div class="variants-section">
          <div class="variants-label">Select Option</div>
          <div class="variant-options">
            <?php foreach ($variants as $i => $v): ?>
            <button type="button" class="variant-btn <?= $i===0?'active':'' ?>"
                    data-id="<?= $v['id'] ?>"
                    data-price="<?= $v['price'] ?>">
              <?= h($v['variant_name']) ?>
              <?php if ($v['price'] != $currentPrice): ?>
                <span style="font-size:.78rem;color:var(--text-muted)"><?= price($v['price']) ?></span>
              <?php endif; ?>
            </button>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Add to cart -->
        <div class="detail-add-row">
          <div class="detail-qty qty-control">
            <button type="button" class="qty-btn qty-dec">−</button>
            <input type="number" class="qty-input qty-value" value="1" min="1" max="<?= $product['stock'] ?>" style="width:50px;height:42px;border:none;border-left:1.5px solid var(--border-strong);border-right:1.5px solid var(--border-strong);text-align:center;font-weight:600;font-size:.95rem">
            <button type="button" class="qty-btn qty-inc">+</button>
          </div>

          <?php if ($product['stock'] > 0): ?>
          <button class="btn btn-primary btn-lg btn-add-to-cart" data-id="<?= $pid ?>" style="flex:1">
            <i class="fa fa-cart-plus"></i> Add to Cart
          </button>
          <?php else: ?>
          <button class="btn btn-secondary btn-lg" disabled style="flex:1;opacity:.5;cursor:not-allowed">
            Out of Stock
          </button>
          <?php endif; ?>

          <button class="btn-icon-only btn-wishlist <?= $isWished ? 'wished' : '' ?>"
                  data-id="<?= $pid ?>"
                  style="width:46px;height:46px;flex-shrink:0"
                  title="<?= $isWished ? 'Remove from wishlist' : 'Add to wishlist' ?>">
            <i class="<?= $isWished ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
          </button>
        </div>

        <!-- Quick info pills -->
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px">
          <span style="font-size:.78rem;padding:5px 12px;background:var(--green-ghost);border-radius:var(--radius-full);color:var(--text-muted)">
            <i class="fa fa-truck" style="color:var(--green-mid)"></i>
            <?= $product['price'] >= 999 ? 'Free Shipping' : 'Shipping from ₹120' ?>
          </span>
          <span style="font-size:.78rem;padding:5px 12px;background:var(--green-ghost);border-radius:var(--radius-full);color:var(--text-muted)">
            <i class="fa fa-leaf" style="color:var(--green-mid)"></i> Live Plant Guarantee
          </span>
          <?php if ($product['sku']): ?>
          <span style="font-size:.78rem;padding:5px 12px;background:var(--green-ghost);border-radius:var(--radius-full);color:var(--text-muted)">
            SKU: <?= h($product['sku']) ?>
          </span>
          <?php endif; ?>
        </div>

        <!-- Share -->
        <div style="display:flex;gap:10px;align-items:center;padding-top:16px;border-top:1px solid var(--border)">
          <span style="font-size:.8rem;color:var(--text-muted);font-weight:600">Share:</span>
          <a href="https://wa.me/?text=<?= urlencode($product['name'] . ' — ' . SITE_URL . '/shop/product.php?slug=' . $slug) ?>" target="_blank" class="btn-icon-only" title="WhatsApp" style="color:#25d366;border-color:#25d366"><i class="fab fa-whatsapp"></i></a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/shop/product.php?slug=' . $slug) ?>" target="_blank" class="btn-icon-only" title="Facebook"><i class="fab fa-facebook-f"></i></a>
          <button onclick="navigator.clipboard.writeText(window.location.href);showToast('Link copied!','success')" class="btn-icon-only" title="Copy Link"><i class="fa fa-link"></i></button>
        </div>
      </div>
    </div>

    <!-- ── Detail Tabs ──────────────────────────────────────── -->
    <div class="detail-tabs">
      <div class="tabs-wrap">
        <button class="tab-btn active" data-dtab="description">Description</button>
        <button class="tab-btn" data-dtab="care">🌿 Plant Care</button>
        <button class="tab-btn" data-dtab="shipping">🚚 Shipping</button>
        <button class="tab-btn" data-dtab="reviews">⭐ Reviews (<?= count($reviews) ?>)</button>
      </div>

      <!-- Description -->
      <div class="detail-tab-content" id="dtab-description">
        <?php if ($product['description']): ?>
          <div style="line-height:1.8;color:var(--text-body)"><?= nl2br(h($product['description'])) ?></div>
        <?php else: ?>
          <p>No description available for this product.</p>
        <?php endif; ?>
      </div>

      <!-- Care -->
      <div class="detail-tab-content" id="dtab-care" style="display:none">
        <?php if ($product['care_info']): ?>
          <div><?= nl2br(h($product['care_info'])) ?></div>
        <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px">
          <?php foreach ([
            ['🌡️','Water Temperature','22°C – 28°C ideal for most aquatic plants'],
            ['💧','Water pH','6.0 – 7.5 is optimal for healthy growth'],
            ['🪨','Substrate','Fine-grained nutrient-rich substrate recommended'],
            ['💡','Lighting','As per light requirement specified above'],
            ['🧪','Fertilizers','Liquid fertilizers 2-3x per week for best results'],
            ['✂️','Trimming','Trim regularly to maintain shape and encourage growth'],
          ] as [$ico,$label,$val]): ?>
          <div style="background:var(--green-ghost);border:1px solid var(--border);border-radius:var(--radius-sm);padding:16px">
            <div style="font-size:1.4rem;margin-bottom:6px"><?= $ico ?></div>
            <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.8px;color:var(--green-mid);font-weight:700;margin-bottom:4px"><?= $label ?></div>
            <div style="font-size:.875rem;color:var(--text-body)"><?= $val ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Shipping -->
      <div class="detail-tab-content" id="dtab-shipping" style="display:none">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px">
          <?php foreach ([
            ['🚚','Standard Delivery','5–7 business days · ₹120 (Free above ₹999)'],
            ['⚡','Express Delivery','2–3 business days · ₹180'],
            ['🌿','Live Plant Packaging','Heat-insulated boxes with moisture retention'],
            ['📦','Guarantee','100% live arrival guarantee on all plants'],
            ['🔄','Returns','Returns accepted within 24 hours of delivery for DOA'],
            ['📞','Support','Expert plant support via WhatsApp: +91 98765 43210'],
          ] as [$ico,$label,$info]): ?>
          <div style="background:var(--green-ghost);border:1px solid var(--border);border-radius:var(--radius-sm);padding:16px">
            <div style="font-size:1.4rem;margin-bottom:6px"><?= $ico ?></div>
            <div style="font-size:.78rem;font-weight:700;color:var(--green-dark);margin-bottom:4px"><?= $label ?></div>
            <div style="font-size:.83rem;color:var(--text-muted)"><?= $info ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Reviews -->
      <div class="detail-tab-content" id="dtab-reviews" style="display:none">
        <?php if (!empty($reviews)): ?>
        <div style="margin-bottom:28px;padding:20px;background:var(--green-ghost);border-radius:var(--radius-md);display:flex;align-items:center;gap:24px;flex-wrap:wrap">
          <div style="text-align:center">
            <div style="font-family:var(--font-display);font-size:3rem;color:var(--green-dark);font-weight:700"><?= number_format($avgRating, 1) ?></div>
            <div style="color:var(--amber);font-size:1.2rem"><?= getStars($avgRating) ?></div>
            <div style="font-size:.78rem;color:var(--text-muted);margin-top:4px"><?= count($reviews) ?> reviews</div>
          </div>
          <div style="flex:1;min-width:200px">
            <?php for ($star = 5; $star >= 1; $star--): ?>
            <?php $cnt = count(array_filter($reviews, fn($r) => $r['rating'] == $star)); ?>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;font-size:.8rem">
              <span style="width:14px;text-align:right"><?= $star ?></span>
              <span style="color:var(--amber)">★</span>
              <div style="flex:1;height:6px;background:var(--border);border-radius:3px">
                <div style="width:<?= count($reviews) ? round($cnt/count($reviews)*100) : 0 ?>%;height:100%;background:var(--amber);border-radius:3px"></div>
              </div>
              <span style="color:var(--text-muted);width:18px"><?= $cnt ?></span>
            </div>
            <?php endfor; ?>
          </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:16px;margin-bottom:32px">
          <?php foreach ($reviews as $r): ?>
          <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:20px">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;flex-wrap:wrap;gap:8px">
              <div>
                <div style="font-weight:600;font-size:.9rem"><?= h($r['reviewer_name'] ?: $r['user_name'] ?: 'Customer') ?></div>
                <div style="color:var(--amber);font-size:.9rem"><?= str_repeat('★', $r['rating']) ?><?= str_repeat('☆', 5 - $r['rating']) ?></div>
              </div>
              <div style="font-size:.75rem;color:var(--text-muted)"><?= date('d M Y', strtotime($r['created_at'])) ?></div>
            </div>
            <p style="font-size:.875rem;color:var(--text-body);line-height:1.7"><?= h($r['review']) ?></p>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
          <p style="color:var(--text-muted);margin-bottom:24px">No reviews yet. Be the first to review this product!</p>
        <?php endif; ?>

        <!-- Submit review -->
        <?php if (isLoggedIn()): ?>
        <div style="background:var(--green-ghost);border:1px solid var(--border-strong);border-radius:var(--radius-lg);padding:24px">
          <h4 style="font-family:var(--font-display);font-size:1rem;color:var(--green-dark);margin-bottom:16px">Write a Review</h4>
          <form method="POST">
            <div class="form-group">
              <label class="form-label">Rating</label>
              <div id="starPicker" style="display:flex;gap:6px;font-size:1.6rem;cursor:pointer;color:var(--text-light)">
                <?php for ($i=1;$i<=5;$i++): ?>
                <span data-val="<?= $i ?>" onclick="setRating(<?= $i ?>)">☆</span>
                <?php endfor; ?>
              </div>
              <input type="hidden" name="rating" id="ratingInput" value="5">
            </div>
            <div class="form-group">
              <label class="form-label">Your Review</label>
              <textarea name="review" class="form-control" rows="4" placeholder="Share your experience with this product…" required minlength="10"></textarea>
            </div>
            <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
          </form>
        </div>
        <?php else: ?>
        <div style="background:var(--green-ghost);border:1px solid var(--border);border-radius:var(--radius-md);padding:20px;text-align:center">
          <p style="color:var(--text-muted);margin-bottom:12px">Login to write a review</p>
          <a href="<?= SITE_URL ?>/account/login.php" class="btn btn-primary btn-sm">Login</a>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ── Related Products ─────────────────────────────────── -->
    <?php if (!empty($related)): ?>
    <div style="margin-top:60px">
      <h2 style="font-family:var(--font-display);font-size:1.6rem;color:var(--green-dark);margin-bottom:24px">You May Also Like</h2>
      <div class="products-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr))">
        <?php foreach ($related as $p): include __DIR__ . '/../includes/product-card.php'; endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<script>
// Detail tabs
document.querySelectorAll('[data-dtab]').forEach(function(btn) {
  btn.addEventListener('click', function() {
    document.querySelectorAll('[data-dtab]').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.detail-tab-content').forEach(c => c.style.display = 'none');
    this.classList.add('active');
    document.getElementById('dtab-' + this.dataset.dtab).style.display = 'block';
  });
});
// Star picker
function setRating(val) {
  document.getElementById('ratingInput').value = val;
  var spans = document.querySelectorAll('#starPicker span');
  spans.forEach(function(s, i) {
    s.textContent = i < val ? '★' : '☆';
    s.style.color = i < val ? 'var(--amber)' : 'var(--text-light)';
  });
}
setRating(5);

// ── Gallery thumbnail switcher ────────────────────────────────
function switchGalleryImg(el, src) {
  document.querySelectorAll('.gallery-thumb').forEach(function(t) { t.classList.remove('active'); });
  el.classList.add('active');
  var main = document.getElementById('mainGalleryImg');
  var emoji = document.getElementById('mainGalleryEmoji');
  if (src) {
    main.src = src;
    main.style.display = '';
    if (emoji) emoji.style.display = 'none';
  }
}

// ── Lightbox ──────────────────────────────────────────────────
var _lbImgs = [], _lbIdx = 0;
function openLightbox(imgs, idx) {
  _lbImgs = imgs.filter(function(s){ return !!s; });
  _lbIdx  = idx || 0;
  var lb = document.getElementById('lightbox');
  lb.style.display = 'flex';
  document.getElementById('lightboxImg').src = _lbImgs[_lbIdx] || '';
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  document.getElementById('lightbox').style.display = 'none';
  document.body.style.overflow = '';
}
function lbPrev(e) { e.stopPropagation(); _lbIdx = (_lbIdx - 1 + _lbImgs.length) % _lbImgs.length; document.getElementById('lightboxImg').src = _lbImgs[_lbIdx]; }
function lbNext(e) { e.stopPropagation(); _lbIdx = (_lbIdx + 1) % _lbImgs.length; document.getElementById('lightboxImg').src = _lbImgs[_lbIdx]; }
document.addEventListener('keydown', function(e) {
  if (document.getElementById('lightbox').style.display === 'flex') {
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft')  lbPrev(e);
    if (e.key === 'ArrowRight') lbNext(e);
  }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
