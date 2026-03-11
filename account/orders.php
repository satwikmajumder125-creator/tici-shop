<?php
require_once __DIR__ . '/../includes/config.php';
if (!isLoggedIn()) { header('Location: ' . SITE_URL . '/account/login.php'); exit; }
$pageTitle   = 'My Orders — TiCi NatureLab';
$accountPage = 'orders';
$db  = db();
$uid = $_SESSION['user_id'];

$status = $_GET['status'] ?? '';
$page   = max(1,(int)($_GET['page']??1));
$perPage= 10;
$offset = ($page - 1) * $perPage;

$where  = ["user_id = ?"];
$params = [$uid];
if ($status) { $where[] = "order_status = ?"; $params[] = $status; }
$whereStr = implode(' AND ', $where);

$total = $db->prepare("SELECT COUNT(*) FROM orders WHERE $whereStr"); $total->execute($params);
$total = (int)$total->fetchColumn();
$pages = max(1, ceil($total / $perPage));

$stmt = $db->prepare("SELECT * FROM orders WHERE $whereStr ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$orders = $stmt->fetchAll();

$statuses = ['pending','confirmed','packed','shipped','out_for_delivery','delivered','cancelled'];
$statusColors = ['pending'=>'#f0a500','confirmed'=>'#3b9dd6','packed'=>'#8b6ebc','shipped'=>'#2d9b6f','out_for_delivery'=>'#1f7c56','delivered'=>'#1a5c3a','cancelled'=>'#c0392b'];

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding:32px 20px 60px">
  <div class="account-layout">
    <?php include __DIR__ . '/../includes/account-nav.php'; ?>
    <main>
      <h1 style="font-family:var(--font-display);font-size:1.8rem;color:var(--green-dark);margin-bottom:20px">My Orders</h1>

      <!-- Status filter tabs -->
      <div class="tabs-wrap" style="margin-bottom:24px">
        <a href="?" class="tab-btn <?= !$status?'active':'' ?>">All</a>
        <?php foreach ($statuses as $st): ?>
        <a href="?status=<?= $st ?>" class="tab-btn <?= $status===$st?'active':'' ?>"><?= ucwords(str_replace('_',' ',$st)) ?></a>
        <?php endforeach; ?>
      </div>

      <?php if (empty($orders)): ?>
      <div class="empty-state"><div class="empty-icon">📦</div><h3>No orders found</h3><p>No orders match this filter.</p><a href="<?= SITE_URL ?>/shop/index.php" class="btn btn-primary" style="margin-top:16px">Start Shopping</a></div>
      <?php else: ?>

      <div style="display:flex;flex-direction:column;gap:14px">
        <?php foreach ($orders as $ord):
          $col = $statusColors[$ord['order_status']] ?? '#666';
          $items = $db->prepare("SELECT * FROM order_items WHERE order_id=? LIMIT 3"); $items->execute([$ord['id']]); $items=$items->fetchAll();
        ?>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
          <!-- Header -->
          <div style="background:var(--green-ghost);border-bottom:1px solid var(--border);padding:14px 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
            <div style="display:flex;gap:20px;flex-wrap:wrap">
              <div><div style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Order</div><div style="font-weight:700;color:var(--green-dark)"><?= h($ord['order_number']) ?></div></div>
              <div><div style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Date</div><div style="font-weight:500;font-size:.875rem"><?= date('d M Y', strtotime($ord['created_at'])) ?></div></div>
              <div><div style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Total</div><div style="font-weight:700;color:var(--green-dark)"><?= price($ord['final_amount']) ?></div></div>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
              <span style="background:<?= $col ?>22;color:<?= $col ?>;font-size:.72rem;font-weight:700;text-transform:capitalize;padding:4px 12px;border-radius:var(--radius-full)">
                <?= str_replace('_',' ',$ord['order_status']) ?>
              </span>
              <a href="<?= SITE_URL ?>/account/order-detail.php?order=<?= h($ord['order_number']) ?>" class="btn btn-sm btn-secondary">View Details</a>
            </div>
          </div>
          <!-- Items preview -->
          <div style="padding:14px 20px">
            <?php foreach ($items as $item): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;font-size:.875rem;border-bottom:1px solid var(--border)">
              <span><?= h($item['product_name']) ?><?= $item['variant_name']?" ({$item['variant_name']})":'' ?> × <?= $item['quantity'] ?></span>
              <span style="font-weight:600"><?= price($item['total']) ?></span>
            </div>
            <?php endforeach; ?>
            <?php $total_items = $db->prepare("SELECT COUNT(*) FROM order_items WHERE order_id=?"); $total_items->execute([$ord['id']]); $tc=$total_items->fetchColumn(); if ($tc > 3): ?>
            <div style="font-size:.75rem;color:var(--text-muted);padding-top:6px">+ <?= $tc - 3 ?> more item(s)</div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($pages > 1): ?>
      <div class="pagination">
        <?php for ($i=1;$i<=$pages;$i++): ?>
        <a href="?status=<?= h($status) ?>&page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>

      <?php endif; ?>
    </main>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
