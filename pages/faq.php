<?php
require_once __DIR__ . '/../includes/config.php';
$pageTitle = 'FAQ — TiCi NatureLab';
include __DIR__ . '/../includes/header.php';

$faqs = [
  'Orders & Shipping' => [
    ['How long does delivery take?', 'Standard delivery takes 5–7 business days. Express delivery takes 2–3 business days. We ship Monday to Saturday across India.'],
    ['Do you offer free shipping?', 'Yes! Orders above ₹999 qualify for free standard shipping. Orders below ₹999 have a flat shipping charge of ₹120.'],
    ['How are live plants packaged?', 'All plants are heat-packed in insulated boxes with moisture-retaining materials to ensure safe delivery. Tissue culture plants are shipped in their sealed cups.'],
    ['Can I track my order?', 'Yes, use our Track Order page with your order number and phone number to get real-time status updates.'],
    ['Do you ship to all states in India?', 'Yes, we deliver pan-India to all states and union territories.'],
  ],
  'Products & Plants' => [
    ['What are tissue culture plants?', 'Tissue culture plants are lab-grown in sterile conditions, making them completely free of pests, algae, and pathogens. They\'re the cleanest plants you can add to your aquarium.'],
    ['Are your plants guaranteed to survive?', 'Yes! We offer a 100% live arrival guarantee. If your plants arrive damaged or dead, send us photos within 24 hours and we\'ll replace or refund them.'],
    ['What does the difficulty level mean?', 'Beginner: Easy to grow, tolerant of various conditions. Intermediate: Requires stable parameters and good lighting. Expert: Demanding, requires CO2, strong lighting, and precise water chemistry.'],
    ['Can I request specific plants not listed?', 'Yes! Contact us via WhatsApp or email and we\'ll do our best to source rare plants for you.'],
    ['How do I acclimatize new plants?', 'Float the bag for 15 minutes, then slowly introduce tank water. For TC plants, rinse the gel gently before planting. Allow 2–4 weeks for full acclimatization.'],
  ],
  'Payments & Returns' => [
    ['What payment methods do you accept?', 'We accept UPI (PhonePe, GPay, Paytm), Bank Transfer (NEFT/IMPS), Cash on Delivery (COD), and Credit/Debit cards.'],
    ['Can I cancel my order?', 'Orders can be cancelled within 2 hours of placement. After that, if the order has been dispatched, it cannot be cancelled.'],
    ['What is your return policy?', 'We accept returns for DOA (dead on arrival) plants within 24 hours of delivery. Send clear photos of the damaged plant to our WhatsApp within 24 hours.'],
    ['How long does a refund take?', 'Refunds are processed within 5–7 business days after we verify the return. UPI refunds are fastest, bank transfers may take longer.'],
  ],
];
?>

<section style="background:linear-gradient(135deg,var(--green-dark),var(--green-mid));padding:50px 0;text-align:center">
  <div class="container">
    <h1 style="font-family:var(--font-display);font-size:2.2rem;color:#fff;margin-bottom:8px">Frequently Asked Questions</h1>
    <p style="color:rgba(255,255,255,.75)">Everything you need to know about TiCi NatureLab</p>
  </div>
</section>

<div class="container" style="padding:48px 20px 80px;max-width:900px">
  <?php foreach ($faqs as $category => $qs): ?>
  <h2 style="font-family:var(--font-display);font-size:1.3rem;color:var(--green-dark);margin:32px 0 16px;padding-bottom:10px;border-bottom:2px solid var(--border)"><?= h($category) ?></h2>
  <div style="display:flex;flex-direction:column;gap:2px;margin-bottom:8px">
    <?php foreach ($qs as $i => [$q, $a]): ?>
    <div style="border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden">
      <button onclick="toggleFaq(this)" style="width:100%;text-align:left;padding:16px 20px;background:var(--bg-card);font-weight:600;font-size:.9rem;color:var(--text-primary);display:flex;justify-content:space-between;align-items:center;gap:12px;border:none;cursor:pointer">
        <?= h($q) ?>
        <i class="fa fa-chevron-down" style="color:var(--text-muted);flex-shrink:0;transition:transform .2s;font-size:.8rem"></i>
      </button>
      <div style="display:none;padding:0 20px 16px;background:var(--green-ghost);font-size:.875rem;color:var(--text-body);line-height:1.75"><?= h($a) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>

  <div style="text-align:center;margin-top:40px;background:var(--green-ghost);border-radius:var(--radius-lg);padding:30px">
    <div style="font-size:2rem;margin-bottom:10px">💬</div>
    <h3 style="font-family:var(--font-display);font-size:1.1rem;color:var(--green-dark);margin-bottom:8px">Still have questions?</h3>
    <p style="color:var(--text-muted);font-size:.875rem;margin-bottom:16px">Our plant experts are ready to help you!</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
      <a href="<?= SITE_URL ?>/pages/contact.php" class="btn btn-primary btn-sm"><i class="fa fa-envelope"></i> Email Us</a>
      <a href="https://wa.me/919876543210" target="_blank" class="btn btn-sm" style="background:#25d366;color:#fff;border-radius:var(--radius-full)"><i class="fab fa-whatsapp"></i> WhatsApp</a>
    </div>
  </div>
</div>

<script>
function toggleFaq(btn) {
  var answer = btn.nextElementSibling;
  var icon   = btn.querySelector('i');
  var isOpen = answer.style.display === 'block';
  answer.style.display = isOpen ? 'none' : 'block';
  icon.style.transform = isOpen ? '' : 'rotate(180deg)';
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
