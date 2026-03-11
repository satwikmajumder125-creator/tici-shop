<?php
require_once __DIR__ . '/../includes/config.php';
$pageTitle = 'Shop — TiCi NatureLab';
$db = db();

// ── Inputs ────────────────────────────────────────────────────
$q         = trim($_GET['q'] ?? '');
$catSlug   = trim($_GET['category'] ?? '');
$sort      = $_GET['sort'] ?? 'newest';
$minPrice  = (float)($_GET['min_price'] ?? 0);
$maxPrice  = (float)($_GET['max_price'] ?? 99999);
$difficulty= $_GET['difficulty'] ?? [];
$light     = $_GET['light'] ?? [];
$sale      = !empty($_GET['sale']);
$isFeatured   = !empty($_GET['featured']);
$isBestseller = !empty($_GET['bestseller']);
$isNew        = !empty($_GET['new']);
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 16;
$offset    = ($page - 1) * $perPage;

// ── Load categories ───────────────────────────────────────────
$categories = getCategories();
$currentCat = null;
if ($catSlug) {
    foreach ($categories as $c) {
        if ($c['slug'] === $catSlug) { $currentCat = $c; break; }
    }
}
if ($currentCat) $pageTitle = h($currentCat['name']) . ' — TiCi NatureLab';
if ($q)          $pageTitle = 'Search: ' . h($q) . ' — TiCi NatureLab';

// ── Build WHERE ───────────────────────────────────────────────
$where  = ["p.status = 1"];
$params = [];

if ($q) {
    $where[]  = "(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
    $like     = "%$q%";
    $params   = array_merge($params, [$like, $like, $like]);
}
if ($currentCat) {
    $where[]  = "p.category_id = ?";
    $params[] = $currentCat['id'];
}
if ($sale) {
    $where[] = "p.sale_price IS NOT NULL AND p.sale_price < p.price";
}
if ($isFeatured)   { $where[] = "p.featured = 1"; }
if ($isBestseller) { $where[] = "p.bestseller = 1"; }
if ($isNew)        { $where[] = "p.new_arrival = 1"; }
if ($minPrice > 0) {
    $where[]  = "COALESCE(p.sale_price, p.price) >= ?";
    $params[] = $minPrice;
}
if ($maxPrice < 99999) {
    $where[]  = "COALESCE(p.sale_price, p.price) <= ?";
    $params[] = $maxPrice;
}
if (!empty($difficulty)) {
    $ph       = implode(',', array_fill(0, count($difficulty), '?'));
    $where[]  = "pp.difficulty IN ($ph)";
    $params   = array_merge($params, $difficulty);
}
if (!empty($light)) {
    $ph       = implode(',', array_fill(0, count($light), '?'));
    $where[]  = "pp.light_requirement IN ($ph)";
    $params   = array_merge($params, $light);
}

$whereStr = implode(' AND ', $where);

// Sort
$orderBy = match($sort) {
    'price_asc'  => 'COALESCE(p.sale_price, p.price) ASC',
    'price_desc' => 'COALESCE(p.sale_price, p.price) DESC',
    'popular'    => 'p.bestseller DESC, p.id DESC',
    'name'       => 'p.name ASC',
    default      => 'p.created_at DESC',
};

// ── Count ──────────────────────────────────────────────────────
$countSql = "SELECT COUNT(*) FROM products p
             LEFT JOIN plant_properties pp ON pp.product_id = p.id
             WHERE $whereStr";
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$totalProducts = (int)$countStmt->fetchColumn();
$totalPages    = max(1, ceil($totalProducts / $perPage));

// ── Fetch ──────────────────────────────────────────────────────
$sql = "SELECT p.*, COALESCE(pi.image_path, pi.image, '') AS thumb, pp.difficulty, pp.light_requirement, pp.growth_rate, pp.co2_requirement,
               c.name AS category_name
        FROM products p
        LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
        LEFT JOIN plant_properties pp ON pp.product_id = p.id
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE $whereStr
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Price range for slider
$rangeStmt = $db->query("SELECT MIN(COALESCE(sale_price,price)) AS minp, MAX(COALESCE(sale_price,price)) AS maxp FROM products WHERE status=1");
$priceRange = $rangeStmt->fetch();

