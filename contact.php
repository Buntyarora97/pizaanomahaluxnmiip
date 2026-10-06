<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pdo->prepare('INSERT INTO contact_messages (name, company, phone, email, subject, message) VALUES (?,?,?,?,?,?)')
        ->execute([trim($_POST['name']), trim($_POST['company'] ?? '') ?: null, trim($_POST['phone']), trim($_POST['email']), trim($_POST['subject']), trim($_POST['message'])]);
    sendMail(setting('company_email'), 'New Contact Enquiry: ' . $_POST['subject'], '<p><b>' . e($_POST['name']) . '</b> (' . e($_POST['email']) . ', ' . e($_POST['phone']) . ')</p><p>' . nl2br(e($_POST['message'])) . '</p>');
    flash('Thank you! Your message has been sent. We will respond shortly.');
}
$pageTitle = 'Contact Us — ' . setting('site_name');
$canonical = SITE_URL . '/contact';
include __DIR__ . '/includes/header.php';
$fl = flash();
?>
<section class="page-hero"><div class="container"><div class="breadcrumb"><a href="index.php">Home</a> / Contact</div><h1>Contact Us</h1><p>We'd love to hear from you — orders, quotes, trade enquiries or support.</p></div></section>
<section class="section"><div class="container">
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:3rem">
    <a class="why-card reveal" style="text-align:center" href="tel:<?= preg_replace('/\s+/','',setting('company_mobile')) ?>"><div class="why-ic" style="margin:0 auto 1rem"><svg class="ic"><use href="#i-phone"/></svg></div><h4>Call Us</h4><p><?= e(setting('company_mobile')) ?><br><?= e(setting('company_phone1')) ?></p></a>
    <a class="why-card reveal" style="text-align:center" href="mailto:<?= e(setting('company_email')) ?>"><div class="why-ic" style="margin:0 auto 1rem"><svg class="ic"><use href="#i-mail"/></svg></div><h4>Email</h4><p><?= e(setting('company_email')) ?></p></a>
    <?php if (setting('whatsapp')): ?><a class="why-card reveal" style="text-align:center" href="https://wa.me/<?= preg_replace('/\D/','',setting('whatsapp')) ?>" target="_blank" rel="noopener"><div class="why-ic" style="margin:0 auto 1rem"><svg class="ic"><use href="#i-wa"/></svg></div><h4>WhatsApp</h4><p>Chat with our team</p></a><?php endif; ?>
    <?php if (setting('map_directions_url')): ?><a class="why-card reveal" style="text-align:center" href="<?= e(setting('map_directions_url')) ?>" target="_blank" rel="noopener"><div class="why-ic" style="margin:0 auto 1rem"><svg class="ic"><use href="#i-globe"/></svg></div><h4>Directions</h4><p><?= e(setting('office_address') ?: setting('registered_address')) ?></p></a><?php endif; ?>
  </div>
  <div class="cart-page">
    <div class="acct-card">
      <h3>Send a Message</h3>
      <?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
      <form method="post"><?= csrf_field() ?>
        <div class="form-row"><div class="field"><label>Name *</label><input name="name" required></div><div class="field"><label>Company</label><input name="company"></div></div>
        <div class="form-row"><div class="field"><label>Phone *</label><input name="phone" required></div><div class="field"><label>Email *</label><input type="email" name="email" required></div></div>
        <div class="field"><label>Subject *</label><input name="subject" required></div>
        <div class="field"><label>Message *</label><textarea name="message" required></textarea></div>
        <button class="btn btn-primary">Send Message</button>
      </form>
    </div>
    <div class="acct-card">
      <h3>Our Details</h3>
      <p style="line-height:2;color:var(--muted);font-size:.93rem">
        <b style="color:var(--navy)">Mahaluxmi Trading House Pvt. Ltd.</b><br>
        <b>Registered Address:</b><br><?= nl2br(e(setting('registered_address'))) ?><br><br>
        <b>Office / Correspondence:</b><br><?= nl2br(e(setting('office_address'))) ?><br><br>
        <b>Mobile:</b> <?= e(setting('company_mobile')) ?><br>
        <b>Phone:</b> <?= e(setting('company_phone1')) ?> · <?= e(setting('company_phone2')) ?><br>
        <b>Email:</b> <?= e(setting('company_email')) ?><br>
        <b>CIN:</b> <?= e(setting('cin')) ?>
      </p>
      <?php if (setting('map_embed_url')): ?><div style="margin-top:1rem;border-radius:12px;overflow:hidden"><iframe src="<?= e(setting('map_embed_url')) ?>" width="100%" height="220" style="border:0" loading="lazy"></iframe></div><?php endif; ?>
    </div>
  </div>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
