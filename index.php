<?php
require_once __DIR__ . '/includes/config.php';
$db = db(); // init tables

$pageTitle = 'TiCi NatureLab — Premium Aquatic & Nature Plants';
$featured   = getFeaturedProducts(8);
$bestsellers= getBestsellers(8);
$newArrivals= getNewArrivals(8);
$categories = getCategories();

// Reviews (static for now — in production from DB)
$reviews = [
    ['name'=>'Rahul M.','rating'=>5,'text'=>'Absolutely stunning plants! The Anubias Nana Petite I ordered arrived healthy and well-packed. My aquarium looks like a professional setup now.','date'=>'2 weeks ago'],
    ['name'=>'Priya S.','rating'=>5,'text'=>'Best tissue culture plants in India. Zero algae, super clean, and melted perfectly into my substrate. TiCi is my go-to store now.','date'=>'1 month ago'],
    ['name'=>'Arjun K.','rating'=>5,'text'=>'The APT fertilizers worked magic on my plants. Huge difference in growth within 2 weeks. Excellent quality and fast delivery!','date'=>'3 weeks ago'],
    ['name'=>'Deepa R.','rating'=>4,'text'=>'Ordered terrarium moss and it arrived beautifully packaged. Very responsive customer support. Will definitely order again!','date'=>'1 week ago'],
    ['name'=>'Sanjay P.','rating'=>5,'text'=>'Monte Carlo TC carpeted perfectly! No melt at all. The quality control at TiCi is exceptional. Highly recommend to any aquascaper.','date'=>'2 months ago'],
    ['name'=>'Anita L.','rating'=>5,'text'=>'Gorgeous vertical garden plants. My balcony looks like a jungle retreat. Everything arrived fresh and healthy. Amazing service!','date'=>'3 weeks ago'],
];

// Blog posts (static/from DB)
$blogs = $db->query("SELECT * FROM blogs WHERE status=1 ORDER BY created_at DESC LIMIT 3")->fetchAll();
$staticBlogs = [
    ['emoji'=>'🌿','cat'=>'Plant Care','title'=>'Beginner\'s Guide to Planted Aquariums','excerpt'=>'Everything you need to know to start your first planted tank — lighting, substrate, CO2, and the easiest plants to begin with.','date'=>'Mar 2026'],
    ['emoji'=>'🧫','cat'=>'Tissue Culture','title'=>'Why Tissue Culture Plants Are Superior','excerpt'=>'TC plants arrive clean, pest-free, and adapted to submersed growth. Here\'s why they\'re the future of planted tanks.','date'=>'Feb 2026'],
    ['emoji'=>'💧','cat'=>'Fertilizers','title'=>'The Complete Fertilizer Guide for Planted Tanks','excerpt'=>'Macro vs micro, EI dosing, PPS-Pro — we break down every fertilizer method and which plants need what nutrients.','date'=>'Feb 2026'],
];

// Banners from DB
$banners = $db->query("SELECT * FROM banners WHERE status=1 ORDER BY sort_order, id")->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<section class="banner">
    <div class="banner-wrap">
        <div class="container">
          <div class="js-banner-slider">
            <div class="banner-slide">
              <div class="row">
                <div class="col-md-6">
                  <div class="banner-slide-col">
                    <h1>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aspernatur, debitis!</h1>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Delectus expedita aspernatur, culpa quae placeat incidunt sit alias tenetur nisi quisquam.</p>
                    <a href="#" class="cmn-btn">Shop Collection</a>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="banner-slide-col">
                    <div class="banner-slide-col-img">
                      <img src="<?= SITE_URL ?>/assets/img/banner-img1.png" alt="">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="banner-slide">
              <div class="row">
                <div class="col-md-6">
                  <div class="banner-slide-col">
                    <h1>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aspernatur, debitis!</h1>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Delectus expedita aspernatur, culpa quae placeat incidunt sit alias tenetur nisi quisquam.</p>
                    <a href="#" class="cmn-btn">Shop Collection</a>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="banner-slide-col">
                    <div class="banner-slide-col-img">
                      <img src="<?= SITE_URL ?>/assets/img/banner-img2.png" alt="">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="banner-slide">
              <div class="row">
                <div class="col-md-6">
                  <div class="banner-slide-col">
                    <h1>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aspernatur, debitis!</h1>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Delectus expedita aspernatur, culpa quae placeat incidunt sit alias tenetur nisi quisquam.</p>
                    <a href="#" class="cmn-btn">Shop Collection</a>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="banner-slide-col">
                    <div class="banner-slide-col-img">
                      <img src="<?= SITE_URL ?>/assets/img/banner-img3.png" alt="">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <img src="<?= SITE_URL ?>/assets/img/weve-banner.svg" class="weve-banner" alt="">
    </div>
