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
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/swiper-bundle.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/nav.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<!-- Announcement Bar -->
<div class="announcement-bar">
  <ul>
    <li><i class="fa-solid fa-indian-rupee-sign"></i> Free shipping on orders above <strong>₹999</strong></li>
    <li><i class="fa-brands fa-pagelines"></i> Fresh tissue culture plants weekly</li>
    <li><i class="fa-solid fa-truck-fast"></i> Pan-India delivery</li>
  </ul>
</div>

<!-- ── Site Header ─────────────────────────────────────── -->
<header class="site-header">
  <!-- Main Nav -->
  <div class="container-fluid">
  <nav class="navbar">
    <div class="navbar__left">
      <a href="<?= SITE_URL ?>/index.php" class="site-logo">
        <img src="<?= SITE_URL ?>/assets/img/hed-logo.svg" alt="">
      </a>
      <div class="burger" id="burger">
        <span class="burger-line"></span>
        <span class="burger-line"></span>
        <span class="burger-line"></span>
      </div>
    </div>
    <div class="navbar__center">
      <span class="overlay"></span>
      <div class="menu" id="menu">
        <ul class="menu__inner">
          <li class="menu__item"><a href="#" class="menu__link">Home</a></li>
          <li class="menu__item menu__dropdown">
            <a href="#" class="menu__link">
              Products
              <i class="fa-solid fa-chevron-down"></i>
            </a>
            <div class="submenu megamenu__text">
              <div class="submenu__inner">
                <h4 class="submenu__title">Women</h4>
                <ul class="submenu__list">
                  <li><a href="#" class="menu__link">Shirts & Blouses</a></li>
                  <li><a href="#" class="menu__link">Pants</a></li>
                  <li><a href="#" class="menu__link">Blazers & Vests</a></li>
                  <li><a href="#" class="menu__link">Cardigans & Sweaters</a></li>
                </ul>
              </div>
              <div class="submenu__inner">
                <h4 class="submenu__title">Men</h4>
                <ul class="submenu__list">
                  <li><a href="#" class="menu__link">T-shirts & Tanks</a></li>
                  <li><a href="#" class="menu__link">Pants</a></li>
                  <li><a href="#" class="menu__link">Hoodies & Sweatshirts</a></li>
                  <li><a href="#" class="menu__link">Blazers & Suits</a></li>
                </ul>
              </div>
              <div class="submenu__inner">
                <h4 class="submenu__title">Kids</h4>
                <ul class="submenu__list">
                  <li><a href="#" class="menu__link">Clothing</a></li>
                  <li><a href="#" class="menu__link">Outerwear</a></li>
                  <li><a href="#" class="menu__link">Activewear</a></li>
                  <li><a href="#" class="menu__link">Accessories</a></li>
                </ul>
              </div>
              <div class="submenu__inner">
                <h4 class="submenu__title">Sport</h4>
                <ul class="submenu__list">
                  <li><a href="#" class="menu__link">Clothing</a></li>
                  <li><a href="#" class="menu__link">Swimwear</a></li>
                  <li><a href="#" class="menu__link">Outerwear</a></li>
                  <li><a href="#" class="menu__link">Accessories & Equipment</a></li>
                </ul>
              </div>
            </div>
          </li>
          <li class="menu__item menu__dropdown">
            <a href="#" class="menu__link">
              Category
              <i class="fa-solid fa-chevron-down"></i>
            </a>
            <ul class="submenu megamenu__normal">
              <li class="menu__item">
                <a href="#" class="menu__link">
                  Women
                  <i class="fa-solid fa-chevron-down"></i>
                </a>
                <ul class="submenu">
                  <li><a href="#" class="menu__link">Dresses</a></li>
                  <li><a href="#" class="menu__link">Tops</a></li>
                  <li><a href="#" class="menu__link">Bottoms</a></li>
                  <li><a href="#" class="menu__link">Outerwear</a></li>
                </ul>
              </li>
              <li class="menu__item">
                <a href="#" class="menu__link">
                  Men
                  <i class="fa-solid fa-chevron-down"></i>
                </a>
                <ul class="submenu">
                  <li class="menu__item">
                    <a href="#" class="menu__link">
                      Shirts
                      <i class="fa-solid fa-chevron-down"></i>
                    </a>
                    <ul class="submenu">
                      <li class="menu__item">
                        <a href="#" class="menu__link">
                          Casual Shirts
                          <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <ul class="submenu">
                          <li><a href="#" class="menu__link">Polo Shirts</a></li>
                          <li><a href="#" class="menu__link">T-Shirts</a></li>
                          <li><a href="#" class="menu__link">Henley Shirts</a></li>
                          <li><a href="#" class="menu__link">Tank Tops</a></li>
                        </ul>
                      </li>
                      <li class="menu__item">
                        <a href="#" class="menu__link">
                          Formal Shirts
                          <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <ul class="submenu">
                          <li><a href="#" class="menu__link">Dress Shirts</a></li>
                          <li><a href="#" class="menu__link">Oxford Shirts</a></li>
                          <li><a href="#" class="menu__link">Linen Shirts</a></li>
                          <li><a href="#" class="menu__link">Flannel Shirts</a></li>
                        </ul>
                      </li>
                      <li><a href="#" class="menu__link">Button-Down Shirts</a></li>
                      <li><a href="#" class="menu__link">Hawaiian Shirts</a></li>
                    </ul>
                  </li>
                  <li class="menu__item">
                    <a href="#" class="menu__link">
                      Pants
                      <i class="fa-solid fa-chevron-down"></i>
                    </a>
                    <ul class="submenu">
                      <li class="menu__item">
                        <a href="#" class="menu__link">
                          Jeans
                          <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <ul class="submenu">
                          <li><a href="#" class="menu__link">Slim Fit</a></li>
                          <li><a href="#" class="menu__link">Regular Fit</a></li>
                          <li><a href="#" class="menu__link">Relaxed Fit</a></li>
                          <li><a href="#" class="menu__link">Bootcut</a></li>
                        </ul>
                      </li>
                      <li class="menu__item">
                        <a href="#" class="menu__link">
                          Chinos
                          <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <ul class="submenu">
                          <li><a href="#" class="menu__link">Classic Chinos</a></li>
                          <li><a href="#" class="menu__link">Slim Chinos</a></li>
                          <li><a href="#" class="menu__link">Cargo Chinos</a></li>
                          <li><a href="#" class="menu__link">Performance Chinos</a></li>
                        </ul>
                      </li>
                      <li><a href="#" class="menu__link">Dress Pants</a></li>
                      <li><a href="#" class="menu__link">Shorts</a></li>
                    </ul>
                  </li>
                  <li><a href="#" class="menu__link">Jackets</a></li>
                  <li><a href="#" class="menu__link">Suits</a></li>
                </ul>
              </li>
              <li class="menu__item">
                <a href="#" class="menu__link">
                  Kids
                  <i class="fa-solid fa-chevron-down"></i>
                </a>
                <ul class="submenu">
                  <li class="menu__item">
                    <a href="#" class="menu__link">
                      Boys
                      <i class="fa-solid fa-chevron-down"></i>
                    </a>
                    <ul class="submenu">
                      <li class="menu__item">
                        <a href="#" class="menu__link">
                          Ages 2-4
                          <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <ul class="submenu">
                          <li><a href="#" class="menu__link">T-Shirts</a></li>
                          <li><a href="#" class="menu__link">Pants</a></li>
                          <li><a href="#" class="menu__link">Shorts</a></li>
                          <li><a href="#" class="menu__link">Sweaters</a></li>
                        </ul>
                      </li>
                      <li class="menu__item">
                        <a href="#" class="menu__link">
                          Ages 5-7
                          <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <ul class="submenu">
                          <li><a href="#" class="menu__link">T-Shirts</a></li>
                          <li><a href="#" class="menu__link">Jeans</a></li>
                          <li><a href="#" class="menu__link">Hoodies</a></li>
                          <li><a href="#" class="menu__link">Jackets</a></li>
                        </ul>
                      </li>
                      <li><a href="#" class="menu__link">Ages 8-10</a></li>
                      <li><a href="#" class="menu__link">Ages 11-13</a></li>
                    </ul>
                  </li>
                  <li class="menu__item">
                    <a href="#" class="menu__link">
                      Girls
                      <i class="fa-solid fa-chevron-down"></i>
                    </a>
                    <ul class="submenu">
                      <li class="menu__item">
                        <a href="#" class="menu__link">
                          Ages 2-4
                          <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <ul class="submenu">
                          <li><a href="#" class="menu__link">Dresses</a></li>
                          <li><a href="#" class="menu__link">Tops</a></li>
                          <li><a href="#" class="menu__link">Leggings</a></li>
                          <li><a href="#" class="menu__link">Sweaters</a></li>
                        </ul>
                      </li>
                      <li class="menu__item">
                        <a href="#" class="menu__link">
                          Ages 5-7
                          <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <ul class="submenu">
                          <li><a href="#" class="menu__link">Dresses</a></li>
                          <li><a href="#" class="menu__link">T-Shirts</a></li>
                          <li><a href="#" class="menu__link">Jeans</a></li>
                          <li><a href="#" class="menu__link">Hoodies</a></li>
                        </ul>
                      </li>
                      <li><a href="#" class="menu__link">Ages 8-10</a></li>
                      <li><a href="#" class="menu__link">Ages 11-13</a></li>
                    </ul>
                  </li>
                  <li><a href="#" class="menu__link">Baby (0-24 months)</a></li>
                  <li><a href="#" class="menu__link">Accessories</a></li>
                </ul>
              </li>
              <li class="menu__item">
                <a href="#" class="menu__link">
                  Sport
                  <i class="fa-solid fa-chevron-down"></i>
                </a>
                <ul class="submenu">
                  <li><a href="#" class="menu__link">Running</a></li>
                  <li><a href="#" class="menu__link">Basketball</a></li>
                  <li><a href="#" class="menu__link">Soccer</a></li>
                  <li><a href="#" class="menu__link">Swimming</a></li>
                </ul>
              </li>
            </ul>
          </li>
          <li class="menu__item menu__dropdown">
            <a href="#" class="menu__link">
              More
              <i class="fa-solid fa-chevron-down"></i>
            </a>
            <div class="submenu megamenu__image">
              <div class="submenu__inner">
                <a href="#">
                  <img src="https://plus.unsplash.com/premium_photo-1677013011737-ba61149ba70c?q=80&w=1740&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="submenu-image" alt="">
                  <span class="submenu__title">Home</span>
                </a>
              </div>
              <div class="submenu__inner">
                <a href="#">
                  <img src="https://images.unsplash.com/photo-1515688594390-b649af70d282?q=80&w=1612&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="submenu-image" alt="">
                  <span class="submenu__title">Beauty</span>
                </a>
              </div>
              <div class="submenu__inner">
                <a href="#">
                  <img src="https://plus.unsplash.com/premium_photo-1676550886096-bfc64aae1e2a?q=80&w=1740&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="submenu-image" alt="">
                  <span class="submenu__title">Holiday</span>
                </a>
              </div>
              <div class="submenu__inner">
                <a href="#">
                  <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=1740&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="submenu-image" alt="">
                  <span class="submenu__title">Sale</span>
                </a>
              </div>
            </div>
          </li>
          <li class="menu__item menu__dropdown">
            <a href="#" class="menu__link">
              Account
              <i class="fa-solid fa-chevron-down"></i>
            </a>
            <ul class="submenu megamenu__normal">
              <li><a href="#" class="menu__link">Login</a></li>
              <li><a href="#" class="menu__link">Register</a></li>
              <li><a href="#" class="menu__link">Track Order</a></li>
              <li><a href="#" class="menu__link">Help</a></li>
            </ul>
          </li>
          <li class="menu__item"><a href="#" class="menu__link">Support</a></li>
        </ul>
      </div>
    </div>
    <div class="navbar__right">
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
  </nav>
  </div>  
          <div class="header-bottom">
              <div class="container">
    <div class="header-top">

      <!-- Live Search -->
      <div class="header-search" id="searchWrap" style="position:relative">
        <form action="<?= SITE_URL ?>/shop/index.php" method="GET" autocomplete="off" onsubmit="closeSearch()" style="display:flex;width:100%;height:100%;align-items:center">
          <select name="cat">
            <option value="">All categories</option>
            <?php foreach (getCategories() as $cat): ?>
            <option value="<?= h($cat['slug']) ?>"><?= h($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <input type="text" name="q" id="searchInput"
                 placeholder="Search plants, fertilizers…"
                 value="<?= h($_GET['q'] ?? '') ?>"
                 autocomplete="off"
                 oninput="liveSearch(this.value)"
                 onfocus="if(this.value.length>=2) liveSearch(this.value)"
                 onkeydown="searchNav(event)">
          <button type="submit" class="search-btn"><i class="fa fa-search"></i></button>
        </form>
        <!-- Dropdown -->
        <div id="searchDrop" class="nav-drop-item"></div>
      </div>


    </div>
  </div>   
          </div>
</header>



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

  var html = '<div class="nav-drop-item-scroll">';

  if (data.suggestions.length) {
    html += '<div class="nv-hd">Suggestions</div>';
    data.suggestions.forEach(function(s, i) {
      var bold = s.replace(new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + ')', 'gi'), '<strong style="color:var(--green-dark)">$1</strong>');
      html += '<div class="sdrop-sug" data-idx="sug-' + i + '" onclick="doSearch(\'' + escQ(s) + '\')" style="padding:9px 18px;cursor:pointer;font-size:.875rem;color:var(--green-bright);display:flex;align-items:center;gap:8px">'
            + '<i class="fa fa-magnifying-glass" style="font-size:.7rem;color:#bbb;flex-shrink:0"></i>'
            + '<span>' + bold + '</span></div>';
    });
  }

  if (data.products.length) {
    html += '<div class="nv-hd">Products</div>';
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

  html += '</div>';
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
