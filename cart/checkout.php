<?php
require_once __DIR__ . '/../includes/config.php';
$pageTitle = 'Checkout — TiCi NatureLab';
$db = db();
$uid = $_SESSION['user_id'] ?? null;
$sid = getSessionId();

// Fetch cart
if ($uid) {
    $stmt = $db->prepare("SELECT ci.*, p.name, p.stock, COALESCE(pi.image_path, pi.image, '') AS thumb, pv.variant_name, COALESCE(pv.price, ci.price) AS unit_price FROM cart_items ci JOIN products p ON p.id=ci.product_id LEFT JOIN product_images pi ON pi.product_id=ci.product_id AND pi.is_primary=1 LEFT JOIN product_variants pv ON pv.id=ci.variant_id WHERE ci.user_id=?");
    $stmt->execute([$uid]);
} else {
    $stmt = $db->prepare("SELECT ci.*, p.name, p.stock, COALESCE(pi.image_path, pi.image, '') AS thumb, pv.variant_name, COALESCE(pv.price, ci.price) AS unit_price FROM cart_items ci JOIN products p ON p.id=ci.product_id LEFT JOIN product_images pi ON pi.product_id=ci.product_id AND pi.is_primary=1 LEFT JOIN product_variants pv ON pv.id=ci.variant_id WHERE ci.session_id=?");
    $stmt->execute([$sid]);
}
$cartItems = $stmt->fetchAll();
if (empty($cartItems)) { header('Location: ' . SITE_URL . '/cart/index.php'); exit; }

$subtotal = array_sum(array_map(fn($r) => $r['unit_price'] * $r['quantity'], $cartItems));
$discount = (float)($_SESSION['cart_discount'] ?? 0);
$couponCode = $_SESSION['coupon_code'] ?? '';
$shipping = $subtotal - $discount >= 999 ? 0 : 120;
$total    = max(0, $subtotal - $discount + $shipping);

// Saved addresses
$addresses = [];
if ($uid) {
    $as = $db->prepare("SELECT * FROM user_addresses WHERE user_id=? ORDER BY default_address DESC");
    $as->execute([$uid]);
    $addresses = $as->fetchAll();
}
$user = currentUser();

