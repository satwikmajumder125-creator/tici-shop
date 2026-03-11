<?php
require_once __DIR__ . '/../includes/config.php';
if (!isLoggedIn()) { header('Location: ' . SITE_URL . '/account/login.php'); exit; }
$pageTitle    = 'My Account — TiCi NatureLab';
$accountPage  = 'dashboard';
$db = db();
$uid = $_SESSION['user_id'];

$user = $_SESSION['user'];
if (!$user) {
    $u = $db->prepare("SELECT * FROM users WHERE id=?"); $u->execute([$uid]); $user = $u->fetch();
}

// Stats
$totalOrders = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id=?"); $totalOrders->execute([$uid]);
$totalOrders = $totalOrders->fetchColumn();

$totalSpent = $db->prepare("SELECT COALESCE(SUM(final_amount),0) FROM orders WHERE user_id=? AND order_status NOT IN ('cancelled')"); $totalSpent->execute([$uid]);
$totalSpent = $totalSpent->fetchColumn();

$wishCount = getWishlistCount();

$pending = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id=? AND order_status='pending'"); $pending->execute([$uid]);
$pending = $pending->fetchColumn();

// Recent orders
$recent = $db->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT 5"); $recent->execute([$uid]);
$recent = $recent->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding:32px 20px 60px">
  <div class="account-layout">
    <?php include __DIR__ . '/../includes/account-nav.php'; ?>

    <main>
      <h1 style="font-family:var(--font-display);font-size:1.8rem;color:var(--green-dark);margin-bottom:24px">
        Welcome back, <?= h(explode(' ', $user['name'])[0]) ?>! 👋
      </h1>

      <!-- Stats -->
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px;margin-bottom:28px">
        <?php foreach ([
          ['Total Orders', $totalOrders, 'fa-bag-shopping', 'var(--green-mid)'],
          ['Total Spent', price($totalSpent), 'fa-indian-rupee-sign', 'var(--amber)'],
          ['Wishlist Items', $wishCount, 'fa-heart', '#e05252'],
          ['Pending Orders', $pending, 'fa-clock', 'var(--terracotta)'],
        ] as [$label,$val,$icon,$color]): ?>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px;text-align:center">
          <div style="width:44px;height:44px;border-radius:50%;background:<?= $color ?>22;display:flex;align-items:center;justify-content:center;margin:0 auto 10px">
            <i class="fa <?= $icon ?>" style="color:<?= $color ?>;font-size:1.1rem"></i>
          </div>
          <div style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--green-dark)"><?= $val ?></div>
          <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-top:4px"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Recent Orders -->
      <div class="card" style="margin-bottom:20px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
          <h2 style="font-family:var(--font-display);font-size:1.1rem;color:var(--green-dark)">Recent Orders</h2>
          <a href="<?= SITE_URL ?>/account/orders.php" style="font-size:.8rem;color:var(--green-mid);font-weight:600">View All →</a>
        </div>
        <?php if (empty($recent)): ?>
        <div class="empty-state" style="padding:32px">
          <div class="empty-icon">🛒</div>
          <h3>No orders yet</h3>
          <p>Start exploring our plants!</p>
          <a href="<?= SITE_URL ?>/shop/index.php" class="btn btn-primary btn-sm" style="margin-top:12px">Shop Now</a>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto">
          <table style="width:100%;border-collapse:collapse">
            <thead><tr>
              <?php foreach (['Order #','Date','Amount','Status',''] as $h): ?>
              <th style="text-align:left;font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);padding:8px 12px;border-bottom:2px solid var(--border)"><?= $h ?></th>
              <?php endforeach; ?>
            </tr></thead>
            <tbody>
              <?php foreach ($recent as $ord):
                $statusColors = ['pending'=>'#f0a500','confirmed'=>'#3b9dd6','packed'=>'#8b6ebc','shipped'=>'#2d9b6f','out_for_delivery'=>'#1f7c56','delivered'=>'#1a5c3a','cancelled'=>'#c0392b'];
                $col = $statusColors[$ord['order_status']] ?? '#666';
              ?>
              <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:12px"><a href="<?= SITE_URL ?>/account/order-detail.php?order=<?= h($ord['order_number']) ?>" style="font-weight:700;color:var(--green-mid)"><?= h($ord['order_number']) ?></a></td>
                <td style="padding:12px;font-size:.83rem;color:var(--text-muted)"><?= date('d M Y', strtotime($ord['created_at'])) ?></td>
                <td style="padding:12px;font-weight:600"><?= price($ord['final_amount']) ?></td>
                <td style="padding:12px">
                  <span style="background:<?= $col ?>22;color:<?= $col ?>;font-size:.72rem;font-weight:700;text-transform:capitalize;padding:3px 10px;border-radius:var(--radius-full)">
                    <?= str_replace('_', ' ', $ord['order_status']) ?>
                  </span>
                </td>
                <td style="padding:12px">
                  <a href="<?= SITE_URL ?>/account/order-detail.php?order=<?= h($ord['order_number']) ?>" style="font-size:.78rem;color:var(--green-mid)">View →</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

      <!-- Quick links -->
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px">
        <?php foreach ([
          [SITE_URL.'/account/wishlist.php', 'fa-heart','#e05252','My Wishlist','Browse saved plants'],
          [SITE_URL.'/account/addresses.php','fa-location-dot','var(--green-mid)','Addresses','Manage delivery addresses'],
          [SITE_URL.'/account/profile.php',  'fa-user-pen','var(--amber)','Edit Profile','Update your details'],
          [SITE_URL.'/pages/track-order.php','fa-truck','var(--terracotta)','Track Order','Check delivery status'],
        ] as [$url,$icon,$color,$title,$sub]): ?>
        <a href="<?= $url ?>" style="display:flex;gap:14px;align-items:center;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);padding:16px;transition:var(--transition)" onmouseover="this.style.borderColor='var(--border-strong)';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='var(--border)';this.style.transform=''">
          <div style="width:40px;height:40px;border-radius:10px;background:<?= $color ?>22;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fa <?= $icon ?>" style="color:<?= $color ?>"></i></div>
          <div><div style="font-weight:600;font-size:.875rem;color:var(--text-primary)"><?= $title ?></div><div style="font-size:.72rem;color:var(--text-muted)"><?= $sub ?></div></div>
        </a>
        <?php endforeach; ?>
      </div>
    </main>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