</section>
<!-- ══════════════════════════════════════════════════════════
     HERO / BANNER SLIDER
══════════════════════════════════════════════════════════ -->
<?php if (!empty($banners)): ?>
<!-- ── DB Banner Slider ───────────────────────────────────── -->


<?php else: ?>
<!-- ── Static Hero (no banners in DB) ────────────────────── -->
<section class="hero">
  <div class="hero-bg-pattern"></div>
  <div class="hero-leaves"></div>
  <div class="container">
    <div class="hero-content">
      <div class="hero-kicker fade-in-up">
        <span>🌿</span> India's #1 Aquascaping Store
      </div>
      <h1 class="hero-title fade-in-up-2">
        Bring Nature<br><em>Indoors</em> with<br>Live Plants
      </h1>
      <p class="hero-desc fade-in-up-3">
        Premium tissue culture plants, aquatic plants, fertilizers, and terrarium essentials. Curated for planted tank enthusiasts and nature lovers across India.
      </p>
      <div class="hero-actions fade-in-up-3">
        <a href="<?= SITE_URL ?>/shop/index.php" class="btn btn-amber btn-lg">
          <i class="fa fa-leaf"></i> Shop Now
        </a>
        <a href="<?= SITE_URL ?>/shop/index.php?category=tissue-culture" class="btn btn-secondary btn-lg" style="border-color:rgba(255,255,255,.4);color:#fff">
          🧫 Tissue Culture
        </a>
      </div>
      <div class="hero-stats fade-in-up-3">
        <div><div class="hero-stat-val">500+</div><div class="hero-stat-label">Plant Species</div></div>
        <div><div class="hero-stat-val">10K+</div><div class="hero-stat-label">Happy Customers</div></div>
        <div><div class="hero-stat-val">Pan-India</div><div class="hero-stat-label">Delivery</div></div>
        <div><div class="hero-stat-val">100%</div><div class="hero-stat-label">Live Guarantee</div></div>
      </div>
    </div>
  </div>
 
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════
     TRUST STRIP
══════════════════════════════════════════════════════════ -->
<div class="trust-strip">
  <div class="container-fluid">
    <div class="trust-strip-wrap">
      <div class="row">
        <div class="col-3 trust-col">
            <div class="trust-icon"><img src="<?= SITE_URL ?>/assets/img/icon-1.png" alt=""></div>
            <div class="trust-text">Free shipping above ₹999</div>
        </div>
        <div class="col-3 trust-col">
            <div class="trust-icon"><img src="<?= SITE_URL ?>/assets/img/icon-2.png" alt=""></div>
            <div class="trust-text">100% Live plant guarantee</div>
        </div>
        <div class="col-3 trust-col">
            <div class="trust-icon"><img src="<?= SITE_URL ?>/assets/img/icon-3.png" alt=""></div>
            <div class="trust-text">Secure payments</div>
        </div>
        <div class="col-3 trust-col">
            <div class="trust-icon"><img src="<?= SITE_URL ?>/assets/img/icon-4.png" alt=""></div>
            <div class="trust-text">Easy returns</div>
        </div>
        <div class="col-3 trust-col">
            <div class="trust-icon"><img src="<?= SITE_URL ?>/assets/img/icon-5.png" alt=""></div>
            <div class="trust-text">Expert plant support</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     CATEGORIES CAROUSEL
