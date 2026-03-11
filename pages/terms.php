<?php
require_once __DIR__ . '/../includes/config.php';

// Route to the right policy
$page = basename($_SERVER['PHP_SELF'], '.php');

$policies = [
  'shipping-policy' => [
    'title' => '🚚 Shipping Policy',
    'content' => '
<h3>Delivery Timelines</h3>
<ul style="margin-left:20px;margin-bottom:16px;color:var(--text-body);line-height:2">
<li><strong>Standard Delivery:</strong> 5–7 business days</li>
<li><strong>Express Delivery:</strong> 2–3 business days</li>
<li>We ship Monday to Saturday</li>
</ul>

<h3>Shipping Charges</h3>
<ul style="margin-left:20px;margin-bottom:16px;color:var(--text-body);line-height:2">
<li><strong>Free Shipping:</strong> Orders above ₹999</li>
<li><strong>Standard Shipping:</strong> ₹120 for orders below ₹999</li>
<li><strong>Express Shipping:</strong> ₹180 (all orders)</li>
</ul>

<h3>Live Plant Packaging</h3>
<p style="color:var(--text-body);line-height:1.8;margin-bottom:12px">All live plants are packed in insulated boxes with moisture-retention material. In summer months, ice packs are added to keep plants cool during transit. Tissue culture plants are shipped in their original sealed cups.</p>

<h3>Delivery Areas</h3>
<p style="color:var(--text-body);line-height:1.8;margin-bottom:12px">We deliver pan-India to all 28 states and 8 union territories. Remote areas may experience 1–2 additional days of delivery time.</p>

<h3>Order Tracking</h3>
<p style="color:var(--text-body);line-height:1.8">Once shipped, you will receive a tracking link via email or WhatsApp. You can also track your order using our <a href="/tici-shop/pages/track-order.php" style="color:var(--green-mid)">Track Order</a> page.</p>
'
  ],
  'return-policy' => [
    'title' => '🔄 Return & Refund Policy',
    'content' => '
<h3>Live Arrival Guarantee</h3>
<p style="color:var(--text-body);line-height:1.8;margin-bottom:14px">We guarantee that all plants arrive alive and healthy. If your order arrives damaged or dead, we will replace or refund it at no additional cost.</p>

<h3>How to Report DOA (Dead on Arrival)</h3>
<ul style="margin-left:20px;margin-bottom:16px;color:var(--text-body);line-height:2">
<li>Take clear photos of the damaged plants immediately on arrival</li>
<li>Contact us within <strong>24 hours of delivery</strong></li>
<li>Send photos via WhatsApp: +91 98765 43210 or email: hello@ticinaturelab.com</li>
<li>Include your order number in the message</li>
</ul>

<h3>What We Cover</h3>
<ul style="margin-left:20px;margin-bottom:16px;color:var(--text-body);line-height:2">
<li>Plants that arrive dead or severely damaged</li>
<li>Wrong items delivered</li>
<li>Missing items from order</li>
</ul>

<h3>What We Don\'t Cover</h3>
<ul style="margin-left:20px;margin-bottom:16px;color:var(--text-body);line-height:2">
<li>Plant death due to improper care after delivery</li>
<li>Cosmetic damage that doesn\'t affect plant health</li>
<li>Claims made after 24 hours of delivery</li>
<li>Fertilizers and accessories (unless defective)</li>
</ul>

<h3>Refund Timeline</h3>
<p style="color:var(--text-body);line-height:1.8">Approved refunds are processed within 5–7 business days. The refund will be returned to the original payment method.</p>
'
  ],
  'privacy-policy' => [
    'title' => '🔒 Privacy Policy',
    'content' => '
<p style="color:var(--text-body);line-height:1.8;margin-bottom:14px">TiCi NatureLab is committed to protecting your privacy. This policy explains how we collect, use, and protect your information.</p>

<h3>Information We Collect</h3>
<ul style="margin-left:20px;margin-bottom:14px;color:var(--text-body);line-height:2">
<li>Name, email, phone number (for account and orders)</li>
<li>Delivery addresses</li>
<li>Order history and purchase data</li>
<li>Device and browsing data (cookies)</li>
</ul>

<h3>How We Use Your Information</h3>
<ul style="margin-left:20px;margin-bottom:14px;color:var(--text-body);line-height:2">
<li>Processing and delivering your orders</li>
<li>Sending order updates and tracking information</li>
<li>Customer support communication</li>
<li>Improving our website and services</li>
<li>Sending promotional offers (only if you opt-in)</li>
</ul>

<h3>Data Security</h3>
<p style="color:var(--text-body);line-height:1.8;margin-bottom:14px">We use industry-standard security measures including SSL encryption and password hashing. Your payment details are processed by secure payment gateways and are never stored on our servers.</p>

<h3>Third Parties</h3>
<p style="color:var(--text-body);line-height:1.8;margin-bottom:14px">We do not sell your personal data to third parties. We share only the minimum required information with shipping partners for order delivery.</p>

<h3>Contact</h3>
<p style="color:var(--text-body);line-height:1.8">For any privacy concerns, contact us at <a href="mailto:privacy@ticinaturelab.com" style="color:var(--green-mid)">privacy@ticinaturelab.com</a></p>
'
  ],
  'terms' => [
    'title' => '📋 Terms & Conditions',
    'content' => '
<p style="color:var(--text-body);line-height:1.8;margin-bottom:14px">By using TiCi NatureLab\'s website and placing orders, you agree to these terms and conditions.</p>

<h3>Use of Website</h3>
<ul style="margin-left:20px;margin-bottom:14px;color:var(--text-body);line-height:2">
<li>You must be 18 years or older to purchase</li>
<li>Account information must be accurate and complete</li>
<li>You are responsible for maintaining account security</li>
</ul>

<h3>Orders & Pricing</h3>
<ul style="margin-left:20px;margin-bottom:14px;color:var(--text-body);line-height:2">
<li>All prices are in Indian Rupees (₹) and inclusive of applicable taxes</li>
<li>We reserve the right to cancel orders due to stock unavailability</li>
<li>Price changes may occur without prior notice</li>
</ul>

<h3>Live Plants</h3>
<p style="color:var(--text-body);line-height:1.8;margin-bottom:14px">Due to the living nature of our products, TiCi NatureLab cannot guarantee specific growth outcomes after delivery. Proper care is the customer\'s responsibility.</p>

<h3>Limitation of Liability</h3>
<p style="color:var(--text-body);line-height:1.8;margin-bottom:14px">TiCi NatureLab\'s liability is limited to the value of the order placed. We are not responsible for indirect losses or damages arising from the use of our products.</p>

<h3>Governing Law</h3>
<p style="color:var(--text-body);line-height:1.8">These terms are governed by the laws of India. Any disputes will be resolved in the courts of jurisdiction applicable to our registered address.</p>
'
  ],
];

