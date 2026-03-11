<?php
require_once __DIR__ . '/../includes/config.php';
$pageTitle = 'Contact Us — TiCi NatureLab';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $message) {
        // In production: send email via mail() or SMTP
        // mail('hello@ticinaturelab.com', $subject, "From: $name <$email>\n\n$message");
        $success = true;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<section style="background:linear-gradient(135deg,var(--green-dark),var(--green-mid));padding:50px 0;text-align:center">
  <div class="container">
    <h1 style="font-family:var(--font-display);font-size:2.2rem;color:#fff;margin-bottom:8px">Get in Touch</h1>
    <p style="color:rgba(255,255,255,.75)">We're here to help with plant care, orders, and anything aquascaping!</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:40px;align-items:start">
      <!-- Contact Info -->
      <div>
        <h2 style="font-family:var(--font-display);font-size:1.4rem;color:var(--green-dark);margin-bottom:20px">Contact Details</h2>
        <?php foreach ([
          ['fa-phone','Phone','fa-whatsapp','+91 98765 43210','tel:+919876543210'],
          ['fa-envelope','Email',null,'hello@ticinaturelab.com','mailto:hello@ticinaturelab.com'],
          ['fa-clock','Hours',null,'Mon–Sat, 10am – 7pm IST',null],
          ['fa-location-dot','Location',null,'TiCi NatureLab, India',null],
        ] as [$icon,$label,$icon2,$val,$href]): ?>
        <div style="display:flex;gap:14px;padding:16px;background:var(--green-ghost);border:1px solid var(--border);border-radius:var(--radius-md);margin-bottom:12px">
          <div style="width:40px;height:40px;border-radius:10px;background:var(--green-mid);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;font-size:.95rem"><i class="fa <?= $icon ?>"></i></div>
          <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:3px"><?= $label ?></div>
            <?php if ($href): ?>
            <a href="<?= $href ?>" style="font-weight:600;color:var(--green-dark);font-size:.9rem"><?= h($val) ?></a>
            <?php else: ?>
            <div style="font-weight:600;color:var(--green-dark);font-size:.9rem"><?= h($val) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>

        <!-- Social -->
        <div style="margin-top:20px">
          <div style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:12px">Find us on</div>
          <div style="display:flex;gap:10px">
            <?php foreach ([['fab fa-instagram','#e1306c'],['fab fa-facebook-f','#1877f2'],['fab fa-youtube','#ff0000'],['fab fa-whatsapp','#25d366']] as [$icon,$col]): ?>
            <a href="#" style="width:40px;height:40px;border-radius:50%;background:<?= $col ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.95rem;transition:transform .2s" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform=''">
              <i class="<?= $icon ?>"></i>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Form -->
      <div class="card">
        <h2 style="font-family:var(--font-display);font-size:1.3rem;color:var(--green-dark);margin-bottom:20px">Send a Message</h2>
        <?php if ($success): ?>
        <div class="flash-msg flash-success"><i class="fa fa-circle-check"></i> Message sent! We'll reply within 24 hours.</div>
        <?php endif; ?>
        <form method="POST">
          <div class="form-row">
            <div class="form-group"><label class="form-label">Your Name</label><input type="text" name="name" class="form-control" required value="<?= h($_POST['name']??'') ?>" placeholder="Full name"></div>
            <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required value="<?= h($_POST['email']??'') ?>" placeholder="your@email.com"></div>
          </div>
          <div class="form-group">
            <label class="form-label">Subject</label>
            <select name="subject" class="form-control">
              <option>Order Query</option>
              <option>Plant Care Help</option>
              <option>Shipping Issue</option>
              <option>Return / Refund</option>
              <option>Wholesale Enquiry</option>
              <option>Other</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">Message</label><textarea name="message" class="form-control" rows="5" required placeholder="How can we help you?"><?= h($_POST['message']??'') ?></textarea></div>
          <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Send Message</button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
