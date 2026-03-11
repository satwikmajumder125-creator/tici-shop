<?php
require_once __DIR__ . '/../includes/config.php';
if (!isLoggedIn()) { header('Location: ' . SITE_URL . '/account/login.php'); exit; }
$db  = db();
$uid = $_SESSION['user_id'];
$orderNum = trim($_GET['order'] ?? '');
if (!$orderNum) { header('Location: ' . SITE_URL . '/account/orders.php'); exit; }

$stmt = $db->prepare("SELECT o.*, oa.name AS ship_name, oa.phone AS ship_phone, oa.address_line1, oa.city, oa.state, oa.pincode FROM orders o LEFT JOIN order_addresses oa ON oa.order_id=o.id WHERE o.order_number=? AND o.user_id=?");
$stmt->execute([$orderNum, $uid]);
$order = $stmt->fetch();
if (!$order) { header('Location: ' . SITE_URL . '/account/orders.php'); exit; }

$items = $db->prepare("SELECT oi.*, p.slug FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?");
$items->execute([$order['id']]); $items=$items->fetchAll();

$history = $db->prepare("SELECT * FROM order_status_history WHERE order_id=? ORDER BY created_at ASC");
$history->execute([$order['id']]); $history=$history->fetchAll();

$pageTitle   = 'Order ' . h($orderNum) . ' — TiCi NatureLab';
$accountPage = 'orders';
$statuses = ['pending','confirmed','packed','shipped','out_for_delivery','delivered'];
$labels   = ['Order Placed','Confirmed','Packed','Shipped','Out for Delivery','Delivered'];
$current  = array_search($order['order_status'], $statuses);
$statusColors = ['pending'=>'#f0a500','confirmed'=>'#3b9dd6','packed'=>'#8b6ebc','shipped'=>'#2d9b6f','out_for_delivery'=>'#1f7c56','delivered'=>'#1a5c3a','cancelled'=>'#c0392b'];
$col = $statusColors[$order['order_status']] ?? '#666';

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding:32px 20px 60px">
  <div class="account-layout">
    <?php include __DIR__ . '/../includes/account-nav.php'; ?>
    <main>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
        <div>
          <a href="<?= SITE_URL ?>/account/orders.php" style="font-size:.83rem;color:var(--green-mid)">← Back to Orders</a>
          <h1 style="font-family:var(--font-display);font-size:1.6rem;color:var(--green-dark);margin-top:6px"><?= h($orderNum) ?></h1>
        </div>
        <span style="background:<?= $col ?>22;color:<?= $col ?>;font-size:.83rem;font-weight:700;text-transform:capitalize;padding:6px 16px;border-radius:var(--radius-full)">
          <?= str_replace('_',' ',$order['order_status']) ?>
        </span>
      </div>

      <!-- Progress bar -->
      <?php if ($order['order_status'] !== 'cancelled'): ?>
      <div class="card" style="margin-bottom:20px">
        <div class="order-progress">
          <?php foreach ($statuses as $i => $st): ?>
          <div class="progress-step">
            <div class="progress-dot <?= $i < $current ? 'done' : ($i === $current ? 'current' : '') ?>">
              <?= $i < $current ? '<i class="fa fa-check" style="font-size:.6rem"></i>' : ($i + 1) ?>
            </div>
            <div class="progress-label"><?= $labels[$i] ?></div>
          </div>
          <?php if ($i < count($statuses) - 1): ?>
          <div class="progress-line <?= $i < $current ? 'done' : '' ?>"></div>
          <?php endif; endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">
        <div>
          <!-- Items -->
          <div class="card" style="margin-bottom:16px">
            <h3 style="font-family:var(--font-display);font-size:1rem;color:var(--green-dark);margin-bottom:14px">Items Ordered (<?= count($items) ?>)</h3>
            <?php foreach ($items as $it): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border);gap:12px">
              <div style="flex:1">
                <div style="font-weight:600;font-size:.875rem">
                  <?php if ($it['slug']): ?><a href="<?= SITE_URL ?>/shop/product.php?slug=<?= h($it['slug']) ?>" style="color:var(--text-primary)"><?= h($it['product_name']) ?></a><?php else: ?><?= h($it['product_name']) ?><?php endif; ?>
                </div>
                <?php if ($it['variant_name']): ?><div style="font-size:.75rem;color:var(--text-muted)"><?= h($it['variant_name']) ?></div><?php endif; ?>
                <div style="font-size:.78rem;color:var(--text-muted)">Qty: <?= $it['quantity'] ?> × <?= price($it['price']) ?></div>
              </div>
              <div style="font-weight:700;color:var(--green-dark);white-space:nowrap"><?= price($it['total']) ?></div>
            </div>
            <?php endforeach; ?>

            <!-- Totals -->
            <div style="margin-top:14px">
              <div class="summary-row"><span style="font-size:.875rem">Subtotal</span><span><?= price($order['total_amount']) ?></span></div>
              <?php if ($order['discount_amount'] > 0): ?>
              <div class="summary-row" style="color:var(--green-mid)"><span style="font-size:.875rem">Coupon (<?= h($order['coupon_code']) ?>)</span><span>−<?= price($order['discount_amount']) ?></span></div>
              <?php endif; ?>
              <div class="summary-row"><span style="font-size:.875rem">Shipping</span><span><?= $order['shipping_amount'] > 0 ? price($order['shipping_amount']) : 'FREE' ?></span></div>
              <div class="summary-row summary-total"><span>Total</span><span><?= price($order['final_amount']) ?></span></div>
            </div>
          </div>

          <!-- Status history -->
          <?php if (!empty($history)): ?>
          <div class="card">
            <h3 style="font-family:var(--font-display);font-size:1rem;color:var(--green-dark);margin-bottom:14px">Order Timeline</h3>
            <div style="position:relative;padding-left:24px">
              <div style="position:absolute;left:7px;top:0;bottom:0;width:2px;background:var(--border)"></div>
              <?php foreach (array_reverse($history) as $h): ?>
              <div style="position:relative;margin-bottom:14px">
                <div style="position:absolute;left:-20px;top:2px;width:10px;height:10px;border-radius:50%;background:var(--green-mid)"></div>
                <div style="font-size:.83rem;font-weight:600;text-transform:capitalize;color:var(--text-primary)"><?= str_replace('_',' ',$h['status']) ?></div>
                <?php if ($h['note']): ?><div style="font-size:.78rem;color:var(--text-muted)"><?= h($h['note']) ?></div><?php endif; ?>
                <div style="font-size:.72rem;color:var(--text-light)"><i class="fa fa-clock"></i> <?= date('d M Y, h:i A', strtotime($h['created_at'])) ?></div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Right panel -->
        <div>
          <!-- Delivery address -->
          <div class="card" style="margin-bottom:16px">
            <h3 style="font-family:var(--font-display);font-size:.95rem;color:var(--green-dark);margin-bottom:12px"><i class="fa fa-location-dot" style="color:var(--green-mid)"></i> Delivery Address</h3>
            <div style="font-size:.875rem;line-height:1.8;color:var(--text-body)">
              <strong><?= h($order['ship_name']) ?></strong><br>
              <?= h($order['address_line1']) ?><br>
              <?= h($order['city']) ?>, <?= h($order['state']) ?> — <?= h($order['pincode']) ?><br>
              📞 <?= h($order['ship_phone']) ?>
            </div>
          </div>

          <!-- Payment info -->
          <div class="card" style="margin-bottom:16px">
            <h3 style="font-family:var(--font-display);font-size:.95rem;color:var(--green-dark);margin-bottom:12px"><i class="fa fa-credit-card" style="color:var(--green-mid)"></i> Payment</h3>
            <div style="font-size:.875rem;color:var(--text-body)">
              <div style="display:flex;justify-content:space-between;margin-bottom:6px"><span>Method</span><span style="font-weight:600;text-transform:uppercase"><?= h($order['payment_method']) ?></span></div>
              <div style="display:flex;justify-content:space-between">
                <span>Status</span>
                <span style="font-weight:600;color:<?= $order['payment_status']==='paid'?'var(--green-mid)':'var(--amber)' ?>;text-transform:capitalize"><?= h($order['payment_status']) ?></span>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div style="display:flex;flex-direction:column;gap:8px">
            <a href="<?= SITE_URL ?>/pages/track-order.php?order=<?= h($orderNum) ?>" class="btn btn-primary btn-full">
              <i class="fa fa-truck"></i> Track Order
            </a>
            <a href="https://wa.me/919876543210?text=Help+with+order+<?= h($orderNum) ?>" target="_blank" class="btn btn-secondary btn-full" style="border-color:#25d366;color:#25d366">
              <i class="fab fa-whatsapp"></i> WhatsApp Support
            </a>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
