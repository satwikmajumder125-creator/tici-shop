<?php
require_once __DIR__ . '/../includes/config.php';
if (!isLoggedIn()) { header('Location: ' . SITE_URL . '/account/login.php'); exit; }
$pageTitle   = 'Change Password — TiCi NatureLab';
$accountPage = 'password';
$db  = db();
$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $user = $db->prepare("SELECT password FROM users WHERE id=?"); $user->execute([$uid]); $user=$user->fetch();

    if (!password_verify($current, $user['password'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new, PASSWORD_BCRYPT), $uid]);
        setFlash('success', 'Password changed successfully!');
        header('Location: ' . SITE_URL . '/account/password.php'); exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container" style="padding:32px 20px 60px">
  <div class="account-layout">
    <?php include __DIR__ . '/../includes/account-nav.php'; ?>
    <main>
      <h1 style="font-family:var(--font-display);font-size:1.8rem;color:var(--green-dark);margin-bottom:24px">Change Password</h1>
      <?php if (isset($error)): ?><div class="flash-msg flash-error"><?= h($error) ?></div><?php endif; ?>
      <div class="card" style="max-width:400px">
        <form method="POST">
          <div class="form-group"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" required></div>
          <div class="form-group"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" required minlength="8" placeholder="At least 8 characters"></div>
          <div class="form-group"><label class="form-label">Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
          <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
      </div>
    </main>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
