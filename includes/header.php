<?php
// header.php — included at the top of every page
// Expects $pageTitle to be set before include
$cartCount    = getCartCount();
$wishCount    = getWishlistCount();
$categories   = getCategories();
$topCats      = array_filter($categories, fn($c) => $c['sort_order'] < 7);
$flash        = getFlash();
$currentPage  = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle ?? 'TiCi NatureLab') ?> — Aquascaping & Nature Plants</title>
<meta name="description" content="Premium aquatic plants, tissue culture plants, fertilizers, terrarium & paludarium items. India's finest aquascaping store.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<!-- Announcement Bar -->
<div class="announcement-bar">
  🌿 Free shipping on orders above <strong>₹999</strong> &nbsp;|&nbsp; 🧫 Fresh tissue culture plants weekly &nbsp;|&nbsp; 🚚 Pan-India delivery
</div>

<!-- ── Site Header ─────────────────────────────────────── -->
<header class="site-header">
  <div class="container">
    <div class="header-top">

      <!-- Hamburger (mobile) -->
      <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fa fa-bars"></i>
      </button>

      <!-- Logo -->
      <a href="<?= SITE_URL ?>/index.php" class="site-logo">
        <div class="logo-icon">🌿</div>
        <div class="logo-text">
          <span class="logo-name">TiCi</span>
          <span class="logo-tagline">NatureLab</span>
        </div>
      </a>

      <!-- Live Search -->
      <div class="header-search" id="searchWrap" style="position:relative">
        <form action="<?= SITE_URL ?>/shop/index.php" method="GET" autocomplete="off" onsubmit="closeSearch()" style="display:flex;width:100%;height:100%;align-items:center">
          <select name="cat" style="height:100%;padding:0 8px 0 12px;border:none;border-right:1px solid var(--border);background:transparent;color:var(--text-primary);font-size:.78rem;cursor:pointer;outline:none;min-width:120px;max-width:140px">
            <option value="">All categories</option>
            <?php foreach (getCategories() as $cat): ?>
            <option value="<?= h($cat['slug']) ?>"><?= h($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <input type="text" name="q" id="searchInput"
                 placeholder="Search plants, fertilizers…"
                 value="<?= h($_GET['q'] ?? '') ?>"
                 autocomplete="off"
                 style="flex:1;border:none;background:transparent;outline:none;padding:0 12px;font-size:.875rem;color:var(--text-primary)"
                 oninput="liveSearch(this.value)"
                 onfocus="if(this.value.length>=2) liveSearch(this.value)"
                 onkeydown="searchNav(event)">
          <button type="submit" class="search-btn"><i class="fa fa-search"></i></button>
        </form>
        <!-- Dropdown -->
        <div id="searchDrop" style="display:none;position:absolute;top:calc(100% + 8px);left:0;right:0;background:#fff;border:1px solid #e0e0e0;border-radius:14px;box-shadow:0 10px 40px rgba(0,0,0,.15);z-index:9999;overflow:hidden;max-height:520px;overflow-y:auto"></div>
      </div>

      <!-- Actions -->
      <div class="header-actions">
        <?php if (isLoggedIn()): ?>
        <a href="<?= SITE_URL ?>/account/index.php" class="hdr-btn">
          <i class="fa fa-user"></i><span>Account</span>
        </a>
        <a href="<?= SITE_URL ?>/account/wishlist.php" class="hdr-btn">
          <i class="fa fa-heart"></i><span>Wishlist</span>
          <?php if ($wishCount > 0): ?><span class="hdr-badge"><?= $wishCount ?></span><?php endif; ?>
        </a>
        <?php else: ?>
        <a href="<?= SITE_URL ?>/account/login.php" class="hdr-btn">
          <i class="fa fa-user"></i><span>Login</span>
        </a>
        <?php endif; ?>
        <a href="<?= SITE_URL ?>/cart/index.php" class="hdr-btn cart-btn">
          <i class="fa fa-cart-shopping"></i>
          <span class="cart-count"><?= $cartCount ?></span>
          <span>Cart</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Main Nav -->
  <nav class="main-nav">
    <div class="container">
      <div class="nav-inner">
        <a href="<?= SITE_URL ?>/index.php" class="nav-link <?= $currentPage==='index'?'active':'' ?>">
          <span class="nav-emoji">🏠</span> Home
        </a>
        <a href="<?= SITE_URL ?>/shop/index.php" class="nav-link <?= $currentPage==='index'&&str_contains($_SERVER['PHP_SELF'],'shop')?'active':'' ?>">
          <span class="nav-emoji">🛍️</span> Shop
        </a>
        <?php foreach ($topCats as $cat): ?>
        <a href="<?= SITE_URL ?>/shop/index.php?category=<?= h($cat['slug']) ?>" class="nav-link">
          <span class="nav-emoji"><?= h($cat['image']) ?></span> <?= h($cat['name']) ?>
        </a>
        <?php endforeach; ?>
        <a href="<?= SITE_URL ?>/pages/about.php" class="nav-link">About</a>
        <a href="<?= SITE_URL ?>/pages/contact.php" class="nav-link">Contact</a>
        <a href="<?= SITE_URL ?>/shop/index.php?sale=1" class="nav-link sale">🔥 Sale</a>
      </div>
    </div>
  </nav>
</header>

<!-- ── Mobile Nav Drawer ───────────────────────────────── -->
<div class="mobile-nav" id="mobileNav">
  <div class="mobile-nav-overlay" id="mobileNavOverlay"></div>
  <div class="mobile-nav-drawer">
    <div class="mobile-nav-header">
      <a href="<?= SITE_URL ?>/index.php" class="site-logo" onclick="closeMobileNav()">
        <div class="logo-icon" style="width:36px;height:36px;font-size:1.1rem">🌿</div>
        <div class="logo-text"><span class="logo-name">TiCi NatureLab</span></div>
      </a>
      <button class="mobile-nav-close" onclick="closeMobileNav()"><i class="fa fa-times"></i></button>
    </div>

    <!-- Mobile search -->
    <div style="padding:12px 16px;border-bottom:1px solid var(--border)">
      <form action="<?= SITE_URL ?>/shop/index.php" method="GET">
        <div style="position:relative">
          <input type="text" name="q" placeholder="Search plants…" style="width:100%;padding:10px 40px 10px 14px;border:1.5px solid var(--border-strong);border-radius:var(--radius-full);font-size:.875rem;outline:none" value="<?= h($_GET['q'] ?? '') ?>">
          <button type="submit" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);background:none;border:none"><i class="fa fa-search"></i></button>
        </div>
      </form>
    </div>

    <div class="mobile-nav-links">
      <a href="<?= SITE_URL ?>/index.php" class="mobile-nav-link" onclick="closeMobileNav()"><span class="mn-emoji">🏠</span> Home</a>
      <a href="<?= SITE_URL ?>/shop/index.php" class="mobile-nav-link" onclick="closeMobileNav()"><span class="mn-emoji">🛍️</span> All Products</a>
      <?php foreach ($categories as $cat): ?>
      <a href="<?= SITE_URL ?>/shop/index.php?category=<?= h($cat['slug']) ?>" class="mobile-nav-link" onclick="closeMobileNav()">
        <span class="mn-emoji"><?= h($cat['image']) ?></span> <?= h($cat['name']) ?>
      </a>
      <?php endforeach; ?>
      <a href="<?= SITE_URL ?>/shop/index.php?sale=1" class="mobile-nav-link" style="color:var(--terracotta)" onclick="closeMobileNav()"><span class="mn-emoji">🔥</span> Sale Items</a>
      <a href="<?= SITE_URL ?>/pages/about.php" class="mobile-nav-link" onclick="closeMobileNav()"><span class="mn-emoji">ℹ️</span> About Us</a>
      <a href="<?= SITE_URL ?>/pages/contact.php" class="mobile-nav-link" onclick="closeMobileNav()"><span class="mn-emoji">📞</span> Contact</a>
      <a href="<?= SITE_URL ?>/pages/track-order.php" class="mobile-nav-link" onclick="closeMobileNav()"><span class="mn-emoji">📦</span> Track Order</a>
    </div>
    <div class="mobile-nav-actions">
      <?php if (isLoggedIn()): ?>
      <a href="<?= SITE_URL ?>/account/index.php" class="btn btn-secondary btn-full" onclick="closeMobileNav()"><i class="fa fa-user"></i> My Account</a>
      <a href="<?= SITE_URL ?>/account/logout.php" class="btn btn-secondary btn-full" style="color:var(--terracotta);border-color:var(--terracotta)">Logout</a>
      <?php else: ?>
      <a href="<?= SITE_URL ?>/account/login.php" class="btn btn-primary btn-full" onclick="closeMobileNav()"><i class="fa fa-sign-in-alt"></i> Login</a>
      <a href="<?= SITE_URL ?>/account/register.php" class="btn btn-secondary btn-full" onclick="closeMobileNav()">Create Account</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Flash -->
