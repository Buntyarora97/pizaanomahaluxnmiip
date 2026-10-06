<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Compare Products — ' . setting('site_name');
include __DIR__ . '/includes/header.php';
?>
<section class="section"><div class="container">
  <div class="sec-head"><span class="sec-eyebrow">Side by Side</span><h2>Product Comparison</h2><p>Add 2–4 products using the compare button on any product card.</p></div>
  <div id="cmpWrap" class="compare-wrap"><p class="muted" style="text-align:center">Loading comparison...</p></div>
</div></section>
<script>
(async function(){
  const ids = JSON.parse(localStorage.getItem('mth_compare')||'[]');
  const wrap = document.getElementById('cmpWrap');
  if (!ids.length) { wrap.innerHTML = '<p class="muted" style="text-align:center;padding:2rem 0">No products added yet. Use the compare icon on product cards.</p>'; return; }
  const r = await fetch(SITE_URL + '/ajax/compare.php?ids=' + ids.join(','));
  wrap.innerHTML = await r.text();
})();
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