$policy = $policies[$page] ?? $policies['shipping-policy'];
$pageTitle = strip_tags($policy['title']) . ' — TiCi NatureLab';

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding:40px 20px 80px;max-width:860px">
  <nav class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a><span class="breadcrumb-sep">›</span><span><?= strip_tags($policy['title']) ?></span></nav>

  <h1 style="font-family:var(--font-display);font-size:2rem;color:var(--green-dark);margin:20px 0 28px"><?= $policy['title'] ?></h1>

  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:32px;line-height:1.75">
    <style>
      h3 { font-family:var(--font-display);font-size:1.05rem;color:var(--green-dark);margin:20px 0 10px;padding-bottom:6px;border-bottom:1px solid var(--border); }
      h3:first-child { margin-top:0; }
    </style>
    <?= $policy['content'] ?>
    <div style="margin-top:28px;padding-top:20px;border-top:1px solid var(--border);font-size:.78rem;color:var(--text-muted)">
      Last updated: <?= date('F Y') ?> | Questions? <a href="<?= SITE_URL ?>/pages/contact.php" style="color:var(--green-mid)">Contact us</a>
    </div>
  </div>

  <!-- Other policies quick links -->
  <div style="margin-top:24px;display:flex;gap:10px;flex-wrap:wrap">
    <a href="<?= SITE_URL ?>/pages/shipping-policy.php" class="btn btn-sm btn-secondary">Shipping Policy</a>
    <a href="<?= SITE_URL ?>/pages/return-policy.php" class="btn btn-sm btn-secondary">Return Policy</a>
    <a href="<?= SITE_URL ?>/pages/privacy-policy.php" class="btn btn-sm btn-secondary">Privacy Policy</a>
    <a href="<?= SITE_URL ?>/pages/terms.php" class="btn btn-sm btn-secondary">Terms &amp; Conditions</a>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