<?php if ($flash): ?>
<div class="container">
  <div class="flash-msg flash-<?= h($flash['type']) ?>">
    <i class="fa fa-<?= $flash['type']==='success'?'circle-check':'circle-xmark' ?>"></i>
    <?= h($flash['msg']) ?>
  </div>
</div>
<?php endif; ?>

<script>
// ── Live Search ──────────────────────────────────────────────
var _searchTimer = null;
var _searchActive = -1; // keyboard nav index

function liveSearch(q) {
  clearTimeout(_searchTimer);
  if (q.length < 2) { closeSearch(); return; }
  _searchTimer = setTimeout(function() {
    fetch('<?= SITE_URL ?>/shop/search-suggest.php?q=' + encodeURIComponent(q))
      .then(function(r){ return r.json(); })
      .then(function(data){ renderDrop(data, q); })
      .catch(function(){});
  }, 200);
}

function renderDrop(data, q) {
  var drop = document.getElementById('searchDrop');
  if (!data.suggestions.length && !data.products.length) { closeSearch(); return; }

  var html = '';

  if (data.suggestions.length) {
    html += '<div style="padding:10px 16px 4px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#999">Suggestions</div>';
    data.suggestions.forEach(function(s, i) {
      var bold = s.replace(new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + ')', 'gi'), '<strong style="color:var(--green-dark)">$1</strong>');
      html += '<div class="sdrop-sug" data-idx="sug-' + i + '" onclick="doSearch(\'' + escQ(s) + '\')" style="padding:9px 18px;cursor:pointer;font-size:.875rem;color:var(--green-bright);display:flex;align-items:center;gap:8px">'
            + '<i class="fa fa-magnifying-glass" style="font-size:.7rem;color:#bbb;flex-shrink:0"></i>'
            + '<span>' + bold + '</span></div>';
    });
  }

  if (data.products.length) {
    html += '<div style="padding:10px 16px 4px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#999;border-top:1px solid #f0f0f0;margin-top:4px">Products</div>';
    data.products.forEach(function(p, i) {
      html += '<a class="sdrop-prod" data-idx="prod-' + i + '" href="' + p.url + '" style="display:flex;align-items:center;gap:12px;padding:9px 16px;text-decoration:none;color:inherit;border-bottom:1px solid #f8f8f8" onclick="closeSearch()">'
            + (p.thumb
                ? '<img src="' + p.thumb + '" style="width:44px;height:44px;object-fit:cover;border-radius:8px;flex-shrink:0;border:1px solid #eee" onerror="this.style.display=\'none\'">'
                : '<div style="width:44px;height:44px;border-radius:8px;background:#f3f3f3;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0">🌿</div>')
            + '<div style="flex:1;min-width:0">'
            + '<div style="font-size:.875rem;font-weight:500;color:#222;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + escHtml(p.name) + '</div>'
            + '<div style="font-size:.8rem;color:var(--green-bright);font-weight:600;margin-top:2px">' + p.price_fmt + '</div>'
            + '</div></a>';
    });
  }

  // Footer link
  html += '<a href="<?= SITE_URL ?>/shop/index.php?q=' + encodeURIComponent(q) + '" onclick="closeSearch()" style="display:block;text-align:center;padding:12px;font-size:.83rem;font-weight:600;color:var(--green-bright);border-top:1px solid #f0f0f0;background:#fafafa;text-decoration:none">Search for &ldquo;' + escHtml(q) + '&rdquo; →</a>';

  drop.innerHTML = html;
  drop.style.display = 'block';
  _searchActive = -1;
}

