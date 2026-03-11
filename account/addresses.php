<?php
require_once __DIR__ . '/../includes/config.php';
if (!isLoggedIn()) { header('Location: ' . SITE_URL . '/account/login.php'); exit; }
$pageTitle   = 'My Addresses — TiCi NatureLab';
$accountPage = 'addresses';
$db  = db();
$uid = $_SESSION['user_id'];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name  = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $addr1 = trim($_POST['address_line1'] ?? '');
        $city  = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $pin   = trim($_POST['pincode'] ?? '');
        if ($name && $addr1 && $city && $state && strlen($pin) === 6) {
            $isDefault = !$db->prepare("SELECT COUNT(*) FROM user_addresses WHERE user_id=?")->execute([$uid]) ? 1 : 0;
            $db->prepare("INSERT INTO user_addresses (user_id,name,phone,address_line1,city,state,pincode,default_address) VALUES (?,?,?,?,?,?,?,?)")
               ->execute([$uid,$name,$phone,$addr1,$city,$state,$pin,$isDefault]);
            setFlash('success','Address added successfully.');
        } else { setFlash('error','Please fill all required fields.'); }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['addr_id'] ?? 0);
        $db->prepare("DELETE FROM user_addresses WHERE id=? AND user_id=?")->execute([$id,$uid]);
        setFlash('success','Address removed.');
    } elseif ($action === 'set_default') {
        $id = (int)($_POST['addr_id'] ?? 0);
        $db->prepare("UPDATE user_addresses SET default_address=0 WHERE user_id=?")->execute([$uid]);
        $db->prepare("UPDATE user_addresses SET default_address=1 WHERE id=? AND user_id=?")->execute([$id,$uid]);
        setFlash('success','Default address updated.');
    }
    header('Location: ' . SITE_URL . '/account/addresses.php'); exit;
}

$addresses = $db->prepare("SELECT * FROM user_addresses WHERE user_id=? ORDER BY default_address DESC, id DESC"); $addresses->execute([$uid]); $addresses=$addresses->fetchAll();
$states = ['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Delhi','Jammu & Kashmir','Ladakh'];

include __DIR__ . '/../includes/header.php';
?>
<div class="container" style="padding:32px 20px 60px">
  <div class="account-layout">
    <?php include __DIR__ . '/../includes/account-nav.php'; ?>
    <main>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
        <h1 style="font-family:var(--font-display);font-size:1.8rem;color:var(--green-dark)">My Addresses</h1>
        <button onclick="document.getElementById('addAddrModal').style.display='flex'" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Add Address</button>
      </div>

      <?php if (empty($addresses)): ?>
      <div class="empty-state"><div class="empty-icon">📍</div><h3>No saved addresses</h3><p>Add an address for faster checkout.</p>
        <button onclick="document.getElementById('addAddrModal').style.display='flex'" class="btn btn-primary" style="margin-top:16px">Add Address</button>
      </div>
      <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
        <?php foreach ($addresses as $addr): ?>
        <div style="background:var(--bg-card);border:<?= $addr['default_address']?'2px solid var(--green-bright)':'1px solid var(--border)' ?>;border-radius:var(--radius-lg);padding:20px;position:relative">
          <?php if ($addr['default_address']): ?>
          <div style="position:absolute;top:12px;right:12px;background:var(--green-mid);color:#fff;font-size:.65rem;font-weight:700;padding:2px 8px;border-radius:var(--radius-full);text-transform:uppercase;letter-spacing:.5px">Default</div>
          <?php endif; ?>
          <div style="font-weight:700;font-size:.9rem;color:var(--text-primary);margin-bottom:4px"><?= h($addr['name']) ?></div>
          <div style="font-size:.83rem;color:var(--text-body);line-height:1.7">
            <?= h($addr['address_line1']) ?><br>
            <?= h($addr['city']) ?>, <?= h($addr['state']) ?> — <?= h($addr['pincode']) ?><br>
            📞 <?= h($addr['phone']) ?>
          </div>
          <div style="display:flex;gap:8px;margin-top:14px">
            <?php if (!$addr['default_address']): ?>
            <form method="POST" style="display:inline">
              <input type="hidden" name="action" value="set_default">
              <input type="hidden" name="addr_id" value="<?= $addr['id'] ?>">
              <button type="submit" class="btn btn-sm btn-secondary" style="font-size:.72rem">Set Default</button>
            </form>
            <?php endif; ?>
            <form method="POST" style="display:inline" onsubmit="return confirm('Remove this address?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="addr_id" value="<?= $addr['id'] ?>">
              <button type="submit" class="btn btn-sm" style="border:1.5px solid var(--terracotta);color:var(--terracotta);font-size:.72rem;padding:5px 12px;border-radius:var(--radius-full)"><i class="fa fa-trash"></i> Remove</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </main>
  </div>
</div>

<!-- Add Address Modal -->
<div id="addAddrModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;align-items:center;justify-content:center;padding:20px">
  <div style="background:var(--bg-card);border-radius:var(--radius-lg);padding:28px;max-width:500px;width:100%;max-height:90vh;overflow-y:auto">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h3 style="font-family:var(--font-display);font-size:1.1rem;color:var(--green-dark)">Add New Address</h3>
      <button onclick="document.getElementById('addAddrModal').style.display='none'" style="color:var(--text-muted);font-size:1.2rem;background:none;border:none;cursor:pointer">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="form-row">
        <div class="form-group"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
        <div class="form-group"><label class="form-label">Phone *</label><input type="tel" name="phone" class="form-control" required></div>
      </div>
      <div class="form-group"><label class="form-label">Address *</label><input type="text" name="address_line1" class="form-control" required placeholder="House no., Street name"></div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">City *</label><input type="text" name="city" class="form-control" required></div>
        <div class="form-group"><label class="form-label">State *</label>
          <select name="state" class="form-control" required>
            <option value="">Select</option>
            <?php foreach ($states as $st): ?><option value="<?= h($st) ?>"><?= h($st) ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group"><label class="form-label">Pincode *</label><input type="text" name="pincode" class="form-control" maxlength="6" required placeholder="6-digit pincode"></div>
      <div style="display:flex;gap:10px;margin-top:4px">
        <button type="submit" class="btn btn-primary">Save Address</button>
        <button type="button" onclick="document.getElementById('addAddrModal').style.display='none'" class="btn btn-secondary">Cancel</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