══════════════════════════════════════════════════════════ -->
<section class="cat-carousel-section">
  <div class="container-fluid">
    
    <div class="section-header-row">
      <div class="section-header">
        <h2 class="carousel-title">Best for your categories <span><b>23 categories</b> belonging to a total of <b>34,592 products</b></span></h2>
      </div>
      <div style="display:flex;align-items:center;gap:17px">
        <button class="cmn-btn-arrow car-arrow-prev"></button>
        <button class="cmn-btn-arrow car-arrow-next"></button>
        <!-- <a href="<?= SITE_URL ?>/shop/categories.php" class="cmn-btn">View All</a> -->
      </div>
    </div>

    <div class="cat-carousel-viewport" id="catCarousel">
      <div class="cat-carousel-track" id="catTrack">
        <!-- All Products -->
        <a href="<?= SITE_URL ?>/shop/index.php" class="cat-card">
          <div class="cat-card-img"><span><img src="<?= SITE_URL ?>/assets/img/emty-img.jpg" alt=""></span></div>
          <div class="cat-card-body">
            <div class="cat-card-name">All Products</div>
          </div>
        </a>
        <?php foreach ($categories as $cat): ?>
        <a href="<?= SITE_URL ?>/shop/categories.php?category=<?= h($cat['slug']) ?>" class="cat-card">
          <div class="cat-card-img">
            <?php if (!empty($cat['image']) && strlen($cat['image']) <= 8): ?>
              <span><?= h($cat['image']) ?></span>
            <?php elseif (!empty($cat['image'])): ?>
              <img src="<?= h(imgUrl($cat['image'])) ?>" alt="<?= h($cat['name']) ?>">
            <?php else: ?>
              <span>🌿</span>
            <?php endif; ?>
          </div>
          <div class="cat-card-body">
            <div class="cat-card-name"><?= h($cat['name']) ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <img src="<?= SITE_URL ?>/assets/img/rigt-wave.svg" class="rigt-wave" alt="">
</section>

<!-- ══════════════════════════════════════════════════════════
     PRODUCT CAROUSELS — Featured / Best Sellers / New Arrivals
══════════════════════════════════════════════════════════ -->
<?php
// Fallback — if no featured/bestsellers, show all products
if (empty($featured)) {
    $featured = db()->query("SELECT p.*, COALESCE(pi.image_path,pi.image,'') as thumb, pp.difficulty, pp.light_requirement FROM products p LEFT JOIN product_images pi ON pi.product_id=p.id AND pi.is_primary=1 LEFT JOIN plant_properties pp ON pp.product_id=p.id WHERE p.status=1 ORDER BY p.created_at DESC LIMIT 8")->fetchAll();
}
if (empty($bestsellers)) { $bestsellers = $featured; }
?>

<?php
$prodSections = [
  ['id'=>'featured',    'label'=>'Our Collection', 'title'=>'Featured',     'emoji'=>'⭐', 'data'=>$featured,    'url'=>SITE_URL.'/shop/index.php?featured=1'],
  ['id'=>'bestsellers', 'label'=>'Top Picks',      'title'=>'Best Sellers', 'emoji'=>'🔥', 'data'=>$bestsellers, 'url'=>SITE_URL.'/shop/index.php?bestseller=1'],
  ['id'=>'newarrivals', 'label'=>'Just In',        'title'=>'New Arrivals', 'emoji'=>'✨', 'data'=>$newArrivals, 'url'=>SITE_URL.'/shop/index.php?new=1'],
];
foreach ($prodSections as $sec):
  if (empty($sec['data'])) continue;
?>
<section class="prod-carousel-section <?= $sec['id']==='featured' ? '' : 'prod-carousel-alt' ?> <?= $sec['id']==='newarrivals' ? 'prod-carousel-new' : '' ?>">
  <div class="container-fluid">
    <div class="prod-carousel-header">
      <div>
        <div class="section-label"><?= $sec['label'] ?></div>
        <h2><?= $sec['title'] ?></h2>
      </div>
      <div style="display:flex;align-items:center;gap:10px">
        <button class="car-arrow" onclick="prodCar('<?= $sec['id'] ?>',-1)">&#8249;</button>
        <button class="car-arrow" onclick="prodCar('<?= $sec['id'] ?>',1)">&#8250;</button>
        <a href="<?= $sec['url'] ?>" class="cmn-btn">View All</a>
      </div>
    </div>
    <div class="prod-carousel-viewport">
      <div class="prod-carousel-track" id="ptrack-<?= $sec['id'] ?>">
        <?php foreach ($sec['data'] as $p): ?>
        <div class="prod-carousel-item">
          <?php include __DIR__ . '/includes/product-card.php'; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
    <img src="<?= SITE_URL ?>/assets/img/left-weve.svg" class="left-wave" alt="">
</section>
<?php endforeach; ?>

<!-- ══════════════════════════════════════════════════════════
     PROMO BANNER
