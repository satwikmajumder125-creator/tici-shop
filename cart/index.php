<?php
require_once __DIR__ . '/../includes/config.php';
$pageTitle = 'Your Cart — TiCi NatureLab';
$db = db();
$uid = $_SESSION['user_id'] ?? null;
$sid = getSessionId();

// Fetch cart items
if ($uid) {
    $stmt = $db->prepare("
        SELECT ci.*, p.name, p.slug, p.stock, COALESCE(pi.image_path, pi.image, '') AS thumb,
               pv.variant_name, COALESCE(pv.price, ci.price) AS unit_price
        FROM cart_items ci
        JOIN products p ON p.id = ci.product_id
        LEFT JOIN product_images pi ON pi.product_id = ci.product_id AND pi.is_primary = 1
        LEFT JOIN product_variants pv ON pv.id = ci.variant_id
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$uid]);
} else {
    $stmt = $db->prepare("
        SELECT ci.*, p.name, p.slug, p.stock, COALESCE(pi.image_path, pi.image, '') AS thumb,
               pv.variant_name, COALESCE(pv.price, ci.price) AS unit_price
        FROM cart_items ci
        JOIN products p ON p.id = ci.product_id
        LEFT JOIN product_images pi ON pi.product_id = ci.product_id AND pi.is_primary = 1
        LEFT JOIN product_variants pv ON pv.id = ci.variant_id
        WHERE ci.session_id = ?
    ");
    $stmt->execute([$sid]);
}
$cartItems = $stmt->fetchAll();

// Totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['unit_price'] * $item['quantity'];
}
$discount  = (float)($_SESSION['cart_discount'] ?? 0);
$couponCode= $_SESSION['coupon_code'] ?? '';
$shipping  = $subtotal > 0 ? ($subtotal - $discount >= 999 ? 0 : 120) : 0;
$total     = max(0, $subtotal - $discount + $shipping);

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding:24px 20px 60px">
  <nav class="breadcrumb">
    <a href="<?= SITE_URL ?>/index.php">Home</a>
    <span class="breadcrumb-sep">›</span>
    <span>Cart</span>
  </nav>

  <h1 style="font-family:var(--font-display);font-size:2rem;color:var(--green-dark);margin-bottom:28px">
    🛒 Your Cart <?php if (!empty($cartItems)): ?><span style="font-size:1rem;font-weight:400;color:var(--text-muted)">(<?= count($cartItems) ?> item<?= count($cartItems)!==1?'s':'' ?>)</span><?php endif; ?>
  </h1>

  <?php if (empty($cartItems)): ?>
  <div class="empty-state" style="padding:80px 20px">
    <div class="empty-icon">🛒</div>
    <h3>Your cart is empty</h3>
    <p>Looks like you haven't added anything yet. Start browsing our plants!</p>
    <a href="<?= SITE_URL ?>/shop/index.php" class="btn btn-primary" style="margin-top:20px">
      <i class="fa fa-leaf"></i> Browse Plants
    </a>
  </div>

  <?php else: ?>
  <div style="display:grid;grid-template-columns:1fr 360px;gap:32px;align-items:start">

    <!-- Cart Items -->
    <div>
      <!-- Checkout steps preview -->
      <div class="checkout-steps" style="margin-bottom:24px">
        <div class="checkout-step active"><span class="step-num">1</span><span>Cart</span></div>
        <div class="checkout-step"><span class="step-num">2</span><span>Details</span></div>
        <div class="checkout-step"><span class="step-num">3</span><span>Shipping</span></div>
        <div class="checkout-step"><span class="step-num">4</span><span>Payment</span></div>
      </div>

      <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
        <!-- Desktop table -->
        <div style="overflow-x:auto">
          <table class="cart-table">
            <thead>
              <tr>
                <th style="width:80px">Product</th>
                <th></th>
                <th>Price</th>
                <th>Qty</th>
                <th>Total</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cartItems as $item):
                $lineTotal = $item['unit_price'] * $item['quantity'];
              ?>
              <tr id="cart-row-<?= $item['id'] ?>">
                <td>
                  <a href="<?= SITE_URL ?>/shop/product.php?slug=<?= h($item['slug']) ?>">
                    <div class="cart-product-img">
                      <?php if ($item['thumb']): ?>
                        <img src="<?= h($item['thumb']) ?>" alt="" style="width:70px;height:70px;object-fit:cover;border-radius:var(--radius-sm)">
                      <?php else: ?>
                        🌿
                      <?php endif; ?>
                    </div>
                  </a>
                </td>
                <td>
                  <a href="<?= SITE_URL ?>/shop/product.php?slug=<?= h($item['slug']) ?>" style="font-weight:600;color:var(--text-primary);font-size:.9rem"><?= h($item['name']) ?></a>
                  <?php if ($item['variant_name']): ?>
                    <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px"><?= h($item['variant_name']) ?></div>
                  <?php endif; ?>
                  <?php if ($item['stock'] <= 5 && $item['stock'] > 0): ?>
                    <div style="font-size:.72rem;color:var(--amber);margin-top:3px"><i class="fa fa-triangle-exclamation"></i> Only <?= $item['stock'] ?> left</div>
                  <?php endif; ?>
                </td>
                <td style="font-weight:600;white-space:nowrap"><?= price($item['unit_price']) ?></td>
                <td>
                  <div class="qty-control">
                    <button type="button" class="qty-btn qty-dec" onclick="changeQty(this, -1, <?= $item['id'] ?>)">−</button>
                    <input type="number" class="qty-input cart-qty-input"
                           value="<?= $item['quantity'] ?>"
                           min="1" max="<?= $item['stock'] ?>"
                           data-item-id="<?= $item['id'] ?>"
                           style="width:46px;height:36px;border:none;border-left:1.5px solid var(--border-strong);border-right:1.5px solid var(--border-strong);text-align:center;font-weight:600;font-size:.875rem">
                    <button type="button" class="qty-btn qty-inc" onclick="changeQty(this, 1, <?= $item['id'] ?>)">+</button>
                  </div>
                </td>
                <td style="font-family:var(--font-display);font-weight:600;color:var(--green-dark);white-space:nowrap" id="line-<?= $item['id'] ?>"><?= price($lineTotal) ?></td>
                <td>
                  <button class="btn-remove-cart" data-item-id="<?= $item['id'] ?>"
                          style="color:var(--terracotta);font-size:1rem;background:none;border:none;cursor:pointer;padding:4px;border-radius:4px;transition:background .2s"
                          onmouseover="this.style.background='#fde8e8'" onmouseout="this.style.background='none'"
                          title="Remove">
                    <i class="fa fa-trash"></i>
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Continue shopping -->
      <div style="margin-top:16px;display:flex;gap:12px;flex-wrap:wrap">
        <a href="<?= SITE_URL ?>/shop/index.php" class="btn btn-secondary btn-sm">
          <i class="fa fa-arrow-left"></i> Continue Shopping
        </a>
        <button onclick="location.reload()" class="btn btn-secondary btn-sm">
          <i class="fa fa-rotate-right"></i> Refresh Cart
        </button>
      </div>
    </div>

    <!-- Order Summary -->
    <div>
      <div class="order-summary">
        <h3 style="font-family:var(--font-display);font-size:1.1rem;color:var(--green-dark);margin-bottom:16px">Order Summary</h3>

        <div class="summary-row">
          <span>Subtotal</span>
          <span id="cart-subtotal"><?= price($subtotal) ?></span>
        </div>
        <div class="summary-row">
          <span>Shipping</span>
          <span id="cart-shipping" style="color:var(--green-mid)"><?= $shipping === 0 ? '<span style="color:var(--green-mid);font-weight:600">FREE</span>' : price($shipping) ?></span>
        </div>
        <?php if ($discount > 0): ?>
        <div class="summary-row" style="color:var(--green-mid)">
          <span>Coupon discount</span>
          <span id="cart-discount">−<?= price($discount) ?></span>
        </div>
        <?php else: ?>
        <div class="summary-row" style="display:none" id="discount-row">
          <span>Coupon discount</span>
          <span id="cart-discount">—</span>
        </div>
        <?php endif; ?>

        <?php if ($subtotal < 999 && $subtotal > 0): ?>
        <div class="free-ship-nudge" style="background:var(--amber-light);border:1px solid #f0d080;border-radius:var(--radius-sm);padding:10px 12px;font-size:.78rem;color:#7a5a00;margin:10px 0">
          <i class="fa fa-truck"></i> Add <strong><?= price(999 - $subtotal) ?></strong> more for free shipping!
        </div>
        <?php endif; ?>

        <div class="summary-row summary-total">
          <span>Total</span>
          <span id="cart-total"><?= price($total) ?></span>
        </div>

        <!-- Coupon -->
        <div style="margin-top:16px" id="couponSection">

          <?php if ($couponCode): ?>
          <!-- ── Applied state ── -->
          <div id="couponApplied" class="coupon-applied-box">
            <div class="cab-left">
              <i class="fa fa-tag"></i>
              <div>
                <div class="cab-code"><?= h($couponCode) ?></div>
                <div class="cab-save">Saving <?= price($discount) ?> on this order</div>
              </div>
            </div>
            <button type="button" id="removeCouponBtn" class="cab-remove" title="Remove coupon">
              <i class="fa fa-xmark"></i> Remove
            </button>
          </div>
          <div id="couponInput" style="display:none">
          <?php else: ?>
          <!-- ── Input state ── -->
          <div id="couponApplied" class="coupon-applied-box" style="display:none"></div>
          <div id="couponInput">
          <?php endif; ?>
            <div style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:8px">Have a coupon?</div>
            <div class="coupon-row">
              <input type="text" id="couponCode" class="coupon-input" placeholder="Enter coupon code" maxlength="30">
            </div>
            <div style="display:flex;gap:8px;margin-top:8px">
              <button type="button" id="applyCouponBtn" class="btn btn-sm btn-primary" style="flex:1">
                <i class="fa fa-tag"></i> Apply Coupon
              </button>
            </div>
            <div id="couponMsg" style="font-size:.78rem;margin-top:8px"></div>
          </div>
        </div>

        <a href="<?= SITE_URL ?>/cart/checkout.php" class="btn btn-primary btn-full btn-lg" style="margin-top:20px">
          Proceed to Checkout <i class="fa fa-arrow-right"></i>
        </a>

        <!-- Trust badges -->
        <div style="display:flex;gap:16px;justify-content:center;margin-top:16px;flex-wrap:wrap">
          <?php foreach (['🔒 Secure Checkout','🌿 Live Guarantee','🚚 Fast Delivery'] as $t): ?>
          <span style="font-size:.7rem;color:var(--text-muted)"><?= $t ?></span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
