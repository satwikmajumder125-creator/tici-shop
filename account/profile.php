<?php
require_once __DIR__ . '/../includes/config.php';
if (!isLoggedIn()) { header('Location: ' . SITE_URL . '/account/login.php'); exit; }
$pageTitle   = 'Edit Profile — TiCi NatureLab';
$accountPage = 'profile';
$db  = db();
$uid = $_SESSION['user_id'];
$user= $db->prepare("SELECT * FROM users WHERE id=?"); $user->execute([$uid]); $user=$user->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if (!$name) { $error = 'Name is required.'; }
    else {
        $db->prepare("UPDATE users SET name=?, phone=? WHERE id=?")->execute([$name,$phone,$uid]);
        $_SESSION['user']['name']  = $name;
        $_SESSION['user']['phone'] = $phone;
        setFlash('success','Profile updated successfully.');
        header('Location: ' . SITE_URL . '/account/profile.php'); exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container" style="padding:32px 20px 60px">
  <div class="account-layout">
    <?php include __DIR__ . '/../includes/account-nav.php'; ?>
    <main>
      <h1 style="font-family:var(--font-display);font-size:1.8rem;color:var(--green-dark);margin-bottom:24px">Edit Profile</h1>
      <?php if (isset($error)): ?><div class="flash-msg flash-error"><?= h($error) ?></div><?php endif; ?>
      <div class="card" style="max-width:480px">
        <form method="POST">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" required value="<?= h($user['name']) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" value="<?= h($user['email']) ?>" readonly style="background:var(--green-ghost);color:var(--text-muted)">
            <div style="font-size:.72rem;color:var(--text-muted);margin-top:4px">Email cannot be changed. Contact support if needed.</div>
          </div>
          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="tel" name="phone" class="form-control" value="<?= h($user['phone'] ?? '') ?>" placeholder="10-digit number">
          </div>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
      </div>
    </main>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