// ── Handle POST ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $addr1   = trim($_POST['address_line1'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $payment = $_POST['payment_method'] ?? 'cod';
    $notes   = trim($_POST['notes'] ?? '');

    $errors = [];
    if (!$name) $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
    if (!preg_match('/^\d{10}$/', preg_replace('/\D/','',$phone))) $errors[] = 'Valid 10-digit phone required.';
    if (!$addr1) $errors[] = 'Address is required.';
    if (!$city)  $errors[] = 'City is required.';
    if (!$state) $errors[] = 'State is required.';
    if (strlen($pincode) !== 6 || !is_numeric($pincode)) $errors[] = 'Valid 6-digit pincode required.';

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            // Generate order number
            $year   = date('Y');
            $last   = $db->query("SELECT MAX(id) FROM orders")->fetchColumn() ?: 0;
            $orderNum = 'TICI-' . $year . '-' . str_pad($last + 1, 6, '0', STR_PAD_LEFT);

            // Insert order
            $db->prepare("INSERT INTO orders (order_number, user_id, guest_name, guest_email, guest_phone, total_amount, discount_amount, shipping_amount, final_amount, coupon_code, payment_method, payment_status, order_status, notes)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,'unpaid','pending',?)")
               ->execute([$orderNum, $uid, $uid?null:$name, $uid?null:$email, $uid?null:$phone,
                         $subtotal, $discount, $shipping, $total, $couponCode ?: null, $payment, $notes]);
            $orderId = $db->lastInsertId();

            // Insert order items
            foreach ($cartItems as $item) {
                $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, variant_name, quantity, price, total)
                    VALUES (?,?,?,?,?,?,?)")
                   ->execute([$orderId, $item['product_id'], $item['name'], $item['variant_name'], $item['quantity'], $item['unit_price'], $item['unit_price'] * $item['quantity']]);
                // Reduce stock
                $db->prepare("UPDATE products SET stock = stock - ? WHERE id=? AND stock >= ?")->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
            }

            // Shipping address
            $db->prepare("INSERT INTO order_addresses (order_id, name, phone, address_line1, city, state, pincode)
                VALUES (?,?,?,?,?,?,?)")
               ->execute([$orderId, $name, $phone, $addr1 . (isset($_POST['address_line2']) ? ', '.$_POST['address_line2'] : ''), $city, $state, $pincode]);

            // Status history
            $db->prepare("INSERT INTO order_status_history (order_id, status, note) VALUES (?,'pending','Order placed')")->execute([$orderId]);

            // Coupon usage
            if (!empty($_SESSION['coupon_id'])) {
                $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id=?")->execute([$_SESSION['coupon_id']]);
                if ($uid) {
                    $db->prepare("INSERT IGNORE INTO coupon_usage (coupon_id, user_id, order_id) VALUES (?,?,?)")->execute([$_SESSION['coupon_id'], $uid, $orderId]);
                }
            }

            // Clear cart
            if ($uid) {
                $db->prepare("DELETE FROM cart_items WHERE user_id=?")->execute([$uid]);
            } else {
                $db->prepare("DELETE FROM cart_items WHERE session_id=?")->execute([$sid]);
            }

            // Clear coupon session
            unset($_SESSION['cart_discount'], $_SESSION['coupon_code'], $_SESSION['coupon_id']);

            // Save address if logged in
            if ($uid && !empty($_POST['save_address'])) {
                $db->prepare("INSERT INTO user_addresses (user_id, name, phone, address_line1, city, state, pincode) VALUES (?,?,?,?,?,?,?)")
                   ->execute([$uid, $name, $phone, $addr1, $city, $state, $pincode]);
            }

            $db->commit();
            header('Location: ' . SITE_URL . '/cart/order-success.php?order=' . $orderNum);
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Order failed. Please try again.';
            error_log($e->getMessage());
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding:24px 20px 60px">
  <nav class="breadcrumb">
    <a href="<?= SITE_URL ?>/index.php">Home</a>
    <span class="breadcrumb-sep">›</span>
    <a href="<?= SITE_URL ?>/cart/index.php">Cart</a>
    <span class="breadcrumb-sep">›</span>
    <span>Checkout</span>
  </nav>

  <h1 style="font-family:var(--font-display);font-size:2rem;color:var(--green-dark);margin-bottom:24px">Checkout</h1>

  <!-- Steps -->
  <div class="checkout-steps" style="max-width:600px;margin-bottom:36px">
    <div class="checkout-step done"><span class="step-num"><i class="fa fa-check" style="font-size:.65rem"></i></span><span>Cart</span></div>
    <div class="checkout-step active"><span class="step-num">2</span><span>Details &amp; Shipping</span></div>
    <div class="checkout-step"><span class="step-num">3</span><span>Payment</span></div>
    <div class="checkout-step"><span class="step-num">4</span><span>Confirm</span></div>
  </div>

  <?php if (!empty($errors)): ?>
  <div class="flash-msg flash-error" style="margin-bottom:20px">
    <i class="fa fa-circle-xmark"></i>
    <div>
      <?php foreach ($errors as $e): ?><div><?= h($e) ?></div><?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <form method="POST" id="checkoutForm">
    <div style="display:grid;grid-template-columns:1fr 360px;gap:32px;align-items:start">

      <!-- Left: Form -->
      <div>
        <!-- Saved Addresses -->
        <?php if (!empty($addresses)): ?>
        <div class="card" style="margin-bottom:20px">
          <h3 style="font-family:var(--font-display);font-size:1rem;color:var(--green-dark);margin-bottom:14px">Saved Addresses</h3>
          <div style="display:flex;flex-direction:column;gap:10px">
            <?php foreach ($addresses as $addr): ?>
            <label style="display:flex;align-items:flex-start;gap:10px;padding:12px;border:1.5px solid var(--border-strong);border-radius:var(--radius-sm);cursor:pointer;transition:border-color .2s" onclick="fillAddress(this)">
              <input type="radio" name="saved_address" value="<?= $addr['id'] ?>" style="margin-top:3px;accent-color:var(--green-mid)"
                     data-name="<?= h($addr['name']) ?>" data-phone="<?= h($addr['phone']) ?>"
                     data-addr1="<?= h($addr['address_line1']) ?>" data-addr2="<?= h($addr['address_line2'] ?? '') ?>"
                     data-city="<?= h($addr['city']) ?>" data-state="<?= h($addr['state']) ?>" data-pin="<?= h($addr['pincode']) ?>">
              <div style="font-size:.875rem;line-height:1.6">
                <strong><?= h($addr['name']) ?></strong> · <?= h($addr['phone']) ?><br>
                <?= h($addr['address_line1']) ?>, <?= h($addr['city']) ?>, <?= h($addr['state']) ?> — <?= h($addr['pincode']) ?>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
          <div style="margin-top:12px"><a href="#" onclick="clearAddress(event)" style="font-size:.8rem;color:var(--green-mid)">+ Use a different address</a></div>
        </div>
        <?php endif; ?>

        <!-- Contact Info -->
        <div class="card" style="margin-bottom:20px">
          <h3 style="font-family:var(--font-display);font-size:1rem;color:var(--green-dark);margin-bottom:16px">Contact Information</h3>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Full Name *</label>
              <input type="text" name="name" class="form-control" required value="<?= h($_POST['name'] ?? $user['name'] ?? '') ?>" placeholder="Your full name">
            </div>
            <div class="form-group">
              <label class="form-label">Phone *</label>
              <input type="tel" name="phone" class="form-control" required value="<?= h($_POST['phone'] ?? $user['phone'] ?? '') ?>" placeholder="10-digit mobile number">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-control" required value="<?= h($_POST['email'] ?? $user['email'] ?? '') ?>" placeholder="your@email.com">
          </div>
        </div>

        <!-- Shipping Address -->
        <div class="card" style="margin-bottom:20px">
          <h3 style="font-family:var(--font-display);font-size:1rem;color:var(--green-dark);margin-bottom:16px">Shipping Address</h3>
          <div class="form-group">
            <label class="form-label">Address Line 1 *</label>
            <input type="text" name="address_line1" id="addr1" class="form-control" required value="<?= h($_POST['address_line1'] ?? '') ?>" placeholder="House no., Street name">
          </div>
          <div class="form-group">
            <label class="form-label">Address Line 2</label>
            <input type="text" name="address_line2" id="addr2" class="form-control" value="<?= h($_POST['address_line2'] ?? '') ?>" placeholder="Landmark, Area (optional)">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">City *</label>
              <input type="text" name="city" id="city" class="form-control" required value="<?= h($_POST['city'] ?? '') ?>" placeholder="City">
            </div>
            <div class="form-group">
              <label class="form-label">State *</label>
              <select name="state" id="state" class="form-control" required>
                <option value="">Select State</option>
                <?php foreach (['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Delhi','Jammu & Kashmir','Ladakh'] as $st): ?>
                <option value="<?= h($st) ?>" <?= ($_POST['state'] ?? '') === $st ? 'selected' : '' ?>><?= h($st) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Pincode *</label>
              <input type="text" name="pincode" id="pincode" class="form-control" maxlength="6" required value="<?= h($_POST['pincode'] ?? '') ?>" placeholder="6-digit pincode">
            </div>
            <div class="form-group">
              <label class="form-label">Country</label>
              <input type="text" class="form-control" value="India" readonly style="background:var(--green-ghost);color:var(--text-muted)">
            </div>
          </div>
          <?php if ($uid): ?>
          <label style="display:flex;align-items:center;gap:8px;font-size:.83rem;color:var(--text-body);cursor:pointer">
            <input type="checkbox" name="save_address" value="1" style="accent-color:var(--green-mid)"> Save this address for future orders
          </label>
          <?php endif; ?>
        </div>

        <!-- Payment Method -->
        <div class="card" style="margin-bottom:20px">
          <h3 style="font-family:var(--font-display);font-size:1rem;color:var(--green-dark);margin-bottom:16px">Payment Method</h3>
          <div style="display:flex;flex-direction:column;gap:10px">
            <?php foreach ([
              ['cod','💵 Cash on Delivery','Pay when your order arrives at your door'],
              ['upi','📱 UPI / QR Code','Pay instantly via PhonePe, Google Pay, Paytm'],
              ['bank','🏦 Bank Transfer','NEFT / RTGS / IMPS to our bank account'],
              ['card','💳 Credit / Debit Card','Visa, Mastercard, RuPay accepted'],
            ] as [$val,$label,$desc]): ?>
            <label style="display:flex;align-items:flex-start;gap:12px;padding:14px;border:1.5px solid var(--border-strong);border-radius:var(--radius-sm);cursor:pointer;transition:border-color .2s" id="pm-<?= $val ?>">
              <input type="radio" name="payment_method" value="<?= $val ?>" <?= ($_POST['payment_method'] ?? 'cod') === $val ? 'checked' : '' ?> style="accent-color:var(--green-mid);margin-top:2px" onchange="highlightPayment('<?= $val ?>')">
              <div>
                <div style="font-weight:600;font-size:.9rem"><?= $label ?></div>
                <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px"><?= $desc ?></div>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Notes -->
        <div class="card">
          <h3 style="font-family:var(--font-display);font-size:1rem;color:var(--green-dark);margin-bottom:12px">Order Notes (Optional)</h3>
          <textarea name="notes" class="form-control" rows="3" placeholder="Any special instructions for your order or delivery…"><?= h($_POST['notes'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- Right: Order Summary -->
      <div style="position:sticky;top:80px">
        <div class="order-summary">
          <h3 style="font-family:var(--font-display);font-size:1.1rem;color:var(--green-dark);margin-bottom:16px">Order Summary</h3>

          <!-- Items -->
          <div style="margin-bottom:16px;max-height:260px;overflow-y:auto">
            <?php foreach ($cartItems as $item): ?>
            <div style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--border)">
              <div style="width:48px;height:48px;border-radius:var(--radius-sm);background:var(--green-ghost);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;overflow:hidden">
                <?php if ($item['thumb']): ?>
                  <img src="<?= h($item['thumb']) ?>" style="width:100%;height:100%;object-fit:cover">
                <?php else: ?> 🌿
                <?php endif; ?>
              </div>
              <div style="flex:1;min-width:0">
                <div style="font-size:.83rem;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($item['name']) ?></div>
                <?php if ($item['variant_name']): ?><div style="font-size:.72rem;color:var(--text-muted)"><?= h($item['variant_name']) ?></div><?php endif; ?>
                <div style="font-size:.78rem;color:var(--text-muted)">Qty: <?= $item['quantity'] ?></div>
              </div>
              <div style="font-weight:600;font-size:.875rem;white-space:nowrap"><?= price($item['unit_price'] * $item['quantity']) ?></div>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="summary-row"><span>Subtotal</span><span><?= price($subtotal) ?></span></div>
          <?php if ($discount > 0): ?>
          <div class="summary-row" style="color:var(--green-mid)"><span>Coupon (<?= h($couponCode) ?>)</span><span>−<?= price($discount) ?></span></div>
          <?php endif; ?>
          <div class="summary-row"><span>Shipping</span><span style="color:var(--green-mid)"><?= $shipping === 0 ? '<span style="color:var(--green-mid);font-weight:600">FREE</span>' : price($shipping) ?></span></div>
          <div class="summary-row summary-total"><span>Total</span><span><?= price($total) ?></span></div>

          <button type="submit" name="place_order" class="btn btn-primary btn-full btn-lg" style="margin-top:20px">
            <i class="fa fa-lock"></i> Place Order
          </button>
          <p style="text-align:center;font-size:.72rem;color:var(--text-muted);margin-top:10px">
            By placing order, you agree to our <a href="<?= SITE_URL ?>/pages/terms.php" style="color:var(--green-mid)">Terms</a> &amp; <a href="<?= SITE_URL ?>/pages/privacy-policy.php" style="color:var(--green-mid)">Privacy Policy</a>
          </p>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
function fillAddress(label) {
  var radio = label.querySelector('input[type=radio]');
  if (!radio) return;
  document.getElementById('addr1').value    = radio.dataset.addr1 || '';
  document.getElementById('addr2').value    = radio.dataset.addr2 || '';
  document.getElementById('city').value     = radio.dataset.city || '';
  document.getElementById('pincode').value  = radio.dataset.pin || '';
  var state = document.getElementById('state');
  for (var i = 0; i < state.options.length; i++) {
    if (state.options[i].value === radio.dataset.state) { state.selectedIndex = i; break; }
  }
}
function clearAddress(e) {
  e.preventDefault();
  ['addr1','addr2','city','pincode'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('state').selectedIndex = 0;
  document.querySelectorAll('input[name=saved_address]').forEach(r => r.checked = false);
}
function highlightPayment(val) {
  document.querySelectorAll('[id^=pm-]').forEach(el => el.style.borderColor = 'var(--border-strong)');
  var el = document.getElementById('pm-' + val);
  if (el) el.style.borderColor = 'var(--green-bright)';
}
highlightPayment('<?= h($_POST['payment_method'] ?? 'cod') ?>');
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