function changeQty(btn, delta, itemId) {
  var $input = $(btn).siblings('.qty-input');
  var newVal = Math.max(1, parseInt($input.val()) + delta);
  $input.val(newVal).trigger('change');
}

// ── Helpers ───────────────────────────────────────────────────
function fmt(n) { return '₹' + parseFloat(n).toFixed(2); }

function updateSummary(data) {
  document.getElementById('cart-subtotal').textContent = fmt(data.subtotal);
  document.getElementById('cart-shipping').innerHTML   = data.shipping === 0
    ? '<span style="color:var(--green-mid);font-weight:600">FREE</span>'
    : fmt(data.shipping);
  document.getElementById('cart-total').textContent    = fmt(data.total);

  var discRow = document.getElementById('discount-row');
  var discVal = document.getElementById('cart-discount');
  if (data.discount > 0) {
    if (discRow) { discRow.style.display = ''; discRow.style.color = 'var(--green-mid)'; }
    if (discVal) discVal.textContent = '−' + fmt(data.discount);
  } else {
    if (discRow) discRow.style.display = 'none';
    if (discVal) discVal.textContent   = '—';
  }

  var nudge = document.querySelector('.free-ship-nudge');
  if (nudge) nudge.style.display = data.shipping === 0 ? 'none' : '';
}

