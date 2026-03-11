<?php
require_once __DIR__ . '/../includes/config.php';
if (isLoggedIn()) { header('Location: ' . SITE_URL . '/account/index.php'); exit; }
$pageTitle = 'Login — TiCi NatureLab';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter email and password.';
    } else {
        $stmt = db()->prepare("SELECT * FROM users WHERE email=? AND status=1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user']    = $user;
            // Merge guest cart
            $sid = getSessionId();
            db()->prepare("UPDATE cart_items SET user_id=?, session_id=NULL WHERE session_id=? AND user_id IS NULL")->execute([$user['id'], $sid]);
            $redirect = $_GET['redirect'] ?? (SITE_URL . '/account/index.php');
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
include __DIR__ . '/../includes/header.php';
?>

<div style="min-height:60vh;display:flex;align-items:center;justify-content:center;padding:40px 20px">
  <div style="width:100%;max-width:420px">
    <!-- Logo top -->
    <div style="text-align:center;margin-bottom:28px">
      <a href="<?= SITE_URL ?>/index.php" class="site-logo" style="justify-content:center;display:inline-flex">
        <div class="logo-icon">🌿</div>
        <div class="logo-text"><span class="logo-name">TiCi NatureLab</span></div>
      </a>
      <h1 style="font-family:var(--font-display);font-size:1.6rem;color:var(--green-dark);margin-top:16px">Welcome Back</h1>
      <p style="color:var(--text-muted);font-size:.875rem">Login to your TiCi account</p>
    </div>

    <?php if ($error): ?>
    <div class="flash-msg flash-error" style="margin-bottom:16px"><i class="fa fa-circle-xmark"></i> <?= h($error) ?></div>
    <?php endif; ?>

    <div class="card">
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" required autofocus value="<?= h($_POST['email'] ?? '') ?>" placeholder="your@email.com">
        </div>
        <div class="form-group">
          <label class="form-label" style="display:flex;justify-content:space-between">
            Password
            <a href="<?= SITE_URL ?>/account/forgot-password.php" style="font-size:.78rem;color:var(--green-mid);font-weight:normal">Forgot password?</a>
          </label>
          <div style="position:relative">
            <input type="password" name="password" id="pwdInput" class="form-control" required placeholder="Your password">
            <button type="button" onclick="togglePwd()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:.9rem">
              <i class="fa fa-eye" id="pwdEye"></i>
            </button>
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:4px">
          <i class="fa fa-sign-in-alt"></i> Login
        </button>
      </form>

      <div class="divider" style="margin:20px 0;position:relative;text-align:center">
        <span style="background:var(--bg-card);padding:0 12px;color:var(--text-muted);font-size:.8rem;position:relative;z-index:1">or</span>
        <div style="position:absolute;top:50%;left:0;right:0;height:1px;background:var(--border)"></div>
      </div>

      <a href="https://wa.me/919876543210?text=I+need+help+logging+in+to+TiCi+NatureLab" target="_blank"
         style="display:flex;align-items:center;justify-content:center;gap:10px;padding:11px;border:1.5px solid #25d366;border-radius:var(--radius-full);color:#25d366;font-weight:600;font-size:.875rem;transition:var(--transition)"
         onmouseover="this.style.background='#25d366';this.style.color='#fff'" onmouseout="this.style.background='';this.style.color='#25d366'">
        <i class="fab fa-whatsapp" style="font-size:1.1rem"></i> Login help via WhatsApp
      </a>
    </div>

    <p style="text-align:center;margin-top:16px;font-size:.875rem;color:var(--text-muted)">
      New to TiCi? <a href="<?= SITE_URL ?>/account/register.php" style="color:var(--green-mid);font-weight:600">Create an account</a>
    </p>
  </div>
</div>

<script>
function togglePwd() {
  var i = document.getElementById('pwdInput');
  var e = document.getElementById('pwdEye');
  i.type = i.type === 'password' ? 'text' : 'password';
  e.className = i.type === 'password' ? 'fa fa-eye' : 'fa fa-eye-slash';
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