══════════════════════════════════════════════════════════ -->
<section style="background:linear-gradient(135deg,#f0f9f3,#e8f5ed);padding:50px 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
      <div style="background:var(--green-dark);border-radius:var(--radius-lg);padding:36px;position:relative;overflow:hidden">
        <div style="position:absolute;right:-20px;top:-20px;font-size:6rem;opacity:.15">🧫</div>
        <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1.5px;color:var(--green-light);font-weight:700;margin-bottom:8px">Bestseller</div>
        <h3 style="font-family:var(--font-display);font-size:1.4rem;color:#fff;margin-bottom:10px">Tissue Culture Plants</h3>
        <p style="color:rgba(255,255,255,.7);font-size:.875rem;margin-bottom:20px">Pest-free, algae-free, tissue culture plants. Perfect start for your planted tank.</p>
        <a href="<?= SITE_URL ?>/shop/index.php?category=tissue-culture" class="btn btn-amber btn-sm">Shop TC Plants</a>
      </div>
      <div style="background:linear-gradient(135deg,#e9a83a,#c4852a);border-radius:var(--radius-lg);padding:36px;position:relative;overflow:hidden">
        <div style="position:absolute;right:-20px;top:-20px;font-size:6rem;opacity:.15">💧</div>
        <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1.5px;color:rgba(255,255,255,.8);font-weight:700;margin-bottom:8px">Up to 20% Off</div>
        <h3 style="font-family:var(--font-display);font-size:1.4rem;color:#fff;margin-bottom:10px">Fertilizer Bundle</h3>
        <p style="color:rgba(255,255,255,.8);font-size:.875rem;margin-bottom:20px">Get the complete nutrition package — macros, micros, and iron for explosive plant growth.</p>
        <a href="<?= SITE_URL ?>/shop/index.php?category=fertilizers" class="btn btn-dark btn-sm">Shop Fertilizers</a>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     WHY CHOOSE TICI
══════════════════════════════════════════════════════════ -->
<section class="section" style="background:var(--bg-card);border-bottom:1px solid var(--border)">
  <div class="container">
    <div class="section-header">
      <div class="section-label">Why TiCi</div>
      <h2 class="section-title">The TiCi Difference</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:24px">
      <?php foreach ([
        ['🌱','Live Arrival Guarantee','Every plant ships with a live guarantee. If it arrives damaged, we replace it. No questions asked.'],
        ['🧫','Tissue Culture Quality','Our TC plants are grown in sterile labs — zero pests, zero algae, 100% submersed-adapted.'],
        ['🚚','Careful Packaging','Plants are heat-packed in insulated boxes with moisture-retaining materials for safe delivery.'],
        ['💬','Expert Aquascaping Support','Our team of planted tank enthusiasts is always ready to help you choose and care for your plants.'],
        ['🔬','Acclimatized Plants','All plants are acclimatized at our facility before dispatch — less shock, faster growth in your tank.'],
        ['🎁','Loyalty Rewards','Earn points on every order and redeem them for discounts on future purchases.'],
      ] as [$icon, $title, $desc]): ?>
      <div style="text-align:center;padding:28px 20px;background:var(--green-ghost);border:1px solid var(--border);border-radius:var(--radius-lg)">
        <div style="font-size:2.2rem;margin-bottom:14px"><?= $icon ?></div>
        <h4 style="font-family:var(--font-display);font-size:1rem;color:var(--green-dark);margin-bottom:8px"><?= $title ?></h4>
        <p style="font-size:.83rem;color:var(--text-muted);line-height:1.65"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     CUSTOMER REVIEWS CAROUSEL
