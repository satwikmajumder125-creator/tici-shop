<?php
require_once __DIR__ . '/../includes/config.php';
$pageTitle = 'Plant Care Blog — TiCi NatureLab';
include __DIR__ . '/../includes/header.php';

$blogs = [
  ['🌿','Aquascaping Basics','Beginner\'s Complete Guide to Planted Aquariums', 'From choosing the right substrate to setting up CO2, lighting, and selecting beginner plants — everything you need for your first planted tank.','2025-03-01','8 min read'],
  ['🧫','Tissue Culture','Why Tissue Culture Plants Are the Future of Aquascaping', 'TC plants are pest-free, algae-free, and fully adapted. We explain the science, the benefits, and why you should switch to TC plants today.','2025-02-20','6 min read'],
  ['💧','Fertilizers','The Complete Guide to Planted Tank Fertilization', 'Macro nutrients, micro nutrients, EI dosing, PPS-Pro — we break down every method and help you choose the right fertilizer regime.','2025-02-10','10 min read'],
  ['🌱','Plant Care','Top 10 Easiest Aquatic Plants for Beginners', 'Java Moss, Anubias, Cryptocoryne — these plants thrive even without CO2 and are perfect for anyone starting out in aquascaping.','2025-01-28','5 min read'],
  ['🏡','Terrarium','How to Build a Stunning Terrarium in 7 Steps', 'Creating a closed terrarium is easier than you think! We guide you step by step from selecting the container to choosing the right plants.','2025-01-15','7 min read'],
  ['🦋','Paludarium','Paludarium Build: Water + Land + Air in One Tank', 'Paludariums combine aquatic, terrestrial, and arboreal zones. Here\'s how to design and build one that looks stunning and functions perfectly.','2025-01-05','9 min read'],
];
?>

<section style="background:linear-gradient(135deg,var(--green-dark),var(--green-mid));padding:50px 0;text-align:center">
  <div class="container">
    <h1 style="font-family:var(--font-display);font-size:2.2rem;color:#fff;margin-bottom:8px">🌿 Plant Care Blog</h1>
    <p style="color:rgba(255,255,255,.75)">Expert tips, guides, and inspiration from our aquascaping team</p>
  </div>
</section>

<div class="container" style="padding:48px 20px 80px">
  <div class="blog-grid">
    <?php foreach ($blogs as [$emoji,$cat,$title,$excerpt,$date,$readtime]): ?>
    <article class="blog-card">
      <div class="blog-img"><?= $emoji ?></div>
      <div class="blog-body">
        <div class="blog-cat"><?= h($cat) ?></div>
        <h2 class="blog-title"><?= h($title) ?></h2>
        <p class="blog-excerpt"><?= h($excerpt) ?></p>
        <div class="blog-meta">
          <span><i class="fa fa-calendar" style="font-size:.65rem"></i> <?= date('d M Y', strtotime($date)) ?></span>
          <span><?= h($readtime) ?></span>
        </div>
        <a href="#" style="display:inline-flex;align-items:center;gap:6px;font-size:.83rem;font-weight:600;color:var(--green-mid);margin-top:12px">Read Article <i class="fa fa-arrow-right" style="font-size:.75rem"></i></a>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