include __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<div class="container">
  <nav class="breadcrumb">
    <a href="<?= SITE_URL ?>/index.php">Home</a>
    <span class="breadcrumb-sep">›</span>
    <a href="<?= SITE_URL ?>/shop/index.php">Shop</a>
    <?php if ($currentCat): ?>
      <span class="breadcrumb-sep">›</span>
      <span><?= h($currentCat['name']) ?></span>
    <?php elseif ($q): ?>
      <span class="breadcrumb-sep">›</span>
      <span>Search: "<?= h($q) ?>"</span>
    <?php endif; ?>
  </nav>
</div>

<div class="container" style="padding-bottom:60px">
  <!-- Page heading -->
  <div style="margin-bottom:24px">
    <h1 style="font-family:var(--font-display);font-size:1.8rem;color:var(--green-dark)">
      <?php if ($currentCat): ?>
        <?= h($currentCat['image']) ?> <?= h($currentCat['name']) ?>
      <?php elseif ($sale): ?>
        🔥 Sale Products
      <?php elseif ($q): ?>
        Search Results for "<?= h($q) ?>"
      <?php else: ?>
        🛍️ All Products
      <?php endif; ?>
    </h1>
    <?php if ($currentCat && $currentCat['description']): ?>
      <p style="color:var(--text-muted);font-size:.9rem;margin-top:6px"><?= h($currentCat['description']) ?></p>
    <?php endif; ?>
  </div>

  <!-- Category pills strip -->
  <div style="display:flex;gap:10px;overflow-x:auto;scrollbar-width:none;margin-bottom:28px;padding-bottom:4px">
    <a href="<?= SITE_URL ?>/shop/index.php" class="btn btn-sm <?= !$catSlug && !$sale ? 'btn-primary' : 'btn-secondary' ?>">All</a>
    <?php foreach ($categories as $cat): ?>
    <a href="<?= SITE_URL ?>/shop/index.php?category=<?= h($cat['slug']) ?>"
       class="btn btn-sm <?= $catSlug === $cat['slug'] ? 'btn-primary' : 'btn-secondary' ?>"
       style="white-space:nowrap">
      <?= h($cat['image']) ?> <?= h($cat['name']) ?>
    </a>
    <?php endforeach; ?>
    <a href="<?= SITE_URL ?>/shop/index.php?sale=1"
       class="btn btn-sm <?= $sale ? 'btn-amber' : 'btn-secondary' ?>">🔥 Sale</a>
  </div>

  <form id="filterForm" method="GET" action="<?= SITE_URL ?>/shop/index.php">
    <?php if ($q): ?><input type="hidden" name="q" value="<?= h($q) ?>"><?php endif; ?>
    <?php if ($catSlug): ?><input type="hidden" name="category" value="<?= h($catSlug) ?>"><?php endif; ?>
    <?php if ($sale): ?><input type="hidden" name="sale" value="1"><?php endif; ?>
    <?php if ($isFeatured): ?><input type="hidden" name="featured" value="1"><?php endif; ?>
    <?php if ($isBestseller): ?><input type="hidden" name="bestseller" value="1"><?php endif; ?>
    <?php if ($isNew): ?><input type="hidden" name="new" value="1"><?php endif; ?>
    <input type="hidden" name="sort" id="sortHidden" value="<?= h($sort) ?>">

    <div class="shop-layout">
      <!-- ── SIDEBAR ───────────────────────────────────────── -->
      <aside class="shop-sidebar" id="shopSidebar">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
          <h3 style="font-family:var(--font-display);font-size:1rem;color:var(--green-dark)">Filters</h3>
          <div style="display:flex;gap:8px">
            <a href="<?= SITE_URL ?>/shop/index.php<?= $catSlug?"?category=$catSlug":'' ?>" style="font-size:.75rem;color:var(--terracotta)">Clear All</a>
            <button type="button" id="filterClose" style="display:none;font-size:1rem;color:var(--text-muted);background:none;border:none;cursor:pointer">✕</button>
          </div>
        </div>

        <!-- Price Range -->
        <div class="filter-section">
          <div class="filter-title">Price Range</div>
          <div class="price-range">
            <input type="number" name="min_price" id="minPrice" placeholder="₹ Min" value="<?= $minPrice ?: '' ?>" min="0" style="width:80px;padding:7px 8px;border:1.5px solid var(--border-strong);border-radius:var(--radius-sm);font-size:.83rem;outline:none">
            <span style="color:var(--text-muted)">—</span>
            <input type="number" name="max_price" id="maxPrice" placeholder="₹ Max" value="<?= $maxPrice < 99999 ? $maxPrice : '' ?>" min="0" style="width:80px;padding:7px 8px;border:1.5px solid var(--border-strong);border-radius:var(--radius-sm);font-size:.83rem;outline:none">
          </div>
          <button type="button" data-price-apply class="btn btn-sm btn-primary" style="margin-top:10px;width:100%">Apply</button>
        </div>

        <!-- Difficulty -->
        <div class="filter-section">
          <div class="filter-title">Plant Difficulty</div>
          <div class="filter-options">
            <?php foreach (['Beginner','Intermediate','Expert'] as $d): ?>
            <label class="filter-check">
              <input type="checkbox" name="difficulty[]" value="<?= $d ?>" <?= in_array($d, (array)$difficulty) ? 'checked' : '' ?>>
              <?= ['Beginner'=>'🌱 Beginner','Intermediate'=>'🌿 Intermediate','Expert'=>'🌳 Expert'][$d] ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Light -->
        <div class="filter-section">
          <div class="filter-title">Light Requirement</div>
          <div class="filter-options">
            <?php foreach (['Low','Medium','High'] as $l): ?>
            <label class="filter-check">
              <input type="checkbox" name="light[]" value="<?= $l ?>" <?= in_array($l, (array)$light) ? 'checked' : '' ?>>
              <?= ['Low'=>'🌑 Low Light','Medium'=>'🌤️ Medium Light','High'=>'☀️ High Light'][$l] ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Category (sidebar) -->
        <div class="filter-section">
          <div class="filter-title">Categories</div>
          <div class="filter-options">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= SITE_URL ?>/shop/index.php?category=<?= h($cat['slug']) ?>" class="filter-check" style="text-decoration:none;<?= $catSlug===$cat['slug']?'color:var(--green-dark);font-weight:600':'' ?>">
              <?= h($cat['image']) ?> <?= h($cat['name']) ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>

        <button type="button" data-price-apply class="btn btn-primary btn-full">Apply Filters</button>
      </aside>

      <!-- ── MAIN CONTENT ──────────────────────────────────── -->
      <main>
        <!-- Top bar -->
        <div class="shop-top-bar">
          <div style="display:flex;align-items:center;gap:12px">
            <button type="button" id="filterToggle" class="filter-toggle-btn">
              <i class="fa fa-sliders"></i> Filters
            </button>
            <div class="results-count">
              <?= $totalProducts ?> product<?= $totalProducts !== 1 ? 's' : '' ?> found
              <?php if ($q): ?> for "<strong><?= h($q) ?></strong>"<?php endif; ?>
            </div>
          </div>
          <select id="sortSelect" class="sort-select">
            <option value="newest"    <?= $sort==='newest'    ?'selected':'' ?>>Newest First</option>
            <option value="popular"   <?= $sort==='popular'   ?'selected':'' ?>>Most Popular</option>
            <option value="price_asc" <?= $sort==='price_asc' ?'selected':'' ?>>Price: Low to High</option>
            <option value="price_desc"<?= $sort==='price_desc'?'selected':'' ?>>Price: High to Low</option>
            <option value="name"      <?= $sort==='name'      ?'selected':'' ?>>Name A–Z</option>
          </select>
        </div>

        <!-- Products grid -->
        <?php if (empty($products)): ?>
          <div class="empty-state">
            <div class="empty-icon">🌿</div>
            <h3>No plants found</h3>
            <p>Try adjusting your filters or search term.</p>
            <a href="<?= SITE_URL ?>/shop/index.php" class="btn btn-primary" style="margin-top:16px">Browse All Products</a>
          </div>
        <?php else: ?>
          <div class="products-grid">
            <?php foreach ($products as $p): ?>
              <?php include __DIR__ . '/../includes/product-card.php'; ?>
            <?php endforeach; ?>
          </div>

          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
          <div class="pagination">
            <?php
            $qp = $_GET;
            if ($page > 1):
              $qp['page'] = $page - 1; ?>
              <a href="?<?= http_build_query($qp) ?>" class="page-btn"><i class="fa fa-chevron-left"></i></a>
            <?php endif;
            for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++):
              $qp['page'] = $i; ?>
              <a href="?<?= http_build_query($qp) ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor;
            if ($page < $totalPages):
              $qp['page'] = $page + 1; ?>
              <a href="?<?= http_build_query($qp) ?>" class="page-btn"><i class="fa fa-chevron-right"></i></a>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        <?php endif; ?>
      </main>
    </div>
  </form>
