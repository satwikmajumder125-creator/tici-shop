<?php
require_once __DIR__ . '/../includes/config.php';
$pageTitle = 'Track Order — TiCi NatureLab';
$db = db();
$order = null; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_GET['order']) && $_GET['order'])) {
    $orderNum = strtoupper(trim($_POST['order_number'] ?? $_GET['order'] ?? ''));
    $phone    = preg_replace('/\D/', '', $_POST['phone'] ?? '');

    if ($orderNum) {
        $stmt = $db->prepare("SELECT o.*, oa.name AS ship_name, oa.phone AS ship_phone, oa.address_line1, oa.city, oa.state, oa.pincode FROM orders o LEFT JOIN order_addresses oa ON oa.order_id=o.id WHERE o.order_number=?");
        $stmt->execute([$orderNum]);
        $order = $stmt->fetch();

        if (!$order) {
            $error = 'Order not found. Please check your order number.';
        } elseif ($phone && $order['ship_phone'] && preg_replace('/\D/','',$order['ship_phone']) !== $phone && preg_replace('/\D/','',$order['guest_phone'] ?? '') !== $phone) {
            $error = 'Phone number does not match this order.';
            $order = null;
        }
    }
}

if ($order) {
    $items = $db->prepare("SELECT * FROM order_items WHERE order_id=?"); $items->execute([$order['id']]); $items=$items->fetchAll();
    $history = $db->prepare("SELECT * FROM order_status_history WHERE order_id=? ORDER BY created_at ASC"); $history->execute([$order['id']]); $history=$history->fetchAll();
}

