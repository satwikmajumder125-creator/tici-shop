<?php
// account-nav.php — reusable account sidebar
$accountPage = $accountPage ?? '';
$user = currentUser();
if (!$user && isLoggedIn()) {
    $u = db()->prepare("SELECT * FROM users WHERE id=?");
    $u->execute([$_SESSION['user_id']]);
    $user = $u->fetch();
    $_SESSION['user'] = $user;
}
?>
<div class="account-nav">
  <div class="account-nav-header">
    <div class="account-nav-avatar"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
    <div class="account-nav-name"><?= h($user['name'] ?? 'User') ?></div>
    <div class="account-nav-email"><?= h($user['email'] ?? '') ?></div>
  </div>
  <div class="account-nav-links">
    <?php $links = [
      ['dashboard','fa-gauge-high','Dashboard',     '/account/index.php'],
      ['orders',   'fa-bag-shopping','My Orders',   '/account/orders.php'],
      ['wishlist', 'fa-heart',       'Wishlist',    '/account/wishlist.php'],
      ['addresses','fa-location-dot','Addresses',   '/account/addresses.php'],
      ['profile',  'fa-user-pen',    'Edit Profile','/account/profile.php'],
      ['password', 'fa-lock',        'Password',    '/account/password.php'],
    ]; foreach ($links as [$key,$icon,$label,$url]): ?>
    <a href="<?= SITE_URL . $url ?>" class="account-nav-link <?= $accountPage === $key ? 'active' : '' ?>">
      <i class="fa <?= $icon ?>"></i> <?= $label ?>
    </a>
    <?php endforeach; ?>
    <a href="<?= SITE_URL ?>/account/logout.php" class="account-nav-link" style="color:var(--terracotta)">
      <i class="fa fa-right-from-bracket" style="color:var(--terracotta)"></i> Logout
    </a>
  </div>
</div>
