<?php
require_once __DIR__ . '/../includes/config.php';
$pageTitle = 'Order Confirmed — TiCi NatureLab';
$db = db();

$orderNum = trim($_GET['order'] ?? '');
if (!$orderNum) { header('Location: ' . SITE_URL . '/index.php'); exit; }

$stmt = $db->prepare("SELECT o.*, oa.name, oa.phone, oa.address_line1, oa.city, oa.state, oa.pincode FROM orders o LEFT JOIN order_addresses oa ON oa.order_id=o.id WHERE o.order_number=?");
$stmt->execute([$orderNum]);
$order = $stmt->fetch();
if (!$order) { header('Location: ' . SITE_URL . '/index.php'); exit; }

$items = $db->prepare("SELECT * FROM order_items WHERE order_id=?");
$items->execute([$order['id']]);
$orderItems = $items->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding:60px 20px;max-width:700px">
  <!-- Success Banner -->
  <div style="text-align:center;margin-bottom:40px">
    <div style="width:80px;height:80px;background:linear-gradient(135deg,var(--green-mid),var(--green-dark));border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:2rem">✅</div>
    <h1 style="font-family:var(--font-display);font-size:2rem;color:var(--green-dark);margin-bottom:8px">Order Confirmed!</h1>
    <p style="color:var(--text-muted);font-size:1rem">Thank you for your order! We've received it and will start processing soon.</p>
  </div>

  <!-- Order Info Card -->
  <div class="card" style="margin-bottom:20px">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
      <div>
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:4px">Order Number</div>
        <div style="font-family:var(--font-display);font-size:1.2rem;color:var(--green-dark);font-weight:700"><?= h($orderNum) ?></div>
      </div>
      <div>
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:4px">Order Date</div>
        <div style="font-weight:600"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
      </div>
      <div>
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:4px">Payment Method</div>
        <div style="font-weight:600;text-transform:uppercase"><?= h($order['payment_method']) ?></div>
      </div>
      <div>
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:4px">Order Total</div>
        <div style="font-family:var(--font-display);font-size:1.1rem;color:var(--green-dark);font-weight:700"><?= price($order['final_amount']) ?></div>
      </div>
    </div>
  </div>

  <!-- Status Steps -->
  <div class="card" style="margin-bottom:20px">
    <div style="font-weight:700;font-size:.875rem;margin-bottom:16px;color:var(--text-body)">Order Status</div>
    <div class="order-progress">
      <?php $statuses = ['pending','confirmed','packed','shipped','out_for_delivery','delivered'];
            $labels   = ['Placed','Confirmed','Packed','Shipped','Out for Delivery','Delivered'];
            $current  = array_search($order['order_status'], $statuses);
            foreach ($statuses as $i => $st): ?>
      <div class="progress-step">
        <div class="progress-dot <?= $i < $current ? 'done' : ($i === $current ? 'current' : '') ?>">
          <?= $i < $current ? '<i class="fa fa-check" style="font-size:.65rem"></i>' : ($i + 1) ?>
        </div>
        <div class="progress-label"><?= $labels[$i] ?></div>
      </div>
      <?php if ($i < count($statuses) - 1): ?>
      <div class="progress-line <?= $i < $current ? 'done' : '' ?>"></div>
      <?php endif; endforeach; ?>
    </div>
  </div>

  <!-- Items -->
  <div class="card" style="margin-bottom:20px">
    <div style="font-weight:700;font-size:.875rem;margin-bottom:14px;color:var(--text-body)">Items Ordered</div>
    <?php foreach ($orderItems as $item): ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border);font-size:.875rem">
      <div>
        <div style="font-weight:600"><?= h($item['product_name']) ?></div>
        <?php if ($item['variant_name']): ?><div style="font-size:.75rem;color:var(--text-muted)"><?= h($item['variant_name']) ?></div><?php endif; ?>
        <div style="color:var(--text-muted);font-size:.78rem">Qty: <?= $item['quantity'] ?> × <?= price($item['price']) ?></div>
      </div>
      <div style="font-weight:700;color:var(--green-dark)"><?= price($item['total']) ?></div>
    </div>
    <?php endforeach; ?>
    <div style="display:flex;justify-content:flex-end;gap:16px;margin-top:12px;font-size:.9rem">
      <?php if ($order['discount_amount'] > 0): ?>
      <div style="color:var(--green-mid)">Discount: −<?= price($order['discount_amount']) ?></div>
      <?php endif; ?>
      <div style="color:var(--text-muted)">Shipping: <?= $order['shipping_amount'] > 0 ? price($order['shipping_amount']) : 'FREE' ?></div>
      <div style="font-family:var(--font-display);font-size:1rem;font-weight:700;color:var(--green-dark)">Total: <?= price($order['final_amount']) ?></div>
    </div>
  </div>

  <!-- Delivery Address -->
  <div class="card" style="margin-bottom:24px">
    <div style="font-weight:700;font-size:.875rem;margin-bottom:10px;color:var(--text-body)">Delivery Address</div>
    <div style="font-size:.875rem;color:var(--text-body);line-height:1.7">
      <strong><?= h($order['name']) ?></strong><br>
      <?= h($order['address_line1']) ?><br>
      <?= h($order['city']) ?>, <?= h($order['state']) ?> — <?= h($order['pincode']) ?><br>
      📞 <?= h($order['phone']) ?>
    </div>
  </div>

  <!-- Actions -->
  <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center">
    <a href="<?= SITE_URL ?>/pages/track-order.php?order=<?= h($orderNum) ?>" class="btn btn-primary">
      <i class="fa fa-truck"></i> Track Order
    </a>
    <?php if (isLoggedIn()): ?>
    <a href="<?= SITE_URL ?>/account/orders.php" class="btn btn-secondary">My Orders</a>
    <?php endif; ?>
    <a href="<?= SITE_URL ?>/shop/index.php" class="btn btn-secondary">Continue Shopping</a>
  </div>

  <!-- Need help -->
  <div style="text-align:center;margin-top:28px;padding:16px;background:var(--green-ghost);border-radius:var(--radius-md)">
    <p style="font-size:.83rem;color:var(--text-muted)">Need help? WhatsApp us at <strong>+91 98765 43210</strong> with your order number.</p>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
