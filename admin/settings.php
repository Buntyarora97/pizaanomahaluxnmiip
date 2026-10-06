<?php
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../../includes/csrf.php';
$keys = ['site_name','site_tagline','company_email','company_mobile','company_phone1','company_phone2',
'registered_address','office_address','cin','gstin','whatsapp','map_embed_url','map_directions_url',
'guest_checkout','maintenance_mode','smtp_host','smtp_port','smtp_user','smtp_pass','smtp_from_email','smtp_from_name',
'razorpay_key_id','razorpay_key_secret','ga4_id','gtm_id','meta_pixel'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $up = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    foreach ($keys as $k) $up->execute([$k, trim((string)($_POST[$k] ?? ''))]);
    flash('Settings saved.');
    redirect('settings.php');
}
$g = $pdo->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll(PDO::FETCH_KEY_PAIR);
$val = fn($k) => e($g[$k] ?? '');
?>
<div class="admin-top"><h1>Site Settings</h1></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
<div class="acct-card"><h3>Company Information</h3>
  <div class="form-row"><div class="field"><label>Site Name</label><input name="site_name" value="<?= $val('site_name') ?>"></div><div class="field"><label>Tagline</label><input name="site_tagline" value="<?= $val('site_tagline') ?>"></div></div>
  <div class="form-row"><div class="field"><label>Company Email</label><input name="company_email" value="<?= $val('company_email') ?>"></div><div class="field"><label>Mobile</label><input name="company_mobile" value="<?= $val('company_mobile') ?>"></div></div>
  <div class="form-row"><div class="field"><label>Phone 1</label><input name="company_phone1" value="<?= $val('company_phone1') ?>"></div><div class="field"><label>Phone 2</label><input name="company_phone2" value="<?= $val('company_phone2') ?>"></div></div>
  <div class="field"><label>Registered Address</label><textarea name="registered_address"><?= $val('registered_address') ?></textarea></div>
  <div class="field"><label>Office / Correspondence Address</label><textarea name="office_address"><?= $val('office_address') ?></textarea></div>
  <div class="form-row"><div class="field"><label>CIN</label><input name="cin" value="<?= $val('cin') ?>"></div><div class="field"><label>GSTIN (publish only if verified)</label><input name="gstin" value="<?= $val('gstin') ?>"></div></div>
  <div class="form-row"><div class="field"><label>WhatsApp (e.g. 919988888889)</label><input name="whatsapp" value="<?= $val('whatsapp') ?>"></div><div class="field"><label>Google Maps Embed URL</label><input name="map_embed_url" value="<?= $val('map_embed_url') ?>"></div></div>
  <div class="field"><label>Google Maps Directions URL</label><input name="map_directions_url" value="<?= $val('map_directions_url') ?>"></div>
</div>
<div class="acct-card"><h3>Store Behaviour</h3>
  <div class="form-row">
    <div class="field"><label>Guest Checkout</label><select name="guest_checkout"><option value="1" <?= $g['guest_checkout']==='1'?'selected':'' ?>>Enabled</option><option value="0" <?= $g['guest_checkout']==='0'?'selected':'' ?>>Disabled</option></select></div>
    <div class="field"><label>Maintenance Mode</label><select name="maintenance_mode"><option value="0" <?= $g['maintenance_mode']==='0'?'selected':'' ?>>Off</option><option value="1" <?= $g['maintenance_mode']==='1'?'selected':'' ?>>ON</option></select></div>
  </div>
</div>
<div class="acct-card"><h3>Razorpay Payment Gateway</h3>
  <div class="form-row"><div class="field"><label>Key ID (public)</label><input name="razorpay_key_id" value="<?= $val('razorpay_key_id') ?>"></div><div class="field"><label>Key Secret (server only)</label><input name="razorpay_key_secret" value="<?= $val('razorpay_key_secret') ?>"></div></div>
  <p class="muted" style="font-size:.8rem">Webhook URL for Razorpay dashboard: <code><?= SITE_URL ?>/payment/callback.php</code></p>
</div>
<div class="acct-card"><h3>Email / SMTP</h3>
  <div class="form-row"><div class="field"><label>SMTP Host</label><input name="smtp_host" value="<?= $val('smtp_host') ?>" placeholder="mail.yourdomain.com"></div><div class="field"><label>Port</label><input name="smtp_port" value="<?= $val('smtp_port') ?>"></div></div>
  <div class="form-row"><div class="field"><label>SMTP Username</label><input name="smtp_user" value="<?= $val('smtp_user') ?>"></div><div class="field"><label>SMTP Password</label><input type="password" name="smtp_pass" value="<?= $val('smtp_pass') ?>"></div></div>
  <div class="form-row"><div class="field"><label>From Email</label><input name="smtp_from_email" value="<?= $val('smtp_from_email') ?>"></div><div class="field"><label>From Name</label><input name="smtp_from_name" value="<?= $val('smtp_from_name') ?>"></div></div>
</div>
<div class="acct-card"><h3>Analytics (optional)</h3>
  <div class="form-row"><div class="field"><label>GA4 Measurement ID</label><input name="ga4_id" value="<?= $val('ga4_id') ?>" placeholder="G-XXXXXXXXXX"></div><div class="field"><label>GTM ID</label><input name="gtm_id" value="<?= $val('gtm_id') ?>"></div></div>
  <div class="field"><label>Meta Pixel ID</label><input name="meta_pixel" value="<?= $val('meta_pixel') ?>"></div>
</div>
<button class="btn btn-primary" style="margin-bottom:2rem">💾 Save All Settings</button>
</form>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