$statuses = ['pending','confirmed','packed','shipped','out_for_delivery','delivered'];
$labels   = ['Order Placed','Confirmed','Packed','Shipped','Out for Delivery','Delivered'];
$icons    = ['fa-bag-shopping','fa-circle-check','fa-box','fa-truck','fa-map-location-dot','fa-house-circle-check'];

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding:40px 20px 80px;max-width:900px">
  <div style="text-align:center;margin-bottom:36px">
    <h1 style="font-family:var(--font-display);font-size:2.2rem;color:var(--green-dark);margin-bottom:8px">📦 Track Your Order</h1>
    <p style="color:var(--text-muted)">Enter your order number to get real-time updates on your delivery</p>
  </div>

  <!-- Search form -->
  <div class="card" style="max-width:500px;margin:0 auto 32px">
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Order Number</label>
        <input type="text" name="order_number" class="form-control" placeholder="TICI-2026-000001" required value="<?= h($_POST['order_number'] ?? $_GET['order'] ?? '') ?>" style="text-transform:uppercase;letter-spacing:1px">
      </div>
      <div class="form-group">
        <label class="form-label">Phone Number (Optional)</label>
        <input type="tel" name="phone" class="form-control" placeholder="Registered phone number" value="<?= h($_POST['phone'] ?? '') ?>">
      </div>
      <button type="submit" class="btn btn-primary btn-full"><i class="fa fa-magnifying-glass"></i> Track Order</button>
    </form>
  </div>

  <?php if ($error): ?>
  <div class="flash-msg flash-error" style="max-width:500px;margin:0 auto"><i class="fa fa-circle-xmark"></i> <?= h($error) ?></div>
  <?php endif; ?>

  <?php if ($order):
    $current = array_search($order['order_status'], $statuses);
    $col = ['pending'=>'#f0a500','confirmed'=>'#3b9dd6','packed'=>'#8b6ebc','shipped'=>'#2d9b6f','out_for_delivery'=>'#e09a2a','delivered'=>'#1a5c3a','cancelled'=>'#c0392b'][$order['order_status']] ?? '#666';
  ?>
  <!-- Order found -->
  <div style="background:var(--green-ghost);border:2px solid var(--border-strong);border-radius:var(--radius-lg);padding:20px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
    <div>
      <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Order Number</div>
      <div style="font-family:var(--font-display);font-size:1.3rem;font-weight:700;color:var(--green-dark)"><?= h($order['order_number']) ?></div>
    </div>
    <div>
      <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Placed On</div>
      <div style="font-weight:600"><?= date('d M Y', strtotime($order['created_at'])) ?></div>
    </div>
    <div>
      <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Total</div>
      <div style="font-weight:700;color:var(--green-dark)"><?= price($order['final_amount']) ?></div>
    </div>
    <span style="background:<?= $col ?>22;color:<?= $col ?>;font-size:.83rem;font-weight:700;text-transform:capitalize;padding:6px 18px;border-radius:var(--radius-full)">
      <?= str_replace('_',' ',$order['order_status']) ?>
    </span>
  </div>

  <!-- Progress Steps -->
  <?php if ($order['order_status'] !== 'cancelled'): ?>
  <div class="card" style="margin-bottom:24px;overflow-x:auto">
    <div style="display:flex;align-items:flex-start;min-width:500px;padding:8px 0">
      <?php foreach ($statuses as $i => $st): $done=$i<$current; $active=$i===$current; ?>
      <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:8px;position:relative;text-align:center">
        <?php if ($i > 0): ?>
        <div style="position:absolute;left:-50%;top:22px;right:50%;height:3px;background:<?= $done||$active?'var(--green-mid)':'var(--border)' ?>;z-index:0"></div>
        <?php endif; ?>
        <div style="width:44px;height:44px;border-radius:50%;background:<?= $done?'var(--green-mid)':($active?'var(--amber)':'var(--border)') ?>;display:flex;align-items:center;justify-content:center;color:<?= $done||$active?'#fff':'var(--text-muted)' ?>;font-size:1rem;position:relative;z-index:1;box-shadow:<?= $active?'0 0 0 4px rgba(233,168,58,.25)':'' ?>">
          <?php if ($done): ?><i class="fa fa-check"></i><?php else: ?><i class="fa <?= $icons[$i] ?>"></i><?php endif; ?>
        </div>
        <div style="font-size:.75rem;font-weight:<?= $done||$active?'700':'500' ?>;color:<?= $done?'var(--green-dark)':($active?'var(--amber)':'var(--text-muted)') ?>;line-height:1.3"><?= $labels[$i] ?></div>
        <?php if ($active): ?><div style="font-size:.65rem;color:var(--amber);font-weight:600">Current Status</div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php else: ?>
  <div style="background:#fde8e8;border:1px solid #f5c2c2;border-radius:var(--radius-md);padding:16px 20px;margin-bottom:24px;color:#9b2c2c">
    <i class="fa fa-circle-xmark"></i> This order has been cancelled.
    <?php if ($order['notes']): ?> Reason: <?= h($order['notes']) ?><?php endif; ?>
  </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start">
    <div>
      <!-- Items -->
      <div class="card" style="margin-bottom:20px">
        <h3 style="font-family:var(--font-display);font-size:1rem;color:var(--green-dark);margin-bottom:14px">Order Items</h3>
        <?php foreach ($items as $it): ?>
        <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);font-size:.875rem">
          <div>
            <div style="font-weight:600"><?= h($it['product_name']) ?><?= $it['variant_name']?" ({$it['variant_name']})":'' ?></div>
            <div style="color:var(--text-muted)">Qty: <?= $it['quantity'] ?> × <?= price($it['price']) ?></div>
          </div>
          <div style="font-weight:700;color:var(--green-dark)"><?= price($it['total']) ?></div>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:12px;text-align:right">
          <?php if ($order['discount_amount'] > 0): ?><div style="font-size:.83rem;color:var(--green-mid);margin-bottom:4px">Coupon: −<?= price($order['discount_amount']) ?></div><?php endif; ?>
          <div style="font-size:.83rem;color:var(--text-muted)">Shipping: <?= $order['shipping_amount']>0?price($order['shipping_amount']):'FREE' ?></div>
          <div style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;color:var(--green-dark);margin-top:4px">Total: <?= price($order['final_amount']) ?></div>
        </div>
      </div>

      <!-- Timeline -->
      <?php if (!empty($history)): ?>
      <div class="card">
        <h3 style="font-family:var(--font-display);font-size:1rem;color:var(--green-dark);margin-bottom:14px">Order Timeline</h3>
        <?php foreach (array_reverse($history) as $h): ?>
        <div style="display:flex;gap:14px;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--border)">
          <div style="width:8px;height:8px;border-radius:50%;background:var(--green-mid);flex-shrink:0;margin-top:6px"></div>
          <div>
            <div style="font-size:.83rem;font-weight:600;text-transform:capitalize;color:var(--text-primary)"><?= str_replace('_',' ',$h['status']) ?></div>
            <?php if ($h['note']): ?><div style="font-size:.78rem;color:var(--text-muted)"><?= h($h['note']) ?></div><?php endif; ?>
            <div style="font-size:.72rem;color:var(--text-light)"><i class="fa fa-clock"></i> <?= date('d M Y, h:i A', strtotime($h['created_at'])) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div>
      <div class="card">
        <h3 style="font-family:var(--font-display);font-size:.95rem;color:var(--green-dark);margin-bottom:12px"><i class="fa fa-location-dot" style="color:var(--green-mid)"></i> Delivery Address</h3>
        <div style="font-size:.875rem;line-height:1.8;color:var(--text-body)">
          <strong><?= h($order['ship_name']) ?></strong><br>
          <?= h($order['address_line1']) ?><br>
          <?= h($order['city']) ?>, <?= h($order['state']) ?> — <?= h($order['pincode']) ?><br>
          📞 <?= h($order['ship_phone']) ?>
        </div>
      </div>
      <div style="margin-top:12px;background:var(--green-ghost);border-radius:var(--radius-md);padding:16px;text-align:center">
        <p style="font-size:.83rem;color:var(--text-muted);margin-bottom:10px">Need help with your order?</p>
        <a href="https://wa.me/919876543210?text=Help+with+order+<?= h($order['order_number']) ?>" target="_blank" class="btn btn-sm" style="background:#25d366;color:#fff;border-radius:var(--radius-full)"><i class="fab fa-whatsapp"></i> WhatsApp Us</a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