══════════════════════════════════════════════════════════ -->
<section class="section testimonials">
  <div class="container">
    <div class="section-header-row" style="margin-bottom:32px">
      <div class="section-header" style="text-align:left;margin-bottom:0">
        <div class="section-label">Reviews</div>
        <h2 class="section-title" style="margin-bottom:4px">What Our Customers Say</h2>
        <p class="section-subtitle" style="margin-bottom:0">Trusted by thousands of aquascaping enthusiasts across India</p>
      </div>
      <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
        <button class="car-arrow car-arrow-light" onclick="slideCar('reviews',-1)">&#8249;</button>
        <button class="car-arrow car-arrow-light" onclick="slideCar('reviews',1)">&#8250;</button>
      </div>
    </div>

    <!-- Stars summary bar -->
    <div class="review-summary-bar">
      <div class="rsb-score">4.9</div>
      <div>
        <div class="rsb-stars">★★★★★</div>
        <div class="rsb-count">Based on <?= count($reviews) ?>+ verified reviews</div>
      </div>
      <div class="rsb-bars">
        <?php foreach ([5=>92,4=>6,3=>1,2=>1,1=>0] as $star=>$pct): ?>
        <div class="rsb-row">
          <span><?= $star ?>★</span>
          <div class="rsb-bar"><div class="rsb-fill" style="width:<?= $pct ?>%;background:<?= $star>=4?'var(--amber)':'#ccc' ?>"></div></div>
          <span><?= $pct ?>%</span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Carousel -->
    <div class="hcar-viewport" style="margin-top:32px">
      <div class="hcar-track" id="hcar-reviews">
        <?php foreach ($reviews as $i => $r): ?>
        <div class="review-card-new">
          <div class="rcn-top">
            <div class="review-stars"><?= str_repeat('★', $r['rating']) ?><?= str_repeat('☆', 5 - $r['rating']) ?></div>
            <div class="rcn-verified"><i class="fa fa-circle-check"></i> Verified</div>
          </div>
          <p class="review-text">"<?= h($r['text']) ?>"</p>
          <div class="review-author">
            <div class="review-avatar"><?= strtoupper(substr($r['name'],0,1)) ?></div>
            <div>
              <div class="review-name"><?= h($r['name']) ?></div>
              <div class="review-date"><?= h($r['date']) ?></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Dots -->
    <div class="hcar-dots" id="dots-reviews">
      <?php foreach ($reviews as $i => $r): ?>
      <button class="hcar-dot <?= $i===0?'active':'' ?>" onclick="slideCar('reviews',0,<?= $i ?>)"></button>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     BLOG CAROUSEL
══════════════════════════════════════════════════════════ -->
<section class="section" style="background:var(--cream)">
  <div class="container">
    <div class="section-header-row" style="margin-bottom:28px">
      <div class="section-header" style="text-align:left;margin-bottom:0">
        <div class="section-label">Knowledge Base</div>
        <h2 class="section-title" style="margin-bottom:0">Plant Care Blog</h2>
      </div>
      <div style="display:flex;align-items:center;gap:10px">
        <button class="car-arrow" onclick="slideCar('blog',-1)">&#8249;</button>
        <button class="car-arrow" onclick="slideCar('blog',1)">&#8250;</button>
        <a href="<?= SITE_URL ?>/pages/blog.php" class="btn btn-secondary btn-sm">All Articles</a>
      </div>
    </div>

    <div class="hcar-viewport">
      <div class="hcar-track" id="hcar-blog">
        <?php
        $allBlogs = !empty($blogs) ? $blogs : $staticBlogs;
        // Merge DB blogs with static fallbacks if needed
        $displayBlogs = $allBlogs;
        if (count($displayBlogs) < 3) {
            foreach ($staticBlogs as $sb) { $displayBlogs[] = $sb; }
        }
        foreach ($displayBlogs as $b):
          $isDb = isset($b['slug']); // DB blog has slug
        ?>
        <article class="blog-card-new">
          <div class="bcn-img">
            <?php if ($isDb && !empty($b['image'])): ?>
              <img src="<?= h(imgUrl($b['image'])) ?>" alt="<?= h($b['title']) ?>">
            <?php else: ?>
              <span><?= $b['emoji'] ?? '🌿' ?></span>
            <?php endif; ?>
            <div class="bcn-cat"><?= h($isDb ? ($b['category'] ?? 'Plant Care') : $b['cat']) ?></div>
          </div>
          <div class="bcn-body">
            <h3 class="bcn-title"><?= h($b['title']) ?></h3>
            <p class="bcn-excerpt"><?= h($b['excerpt'] ?? '') ?></p>
            <div class="bcn-footer">
              <span class="bcn-date"><i class="fa fa-calendar-days"></i> <?= h($isDb ? date('M Y', strtotime($b['created_at'])) : $b['date']) ?></span>
              <a href="<?= $isDb ? SITE_URL.'/pages/blog.php?slug='.h($b['slug']) : SITE_URL.'/pages/blog.php' ?>" class="bcn-link">Read more <i class="fa fa-arrow-right"></i></a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Dots -->
    <div class="hcar-dots" id="dots-blog">
      <?php $blogCount = min(count($displayBlogs), 6); for ($i=0;$i<$blogCount;$i++): ?>
      <button class="hcar-dot <?= $i===0?'active':'' ?>" onclick="slideCar('blog',0,<?= $i ?>)"></button>
      <?php endfor; ?>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     NEWSLETTER
