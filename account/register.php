<?php
require_once __DIR__ . '/../includes/config.php';
if (isLoggedIn()) { header('Location: ' . SITE_URL . '/account/index.php'); exit; }
$pageTitle = 'Create Account — TiCi NatureLab';
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$password) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db = db();
        $existing = $db->prepare("SELECT id FROM users WHERE email=?");
        $existing->execute([$email]);
        if ($existing->fetch()) {
            $error = 'An account with this email already exists. <a href="' . SITE_URL . '/account/login.php">Login instead?</a>';
        } else {
            $db->prepare("INSERT INTO users (name, email, phone, password, status) VALUES (?,?,?,?,1)")
               ->execute([$name, $email, $phone, password_hash($password, PASSWORD_BCRYPT)]);
            $uid = $db->lastInsertId();
            $_SESSION['user_id'] = $uid;
            $_SESSION['user']    = ['id'=>$uid,'name'=>$name,'email'=>$email,'phone'=>$phone];
            // Merge guest cart
            $sid = getSessionId();
            $db->prepare("UPDATE cart_items SET user_id=?, session_id=NULL WHERE session_id=?")->execute([$uid, $sid]);
            setFlash('success', '🎉 Welcome to TiCi NatureLab, ' . $name . '!');
            header('Location: ' . SITE_URL . '/account/index.php');
            exit;
        }
    }
}
include __DIR__ . '/../includes/header.php';
?>

<div style="min-height:60vh;display:flex;align-items:center;justify-content:center;padding:40px 20px">
  <div style="width:100%;max-width:460px">
    <div style="text-align:center;margin-bottom:28px">
      <a href="<?= SITE_URL ?>/index.php" class="site-logo" style="justify-content:center;display:inline-flex">
        <div class="logo-icon">🌿</div>
        <div class="logo-text"><span class="logo-name">TiCi NatureLab</span></div>
      </a>
      <h1 style="font-family:var(--font-display);font-size:1.6rem;color:var(--green-dark);margin-top:16px">Create Account</h1>
      <p style="color:var(--text-muted);font-size:.875rem">Join thousands of plant enthusiasts</p>
    </div>

    <?php if ($error): ?>
    <div class="flash-msg flash-error" style="margin-bottom:16px"><i class="fa fa-circle-xmark"></i> <?= $error ?></div>
    <?php endif; ?>

    <div class="card">
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" required autofocus value="<?= h($_POST['name'] ?? '') ?>" placeholder="Your full name">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?= h($_POST['email'] ?? '') ?>" placeholder="your@email.com">
          </div>
          <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="tel" name="phone" class="form-control" value="<?= h($_POST['phone'] ?? '') ?>" placeholder="10-digit number">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required placeholder="Min 8 characters">
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat password">
          </div>
        </div>
        <label style="display:flex;align-items:flex-start;gap:8px;font-size:.8rem;color:var(--text-body);margin-bottom:16px;cursor:pointer">
          <input type="checkbox" required style="accent-color:var(--green-mid);margin-top:2px;flex-shrink:0">
          I agree to TiCi's <a href="<?= SITE_URL ?>/pages/terms.php" style="color:var(--green-mid)">Terms &amp; Conditions</a> and <a href="<?= SITE_URL ?>/pages/privacy-policy.php" style="color:var(--green-mid)">Privacy Policy</a>
        </label>
        <button type="submit" class="btn btn-primary btn-full btn-lg">
          <i class="fa fa-leaf"></i> Create My Account
        </button>
      </form>
    </div>

    <p style="text-align:center;margin-top:16px;font-size:.875rem;color:var(--text-muted)">
      Already have an account? <a href="<?= SITE_URL ?>/account/login.php" style="color:var(--green-mid);font-weight:600">Login</a>
    </p>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