</div>

<script>
// ── Build current URL params helper ──────────────────────────
function getCurrentParams() {
  // Read all current GET params from PHP, not from JS (reliable)
  var params = new URLSearchParams();
  <?php if ($q):        ?>params.set('q',          '<?= addslashes($q) ?>');<?php endif; ?>
  <?php if ($catSlug):  ?>params.set('category',   '<?= addslashes($catSlug) ?>');<?php endif; ?>
  <?php if ($sale):     ?>params.set('sale',        '1');<?php endif; ?>
  <?php if ($isFeatured):   ?>params.set('featured',    '1');<?php endif; ?>
  <?php if ($isBestseller): ?>params.set('bestseller',  '1');<?php endif; ?>
  <?php if ($isNew):    ?>params.set('new',         '1');<?php endif; ?>
  // price
  var mn = document.getElementById('minPrice');
  var mx = document.getElementById('maxPrice');
  if (mn && mn.value && parseFloat(mn.value) > 0)     params.set('min_price', mn.value);
  if (mx && mx.value && parseFloat(mx.value) > 0)     params.set('max_price', mx.value);
  // difficulty checkboxes
  document.querySelectorAll('input[name="difficulty[]"]:checked').forEach(function(cb){
    params.append('difficulty[]', cb.value);
  });
  // light checkboxes
  document.querySelectorAll('input[name="light[]"]:checked').forEach(function(cb){
    params.append('light[]', cb.value);
  });
  return params;
}

