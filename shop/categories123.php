<?php
require_once __DIR__ . '/../includes/config.php';
$db = db();

$pageTitle = 'All Categories — TiCi NatureLab';

// Fetch all active categories (simple — no subqueries for MariaDB 10.4 compat)
$categories = $db->query("
    SELECT c.*
    FROM categories c
    WHERE c.status = 1
    ORDER BY c.sort_order, c.name
")->fetchAll();

// Deduplicate by slug (keep first per slug)
$seen = []; $cats = [];
foreach ($categories as $c) {
    if (!isset($seen[$c['slug']])) {
        $seen[$c['slug']] = true;
        $cats[] = $c;
    }
}
$categories = $cats;

// Enrich each category with product_count, min_price, sample_img via separate queries
foreach ($categories as &$cat) {
    $cid = (int)$cat['id'];

    // product count
    $cnt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND status = 1");
    $cnt->execute([$cid]);
    $cat['product_count'] = (int)$cnt->fetchColumn();

    // min price
    $mp = $db->prepare("SELECT MIN(COALESCE(sale_price, price)) FROM products WHERE category_id = ? AND status = 1");
    $mp->execute([$cid]);
    $cat['min_price'] = $mp->fetchColumn() ?: null;

    // sample image — first primary image of best product
    $si = $db->prepare("
        SELECT COALESCE(pi.image_path, pi.image, '') AS img
        FROM products p
        LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
        WHERE p.category_id = ? AND p.status = 1
        ORDER BY p.featured DESC, p.id DESC
        LIMIT 1
    ");
    $si->execute([$cid]);
    $cat['sample_img'] = $si->fetchColumn() ?: '';
}
unset($cat);

// Fetch products for a selected category (AJAX or direct param)
$selectedSlug = trim($_GET['category'] ?? '');
$selectedCat  = null;
$catProducts  = [];

if ($selectedSlug) {
    foreach ($categories as $c) {
        if ($c['slug'] === $selectedSlug) { $selectedCat = $c; break; }
    }
    if ($selectedCat) {
        $catProducts = $db->prepare("
            SELECT p.*, COALESCE(pi.image_path, pi.image, '') AS thumb,
                   pp.difficulty, pp.light_requirement
            FROM products p
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            LEFT JOIN plant_properties pp ON pp.product_id = p.id
            WHERE p.category_id = ? AND p.status = 1
            ORDER BY p.featured DESC, p.created_at DESC
        ");
        $catProducts->execute([$selectedCat['id']]);
        $catProducts = $catProducts->fetchAll();
    }
}

include __DIR__ . '/../includes/header.php';
?>

<style>
/* ── Page layout ── */
.cats-page-wrap { background: var(--cream); min-height: 60vh; }

/* ── Hero bar ── */
.cats-hero {
  background: linear-gradient(135deg, var(--green-dark) 0%, var(--green-mid) 100%);
  padding: 48px 0 52px;
  position: relative;
  overflow: hidden;
}
.cats-hero::before {
  content: '🌿';
  position: absolute;
  right: 80px; top: 20px;
  font-size: 7rem;
  opacity: .08;
  pointer-events: none;
}
.cats-hero-label {
  font-size: .72rem;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--green-light);
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.cats-hero-label::before,
.cats-hero-label::after {
  content: '';
  display: inline-block;
  width: 32px;
  height: 2px;
  background: var(--green-light);
  opacity: .5;
}
.cats-hero h1 {
  font-family: var(--font-display);
  font-size: 2.4rem;
  color: #fff;
  margin-bottom: 10px;
}
.cats-hero p {
  color: rgba(255,255,255,.7);
  font-size: .95rem;
  max-width: 500px;
}

/* ── Category grid ── */
.cats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 24px;
  padding: 48px 0;
}
.cat-list-card {
  background: #fff;
  border: 1.5px solid var(--border);
  border-radius: 20px;
  overflow: hidden;
  text-decoration: none;
  color: inherit;
  display: flex;
  flex-direction: column;
  transition: transform .22s, box-shadow .22s, border-color .22s;
  cursor: pointer;
}
.cat-list-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 18px 48px rgba(26,61,43,.13);
  border-color: var(--green-bright);
}
.cat-list-card.active {
  border-color: var(--green-bright);
  box-shadow: 0 0 0 3px rgba(61,186,111,.18);
}
.clc-img {
  height: 160px;
  background: linear-gradient(135deg, var(--green-ghost) 0%, var(--green-pale) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 4rem;
  position: relative;
  overflow: hidden;
  flex-shrink: 0;
}
.clc-img img {
  width: 100%; height: 100%; object-fit: cover; display: block;
  transition: transform .4s;
}
.cat-list-card:hover .clc-img img { transform: scale(1.06); }
.clc-count {
  position: absolute;
  bottom: 10px; right: 12px;
  background: rgba(26,61,43,.75);
  backdrop-filter: blur(4px);
  color: #fff;
  font-size: .68rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 20px;
  letter-spacing: .3px;
}
.clc-body {
  padding: 18px 20px 20px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}
.clc-name {
  font-family: var(--font-display);
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--green-dark);
  line-height: 1.25;
}
.clc-from {
  font-size: .78rem;
  color: var(--text-muted);
}
.clc-from strong { color: var(--green-bright); font-weight: 700; }
.clc-cta {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  margin-top: auto;
  padding-top: 12px;
  border-top: 1px solid var(--border);
  font-size: .78rem;
  font-weight: 700;
  color: var(--green-bright);
  text-transform: uppercase;
  letter-spacing: .5px;
  transition: gap .2s;
}
.cat-list-card:hover .clc-cta { gap: 12px; }

/* ── Products drawer panel ── */
.cat-products-panel {
  background: #fff;
  border-top: 2px solid var(--green-bright);
  padding: 40px 0 56px;
}
.cpp-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 28px;
  flex-wrap: wrap;
  gap: 12px;
}
.cpp-back {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: .83rem;
  font-weight: 600;
  color: var(--green-mid);
  text-decoration: none;
  border: 1px solid var(--border);
  padding: 6px 14px;
  border-radius: var(--radius-full);
  transition: background .18s;
}
.cpp-back:hover { background: var(--green-ghost); }