function doSearch(q) {
  document.getElementById('searchInput').value = q;
  var form = document.getElementById('searchInput').closest('form');
  closeSearch();
  form.submit();
}

function closeSearch() {
  var drop = document.getElementById('searchDrop');
  if (drop) { drop.style.display = 'none'; drop.innerHTML = ''; }
  _searchActive = -1;
}

// Keyboard navigation
function searchNav(e) {
  var drop = document.getElementById('searchDrop');
  if (drop.style.display === 'none') return;
  var items = drop.querySelectorAll('.sdrop-sug, .sdrop-prod');
  if (!items.length) return;

  if (e.key === 'ArrowDown') {
    e.preventDefault();
    _searchActive = Math.min(_searchActive + 1, items.length - 1);
  } else if (e.key === 'ArrowUp') {
    e.preventDefault();
    _searchActive = Math.max(_searchActive - 1, -1);
  } else if (e.key === 'Escape') {
    closeSearch(); return;
  } else if (e.key === 'Enter' && _searchActive >= 0) {
    e.preventDefault();
    items[_searchActive].click(); return;
  } else return;

  items.forEach(function(el, i) {
    el.style.background = i === _searchActive ? '#f0f9f3' : '';
  });
  if (_searchActive >= 0) items[_searchActive].scrollIntoView({block:'nearest'});
}

// Hover highlight
document.addEventListener('mouseover', function(e) {
  var el = e.target.closest('.sdrop-sug, .sdrop-prod');
  if (!el) return;
  var drop = document.getElementById('searchDrop');
  drop.querySelectorAll('.sdrop-sug, .sdrop-prod').forEach(function(x,i){
    x.style.background = x === el ? '#f0f9f3' : '';
  });
});

// Close on outside click
document.addEventListener('click', function(e) {
  if (!document.getElementById('searchWrap').contains(e.target)) closeSearch();
});

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escQ(s) {
  return s.replace(/'/g,"\\'");
}
</script>