══════════════════════════════════════════════════════════ -->
<section class="newsletter-section">
  <div class="container">
    <div>🌿</div>
    <h2>Join the TiCi Community</h2>
    <p>Get plant care tips, new arrivals, exclusive offers, and aquascaping inspiration delivered to your inbox.</p>
    <form class="newsletter-form" onsubmit="event.preventDefault(); showToast('🎉 You\'re subscribed!','success')">
      <input type="email" placeholder="Enter your email address" required>
      <button type="submit" class="btn btn-amber">Subscribe</button>
    </form>
  </div>
</section>

<script>

// ── Product Carousels ─────────────────────────────────────────
var _ppos = {};
function prodCar(id, dir) {
  var track = document.getElementById('ptrack-' + id);
  if (!track) return;
  if (!_ppos[id]) _ppos[id] = 0;
  var card = track.querySelector('.prod-carousel-item');
  var step = card ? (card.offsetWidth + 24) * 2 : 400;
  var max  = Math.max(0, track.scrollWidth - track.parentElement.offsetWidth);
  _ppos[id] = Math.max(0, Math.min(_ppos[id] + dir * step, max));
  track.style.transform = 'translateX(-' + _ppos[id] + 'px)';
}

// ── Generic horizontal carousel (reviews + blog) ──────────────
var _hpos = {};
function slideCar(id, dir, goTo) {
  var track = document.getElementById('hcar-' + id);
  if (!track) return;
  if (!_hpos[id]) _hpos[id] = { pos: 0, idx: 0 };
  var state = _hpos[id];

  var card = track.firstElementChild;
  var cardW = card ? card.offsetWidth + 24 : 340;
  var visW  = track.parentElement.offsetWidth;
  var perView = Math.max(1, Math.floor(visW / cardW));
  var total = track.children.length;
  var maxIdx = Math.max(0, total - perView);

  if (typeof goTo !== 'undefined') {
    state.idx = Math.min(goTo, maxIdx);
  } else {
    state.idx = Math.max(0, Math.min(state.idx + dir, maxIdx));
  }

  state.pos = state.idx * cardW;
  track.style.transform = 'translateX(-' + state.pos + 'px)';

  // Update dots
  var dots = document.querySelectorAll('#dots-' + id + ' .hcar-dot');
  dots.forEach(function(d, i) { d.classList.toggle('active', i === state.idx); });
}

// Touch swipe for review/blog carousels
['reviews','blog'].forEach(function(id) {
  var vp = document.getElementById('hcar-' + id);
  if (!vp) return;
  var sx = 0;
  vp.parentElement.addEventListener('touchstart', function(e){ sx = e.touches[0].clientX; }, {passive:true});
  vp.parentElement.addEventListener('touchend', function(e){
    var dx = e.changedTouches[0].clientX - sx;
    if (Math.abs(dx) > 40) slideCar(id, dx < 0 ? 1 : -1);
  });
});

// ── Banner Slider ─────────────────────────────────────────────
(function() {
  var slides = document.querySelectorAll('.banner-slide');
  var dots   = document.querySelectorAll('.banner-dot');
  if (!slides.length) return;
  var cur = 0, timer;

  function goTo(n) {
    slides[cur].classList.remove('active');
    if (dots[cur]) dots[cur].classList.remove('active');
    cur = (n + slides.length) % slides.length;
    slides[cur].classList.add('active');
    if (dots[cur]) dots[cur].classList.add('active');
  }
  function startAuto() { timer = setInterval(function(){ goTo(cur + 1); }, 5000); }
  function resetAuto()  { clearInterval(timer); startAuto(); }

  window.bannerMove = function(dir) { goTo(cur + dir); resetAuto(); };
  window.bannerGo   = function(n)   { goTo(n); resetAuto(); };

  if (slides.length > 1) startAuto();

  // Swipe support
  var startX = 0;
  var sl = document.getElementById('bannerSlider');
  if (sl) {
    sl.addEventListener('touchstart', function(e){ startX = e.touches[0].clientX; }, {passive:true});
    sl.addEventListener('touchend',   function(e){
      var dx = e.changedTouches[0].clientX - startX;
      if (Math.abs(dx) > 40) { bannerMove(dx < 0 ? 1 : -1); }
    });
  }
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