/* Empty state */
.cats-empty {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-muted);
}
.cats-empty .ce-emoji { font-size: 4rem; margin-bottom: 16px; }
.cats-empty h3 { font-family: var(--font-display); color: var(--green-dark); margin-bottom: 8px; }

@media (max-width: 700px) {
  .cats-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
  .clc-img   { height: 120px; font-size: 3rem; }
  .cats-hero h1 { font-size: 1.7rem; }
}
@media (max-width: 420px) {
  .cats-grid { grid-template-columns: 1fr; }
}
</style>

<!-- Breadcrumb -->
<div class="container">
  <nav class="breadcrumb">
    <a href="<?= SITE_URL ?>/index.php">Home</a>
    <span class="breadcrumb-sep">›</span>
    <a href="<?= SITE_URL ?>/shop/index.php">Shop</a>
    <span class="breadcrumb-sep">›</span>
    <?php if ($selectedCat): ?>
    <a href="<?= SITE_URL ?>/shop/categories.php">All Categories</a>
    <span class="breadcrumb-sep">›</span>
    <span><?= h($selectedCat['name']) ?></span>
    <?php else: ?>
    <span>All Categories</span>
    <?php endif; ?>
  </nav>
</div>

<div class="cats-page-wrap">

  <!-- Hero -->
  <div class="cats-hero">
    <div class="container">
      <div class="cats-hero-label">Browse</div>
      <h1><?= $selectedCat ? h($selectedCat['name']) : 'All Categories' ?></h1>
      <p><?= $selectedCat
            ? 'Showing all ' . count($catProducts) . ' products in ' . h($selectedCat['name'])
            : 'Explore our ' . count($categories) . ' curated plant categories — aquatic, terrarium, fertilizers & more.'
          ?></p>
    </div>
  </div>

  <?php if (!$selectedCat): ?>
  <!-- ── ALL CATEGORIES GRID ───────────────────────── -->
  <div class="container">
    <div class="cats-grid">

      <!-- All Products card -->
      <a href="<?= SITE_URL ?>/shop/index.php" class="cat-list-card">
        <div class="clc-img">
          <span>🛍️</span>
          <div class="clc-count"><?= array_sum(array_column($categories,'product_count')) ?>+ products</div>
        </div>
        <div class="clc-body">
          <div class="clc-name">All Products</div>
          <div class="clc-from">Browse everything in our store</div>
          <div class="clc-cta">Shop Now <i class="fa fa-arrow-right"></i></div>
        </div>
      </a>

      <?php foreach ($categories as $cat):
        $imgSrc = imgUrl($cat['sample_img'] ?? '');
        $emoji  = (!empty($cat['image']) && mb_strlen($cat['image']) <= 8) ? $cat['image'] : '🌿';
        $hasImg = $imgSrc && !empty($cat['sample_img']);
      ?>
      <a href="<?= SITE_URL ?>/shop/categories.php?category=<?= h($cat['slug']) ?>"
         class="cat-list-card" id="catcard-<?= h($cat['slug']) ?>">
        <div class="clc-img">
          <?php if ($hasImg): ?>
            <img src="<?= h($imgSrc) ?>" alt="<?= h($cat['name']) ?>"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <span style="display:none;font-size:4rem"><?= $emoji ?></span>
          <?php else: ?>
            <span><?= $emoji ?></span>
          <?php endif; ?>
          <?php if ($cat['product_count'] > 0): ?>
          <div class="clc-count"><?= $cat['product_count'] ?> product<?= $cat['product_count']!==1?'s':'' ?></div>
          <?php endif; ?>
        </div>
        <div class="clc-body">
          <div class="clc-name"><?= h($cat['name']) ?></div>
          <?php if ($cat['min_price']): ?>
          <div class="clc-from">From <strong><?= price($cat['min_price']) ?></strong></div>
          <?php elseif (!empty($cat['description'])): ?>
          <div class="clc-from" style="font-size:.76rem;-webkit-line-clamp:2;-webkit-box-orient:vertical;display:-webkit-box;overflow:hidden"><?= h($cat['description']) ?></div>
          <?php else: ?>
          <div class="clc-from">Explore this collection</div>
          <?php endif; ?>
          <div class="clc-cta">Shop Now <i class="fa fa-arrow-right"></i></div>
        </div>
      </a>
      <?php endforeach; ?>

    </div>
  </div>

  <?php else: ?>
  <!-- ── CATEGORY PRODUCTS ────────────────────────── -->
  <div class="cat-products-panel">
    <div class="container">
      <div class="cpp-header">
        <div>
          <a href="<?= SITE_URL ?>/shop/categories.php" class="cpp-back">
            <i class="fa fa-arrow-left"></i> All Categories
          </a>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
          <span style="font-size:.83rem;color:var(--text-muted)"><?= count($catProducts) ?> product<?= count($catProducts)!==1?'s':'' ?></span>
          <a href="<?= SITE_URL ?>/shop/index.php?category=<?= h($selectedCat['slug']) ?>" class="btn btn-secondary btn-sm">
            <i class="fa fa-sliders"></i> Filter & Sort
          </a>
        </div>
      </div>

      <?php if (empty($catProducts)): ?>
      <div class="cats-empty">
        <div class="ce-emoji">🌱</div>
        <h3>No products yet</h3>
        <p>We're adding products to this category soon. <a href="<?= SITE_URL ?>/shop/index.php" style="color:var(--green-bright)">Browse all products</a></p>
      </div>
      <?php else: ?>
      <div class="products-grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr))">
        <?php foreach ($catProducts as $p): include __DIR__ . '/../includes/product-card.php'; endforeach; ?>
      </div>
      <div style="text-align:center;margin-top:36px">
        <a href="<?= SITE_URL ?>/shop/index.php?category=<?= h($selectedCat['slug']) ?>" class="btn btn-secondary">
          View All in <?= h($selectedCat['name']) ?> <i class="fa fa-arrow-right"></i>
        </a>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php endif; ?>

</div><!-- /cats-page-wrap -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
