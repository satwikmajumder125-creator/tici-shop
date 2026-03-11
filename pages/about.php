<?php
require_once __DIR__ . '/../includes/config.php';
$pageTitle = 'About TiCi NatureLab — India\'s Premium Aquascaping Store';
include __DIR__ . '/../includes/header.php';
?>

<!-- Hero -->
<section style="background:linear-gradient(135deg,var(--green-dark),var(--green-mid));padding:60px 0;color:#fff;text-align:center;position:relative;overflow:hidden">
  <div style="position:absolute;inset:0;background:radial-gradient(circle at 30% 50%,rgba(255,255,255,.05),transparent)"></div>
  <div class="container" style="position:relative">
    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:2px;color:var(--green-light);margin-bottom:10px">Our Story</div>
    <h1 style="font-family:var(--font-display);font-size:clamp(1.8rem,4vw,3rem);color:#fff;margin-bottom:14px">About TiCi NatureLab</h1>
    <p style="color:rgba(255,255,255,.75);max-width:560px;margin:0 auto;font-size:1rem;line-height:1.7">India's premier destination for high-quality aquatic plants, tissue culture plants, and nature-inspired products for planted tanks, terrariums, and vertical gardens.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center;margin-bottom:60px">
      <div>
        <div class="section-label">Who We Are</div>
        <h2 style="font-family:var(--font-display);font-size:1.8rem;color:var(--green-dark);margin-bottom:16px">Passionate Plant Lovers, Just Like You</h2>
        <p style="color:var(--text-body);line-height:1.8;margin-bottom:14px">TiCi NatureLab was founded by a group of aquascaping enthusiasts who were frustrated by the lack of quality plant sources in India. We set out to build a store that we ourselves would love to buy from.</p>
        <p style="color:var(--text-body);line-height:1.8;margin-bottom:14px">Today, we serve thousands of planted tank hobbyists, terrarium builders, and nature lovers across India with the finest plants, fertilizers, and accessories — all carefully sourced, acclimatized, and packed with care.</p>
        <p style="color:var(--text-body);line-height:1.8">Every plant that leaves our facility is checked by our expert team. We guarantee live arrival on every order.</p>
      </div>
      <div style="background:var(--green-ghost);border-radius:var(--radius-lg);padding:40px;text-align:center;font-size:5rem">🌿</div>
    </div>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:20px;margin-bottom:60px">
      <?php foreach ([['500+','Plant Species'],['10,000+','Happy Customers'],['50+','Cities Served'],['100%','Live Arrival Rate']] as [$val,$label]): ?>
      <div style="text-align:center;padding:28px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg)">
        <div style="font-family:var(--font-display);font-size:2.2rem;font-weight:700;color:var(--green-dark);margin-bottom:6px"><?= $val ?></div>
        <div style="font-size:.83rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px"><?= $label ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Values -->
    <div class="section-header"><div class="section-label">Our Values</div><h2 class="section-title">What We Stand For</h2></div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:20px">
      <?php foreach ([
        ['🌱','Quality First','Every plant is personally inspected. If it doesn\'t meet our standards, it doesn\'t ship.'],
        ['📦','Careful Packaging','Live plants need special care in transit. Our packaging ensures they arrive fresh and healthy.'],
        ['💬','Expert Support','Our team of aquascaping enthusiasts provides personalized care advice for every purchase.'],
        ['🔬','Scientific Approach','We use tissue culture technology to provide pest-free, algae-free, lab-grown plants.'],
      ] as [$icon,$title,$desc]): ?>
      <div style="padding:24px;background:var(--green-ghost);border:1px solid var(--border);border-radius:var(--radius-lg)">
        <div style="font-size:2rem;margin-bottom:12px"><?= $icon ?></div>
        <h3 style="font-family:var(--font-display);font-size:1rem;color:var(--green-dark);margin-bottom:8px"><?= $title ?></h3>
        <p style="font-size:.83rem;color:var(--text-muted);line-height:1.65"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section style="background:linear-gradient(135deg,var(--green-dark),var(--green-mid));padding:50px 0;text-align:center">
  <div class="container">
    <h2 style="font-family:var(--font-display);font-size:1.8rem;color:#fff;margin-bottom:10px">Ready to Build Your Dream Tank?</h2>
    <p style="color:rgba(255,255,255,.75);margin-bottom:24px">Browse our collection of 500+ premium aquatic plants and accessories.</p>
    <a href="<?= SITE_URL ?>/shop/index.php" class="btn btn-amber btn-lg"><i class="fa fa-leaf"></i> Shop Now</a>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