// ── Sort dropdown ─────────────────────────────────────────────
document.getElementById('sortSelect').addEventListener('change', function() {
  var params = getCurrentParams();
  params.set('sort', this.value);
  window.location.href = '<?= SITE_URL ?>/shop/index.php?' + params.toString();
});

// ── Checkboxes auto-submit ────────────────────────────────────
document.querySelectorAll('input[name="difficulty[]"], input[name="light[]"]').forEach(function(cb) {
  cb.addEventListener('change', function() {
    var params = getCurrentParams();
    params.set('sort', '<?= h($sort) ?>');
    window.location.href = '<?= SITE_URL ?>/shop/index.php?' + params.toString();
  });
});

// ── Price / Filter Apply buttons ─────────────────────────────
document.querySelectorAll('[data-price-apply]').forEach(function(btn) {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    var params = getCurrentParams();
    params.set('sort', '<?= h($sort) ?>');
    window.location.href = '<?= SITE_URL ?>/shop/index.php?' + params.toString();
  });
});

// ── Price inputs Enter key ────────────────────────────────────
['minPrice','maxPrice'].forEach(function(id) {
  var el = document.getElementById(id);
  if (el) el.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      var params = getCurrentParams();
      params.set('sort', '<?= h($sort) ?>');
      window.location.href = '<?= SITE_URL ?>/shop/index.php?' + params.toString();
    }
  });
});

// ── Mobile filter sidebar ─────────────────────────────────────
document.getElementById('filterToggle').addEventListener('click', function(){
  var sb = document.getElementById('shopSidebar');
  sb.classList.toggle('mobile-open');
  document.getElementById('filterClose').style.display = sb.classList.contains('mobile-open') ? 'block' : 'none';
  document.body.style.overflow = sb.classList.contains('mobile-open') ? 'hidden' : '';
});
document.getElementById('filterClose').addEventListener('click', function(){
  document.getElementById('shopSidebar').classList.remove('mobile-open');
  this.style.display = 'none';
  document.body.style.overflow = '';
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