// ── Apply coupon ──────────────────────────────────────────────
var applyBtn = document.getElementById('applyCouponBtn');
if (applyBtn) {
  applyBtn.addEventListener('click', function() {
    var code = document.getElementById('couponCode').value.trim().toUpperCase();
    var msg  = document.getElementById('couponMsg');
    if (!code) { msg.innerHTML = '<span style="color:#c0392b">Please enter a coupon code.</span>'; return; }

    applyBtn.disabled = true;
    applyBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Applying…';

    fetch('http://localhost/tici-shop/cart/coupon.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: 'code=' + encodeURIComponent(code)
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
      if (data.success) {
        // Switch to applied state
        document.getElementById('couponInput').style.display   = 'none';
        var box = document.getElementById('couponApplied');
        box.style.display = '';
        box.innerHTML =
          '<div class="cab-left">' +
            '<i class="fa fa-tag"></i>' +
            '<div>' +
              '<div class="cab-code">' + code + '</div>' +
              '<div class="cab-save">Saving ' + fmt(data.discount) + ' on this order</div>' +
            '</div>' +
          '</div>' +
          '<button type="button" id="removeCouponBtn" class="cab-remove" title="Remove coupon">' +
            '<i class="fa fa-xmark"></i> Remove' +
          '</button>';
        bindRemove();
        updateSummary(data);
      } else {
        msg.innerHTML = '<span style="color:#c0392b"><i class="fa fa-circle-xmark"></i> ' + data.message + '</span>';
        applyBtn.disabled = false;
        applyBtn.innerHTML = '<i class="fa fa-tag"></i> Apply Coupon';
      }
    })
    .catch(function() {
      msg.innerHTML = '<span style="color:#c0392b">Could not apply coupon. Please try again.</span>';
      applyBtn.disabled = false;
      applyBtn.innerHTML = '<i class="fa fa-tag"></i> Apply Coupon';
    });
  });

  document.getElementById('couponCode').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') applyBtn.click();
  });
}

// ── Remove coupon ─────────────────────────────────────────────
function bindRemove() {
  var removeBtn = document.getElementById('removeCouponBtn');
  if (!removeBtn) return;
  removeBtn.addEventListener('click', function() {
    removeBtn.disabled = true;
    removeBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

    fetch('http://localhost/tici-shop/cart/coupon.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: 'action=remove'
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
      // Switch back to input state
      document.getElementById('couponApplied').style.display = 'none';
      var inp = document.getElementById('couponInput');
      inp.style.display = '';
      document.getElementById('couponCode').value = '';
      document.getElementById('couponMsg').innerHTML =
        '<span style="color:var(--text-muted)"><i class="fa fa-info-circle"></i> Coupon removed.</span>';
      updateSummary(data);
    })
    .catch(function() {
      removeBtn.disabled = false;
      removeBtn.innerHTML = '<i class="fa fa-xmark"></i> Remove';
    });
  });
}
bindRemove(); // bind on page load if already applied
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
