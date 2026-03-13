<!-- ═══════════════════════════════════════════════════════
     SITE FOOTER
═══════════════════════════════════════════════════════ -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">

      <!-- Brand -->
      <div>
        <div class="site-logo footer-brand" style="margin-bottom:14px">
          <div class="logo-icon">🌿</div>
          <div class="logo-text">
            <span class="logo-name">TiCi</span>
            <span class="logo-tagline">NatureLab</span>
          </div>
        </div>
        <p class="footer-desc">India's premium aquascaping store — offering the finest tissue culture plants, aquatic plants, fertilizers, and terrarium essentials. Curated for plant lovers and aquascaping enthusiasts.</p>
        <div class="footer-social">
          <a href="#" class="social-btn"><i class="fab fa-instagram"></i></a>
          <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-btn"><i class="fab fa-youtube"></i></a>
          <a href="#" class="social-btn"><i class="fab fa-whatsapp"></i></a>
        </div>
      </div>

      <!-- Quick Links -->
      <div>
        <div class="footer-col-title">Shop</div>
        <div class="footer-links">
          <a href="<?= SITE_URL ?>/shop/index.php?category=aquatic-plants">🌿 Aquatic Plants</a>
          <a href="<?= SITE_URL ?>/shop/index.php?category=tissue-culture">🧫 Tissue Culture</a>
          <a href="<?= SITE_URL ?>/shop/index.php?category=fertilizers">💧 Fertilizers</a>
          <a href="<?= SITE_URL ?>/shop/index.php?category=terrarium">🌱 Terrarium Plants</a>
          <a href="<?= SITE_URL ?>/shop/index.php?category=paludarium">🦋 Paludarium</a>
          <a href="<?= SITE_URL ?>/shop/index.php?category=accessories">🔧 Accessories</a>
          <a href="<?= SITE_URL ?>/shop/index.php?sale=1">🔥 Sale</a>
        </div>
      </div>

      <!-- Company -->
      <div>
        <div class="footer-col-title">Company</div>
        <div class="footer-links">
          <a href="<?= SITE_URL ?>/pages/about.php">About TiCi</a>
          <a href="<?= SITE_URL ?>/pages/blog.php">Plant Care Blog</a>
          <a href="<?= SITE_URL ?>/pages/contact.php">Contact Us</a>
          <a href="<?= SITE_URL ?>/pages/track-order.php">Track Order</a>
          <a href="<?= SITE_URL ?>/pages/faq.php">FAQ</a>
          <a href="<?= SITE_URL ?>/account/login.php">My Account</a>
        </div>
      </div>

      <!-- Contact -->
      <div>
        <div class="footer-col-title">Get in Touch</div>
        <div class="footer-contact-item"><i class="fa fa-location-dot"></i><span>TiCi NatureLab, Aquascaping Store, India</span></div>
        <div class="footer-contact-item"><i class="fa fa-phone"></i><span>+91 98765 43210</span></div>
        <div class="footer-contact-item"><i class="fa fa-envelope"></i><span>hello@ticinaturelab.com</span></div>
        <div class="footer-contact-item"><i class="fa fa-clock"></i><span>Mon–Sat: 10am – 7pm IST</span></div>
        <div style="margin-top:16px">
          <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;color:rgba(255,255,255,.4);margin-bottom:8px">Policies</div>
          <div class="footer-links">
            <a href="<?= SITE_URL ?>/pages/shipping-policy.php">Shipping Policy</a>
            <a href="<?= SITE_URL ?>/pages/return-policy.php">Return Policy</a>
            <a href="<?= SITE_URL ?>/pages/privacy-policy.php">Privacy Policy</a>
            <a href="<?= SITE_URL ?>/pages/terms.php">Terms &amp; Conditions</a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <div class="container">
    <div class="footer-bottom">
      <div class="footer-bottom-text">© <?= date('Y') ?> TiCi NatureLab. All rights reserved. Made with 🌿 for plant lovers.</div>
      <div class="payment-icons">
        <div class="payment-icon">UPI</div>
        <div class="payment-icon">COD</div>
        <div class="payment-icon">NET</div>
        <div class="payment-icon">CARDS</div>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/slick.js"></script>
<script src="<?= SITE_URL ?>/assets/js/nav.js"></script>
<script src="<?= SITE_URL ?>/assets/js/common.js"></script>
<script src="<?= SITE_URL ?>/assets/js/shop.js"></script>
<script>
// Mobile nav
function closeMobileNav() {
  document.getElementById('mobileNav').classList.remove('open');
  document.body.style.overflow = '';
}
document.getElementById('mobileMenuBtn').addEventListener('click', function() {
  document.getElementById('mobileNav').classList.add('open');
  document.body.style.overflow = 'hidden';
});
document.getElementById('mobileNavOverlay').addEventListener('click', closeMobileNav);
</script>
</body>
</html>
