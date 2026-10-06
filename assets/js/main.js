/* Mahaluxmi Trading House - frontend JS */
(function () {
  'use strict';
  const $ = (s, c) => (c || document).querySelector(s);
  const $$ = (s, c) => [...(c || document).querySelectorAll(s)];
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Toast ---------- */
  window.toast = function (msg) {
    const t = $('#toast'); if (!t) return;
    t.textContent = msg; t.classList.add('show');
    clearTimeout(t._h); t._h = setTimeout(() => t.classList.remove('show'), 2600);
  };

  /* ---------- Sticky header ---------- */
  const header = $('#header');
  addEventListener('scroll', () => header && header.classList.toggle('scrolled', scrollY > 8), { passive: true });

  /* ---------- Mobile nav ---------- */
  const burger = $('#burger'), mainnav = $('#mainnav');
  burger && burger.addEventListener('click', () => mainnav.classList.toggle('open'));

  /* ---------- AJAX search ---------- */
  const sIn = $('#ajaxSearch'), sRes = $('#searchResults');
  let sT;
  if (sIn) sIn.addEventListener('input', () => {
    clearTimeout(sT);
    const q = sIn.value.trim();
    if (q.length < 2) { sRes.classList.remove('open'); return; }
    sT = setTimeout(async () => {
      try {
        const r = await fetch(SITE_URL + '/ajax/search.php?q=' + encodeURIComponent(q));
        const d = await r.json();
        let h = '';
        (d.results || []).forEach(p => {
          h += `<a class="sr-item" href="${SITE_URL}/product.php?slug=${p.slug}">
            <img src="${p.image}" alt=""><div><div class="p">${p.name}</div><div class="c">${p.category} · ${p.price}</div></div></a>`;
        });
        h += `<a class="sr-all" href="${SITE_URL}/search.php?q=${encodeURIComponent(q)}">View All Results →</a>`;
        sRes.innerHTML = h; sRes.classList.add('open');
      } catch (e) {}
    }, 250);
  });
  document.addEventListener('click', e => {
    if (sRes && !e.target.closest('.search-wrap')) sRes.classList.remove('open');
    const ms = $('#mSearch'); if (ms && e.target.closest('#mSearch')) { e.preventDefault(); sIn && sIn.focus(); }
  });

  /* ---------- Cart drawer ---------- */
  const drawer = $('#cartDrawer'), overlay = $('#drawerOverlay'), body = $('#cartDrawerBody');
  function openCart() { drawer.classList.add('open'); overlay.classList.add('open'); loadDrawer(); }
  function closeCart() { drawer.classList.remove('open'); overlay.classList.remove('open'); }
  $('#cartOpen') && $('#cartOpen').addEventListener('click', openCart);
  $('#mCart') && $('#mCart').addEventListener('click', e => { e.preventDefault(); openCart(); });
  $('#cartClose') && $('#cartClose').addEventListener('click', closeCart);
  overlay && overlay.addEventListener('click', closeCart);

  async function loadDrawer() {
    if (!body) return;
    body.innerHTML = '<div class="skeleton" style="height:80px;margin-bottom:10px"></div><div class="skeleton" style="height:80px"></div>';
    try {
      const r = await fetch(SITE_URL + '/ajax/cart-get.php');
      const d = await r.json();
      if (!d.items.length) { body.innerHTML = '<p class="muted" style="text-align:center;padding:2rem 0">Your cart is empty.</p>'; return; }
      let h = '';
      d.items.forEach(i => {
        h += `<div class="cd-item">
          <img src="${i.image}" alt="">
          <div><div class="n">${i.name}</div><div style="font-size:.8rem;color:var(--muted)">Qty ${i.quantity} × ${i.price}</div>
          <button class="rm" data-id="${i.id}">Remove</button></div>
          <div class="p">${i.line_total}</div></div>`;
      });
      h += `<div style="display:flex;justify-content:space-between;font-weight:800;padding-top:1rem;color:var(--navy)"><span>Subtotal</span><span>${d.subtotal}</span></div>`;
      body.innerHTML = h;
      $$('.rm', body).forEach(b => b.addEventListener('click', async () => {
        await fetch(SITE_URL + '/ajax/cart-update.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN }, body: JSON.stringify({ id: b.dataset.id, action: 'remove' }) });
        refreshCart(); loadDrawer();
      }));
    } catch (e) { body.innerHTML = '<p class="muted">Could not load cart.</p>'; }
  }

  window.refreshCart = async function () {
    try {
      const r = await fetch(SITE_URL + '/ajax/cart-count.php');
      const d = await r.json();
      const c = $('#cartCount'); if (c) c.textContent = d.count;
    } catch (e) {}
  };

  /* Add to cart (event delegation) */
  document.addEventListener('click', async e => {
    const btn = e.target.closest('.js-add-cart');
    if (!btn) return;
    e.preventDefault();
    btn.disabled = true;
    try {
      const r = await fetch(SITE_URL + '/ajax/cart-add.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
        body: JSON.stringify({ product_id: btn.dataset.id, variant_id: btn.dataset.variant || null, quantity: (() => { const q = $('#qtyInput'); return q ? +q.value : 1; })() })
      });
      const d = await r.json();
      if (d.ok) { toast('Added to cart ✓'); refreshCart(); if (btn.dataset.buy) location.href = SITE_URL + '/checkout.php'; else openCart(); }
      else toast(d.msg || 'Could not add to cart');
    } catch (err) { toast('Network error'); }
    btn.disabled = false;
  });

  /* Wishlist */
  document.addEventListener('click', async e => {
    const b = e.target.closest('.js-wish');
    if (!b) return;
    e.preventDefault();
    const r = await fetch(SITE_URL + '/ajax/wishlist.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN }, body: JSON.stringify({ product_id: b.dataset.id }) });
    const d = await r.json();
    toast(d.msg); if (d.ok) b.classList.toggle('active');
  });

  /* ---------- Hero 3D slider + tilt ---------- */
  const slides = $$('.hero-slide');
  if (slides.length) {
    let idx = 0, timer;
    const dotsWrap = $('.hero-dots');
    slides.forEach((_, i) => {
      const b = document.createElement('button');
      b.addEventListener('click', () => go(i));
      dotsWrap && dotsWrap.appendChild(b);
    });
    const dots = $$('.hero-dots button');
    function go(i) {
      slides[idx].classList.remove('active'); dots[idx] && dots[idx].classList.remove('active');
      idx = i % slides.length;
      slides[idx].classList.add('active'); dots[idx] && dots[idx].classList.add('active');
    }
    go(0);
    if (!reduced) timer = setInterval(() => go(idx + 1), 4200);
    const hero = $('.hero');
    if (hero && !reduced && matchMedia('(pointer:fine)').matches) {
      const g = $('.globe'), stage = $('.slide-stage');
      hero.addEventListener('mousemove', e => {
        const r = hero.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
        if (g) g.style.transform = `rotateY(${x * 14}deg) rotateX(${-y * 10}deg)`;
        if (stage) stage.style.transform = `translate(${x * 18}px,${y * 14}px)`;
      });
    }
  }

  /* ---------- Scroll-scrubbed cinematic hero ---------- */
  const cinematicHero = $('#cinematicHero'), scrollVideo = $('#scrollVideo');
  if (cinematicHero && scrollVideo) {
    let framePending = false;
    const clamp = (n, min, max) => Math.max(min, Math.min(max, n));
    const syncScrollVideo = () => {
      framePending = false;
      if (scrollVideo.readyState < HTMLMediaElement.HAVE_METADATA ||
          !Number.isFinite(scrollVideo.duration) || scrollVideo.duration <= 0) return;

      // Keep playback paused: scroll position is the only source of time, so
      // reversing the page scroll reverses the film instead of racing autoplay.
      if (!scrollVideo.paused) scrollVideo.pause();
      const rect = cinematicHero.getBoundingClientRect();
      const travel = Math.max(1, cinematicHero.offsetHeight - innerHeight);
      const progress = clamp(-rect.top / travel, 0, 1);
      const nextTime = progress * scrollVideo.duration;
      if (Math.abs(scrollVideo.currentTime - nextTime) > .01) {
        try {
          scrollVideo.currentTime = nextTime;
        } catch (error) {
          // Metadata may be available before the browser has a seekable frame.
          // The next scroll/resize/media event will retry the seek.
        }
      }
      cinematicHero.style.setProperty('--film-progress', progress.toFixed(3));
    };
    const requestVideoSync = () => {
      if (framePending) return;
      framePending = true;
      requestAnimationFrame(syncScrollVideo);
    };
    scrollVideo.pause();
    scrollVideo.addEventListener('loadedmetadata', requestVideoSync);
    scrollVideo.addEventListener('loadeddata', () => {
      scrollVideo.removeAttribute('poster');
      requestVideoSync();
    });
    scrollVideo.addEventListener('seeked', requestVideoSync);
    scrollVideo.load();
    addEventListener('scroll', requestVideoSync, { passive: true });
    addEventListener('resize', requestVideoSync, { passive: true });
    requestVideoSync();
  }

  /* ---------- Card tilt (desktop, fine pointer) ---------- */
  if (!reduced && matchMedia('(pointer:fine)').matches) {
    $$('.tilt').forEach(card => {
      card.addEventListener('mousemove', e => {
        const r = card.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
        card.style.transform = `perspective(800px) rotateY(${x * 8}deg) rotateX(${-y * 8}deg) translateY(-4px)`;
      });
      card.addEventListener('mouseleave', () => card.style.transform = '');
    });
  }

  /* ---------- Scroll reveal ---------- */
  const io = 'IntersectionObserver' in window ? new IntersectionObserver(es => es.forEach(en => {
    if (en.isIntersecting) { en.target.classList.add('vis'); io.unobserve(en.target); }
  }), { threshold: .12 }) : null;
  $$('.reveal').forEach(el => io ? io.observe(el) : el.classList.add('vis'));

  /* ---------- PDP gallery ---------- */
  const mainImg = $('.pdp-main img');
  $$('.pdp-thumbs img').forEach(t => t.addEventListener('click', () => {
    $$('.pdp-thumbs img').forEach(x => x.classList.remove('active'));
    t.classList.add('active');
    if (mainImg) { mainImg.style.opacity = 0; setTimeout(() => { mainImg.src = t.dataset.full || t.src; mainImg.style.opacity = 1; }, 160); }
  }));
  $$('.qty').forEach(q => {
    const inp = $('input', q);
    $('.plus', q) && $('.plus', q).addEventListener('click', () => inp.value = Math.min(+(inp.value || 1) + 1, 99));
    $('.minus', q) && $('.minus', q).addEventListener('click', () => inp.value = Math.max(+(inp.value || 1) - 1, 1));
  });

  /* ---------- Tabs ---------- */
  $$('.tab-btns button').forEach(b => b.addEventListener('click', () => {
    const wrap = b.closest('.pdp-tabs');
    $$('.tab-btns button', wrap).forEach(x => x.classList.remove('active'));
    $$('.tab-pane', wrap).forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    $('#' + b.dataset.tab).classList.add('active');
  }));

  /* ---------- Mobile filters ---------- */
  const ft = $('.filter-toggle'), fl = $('.filters');
  ft && ft.addEventListener('click', () => fl.classList.add('open'));
  document.addEventListener('click', e => { if (fl && fl.classList.contains('open') && !e.target.closest('.filters') && !e.target.closest('.filter-toggle')) fl.classList.remove('open'); });

  /* ---------- Shop AJAX filters ---------- */
  const shopGrid = $('#shopGrid');
  if (shopGrid) {
    let fT;
    const apply = () => {
      clearTimeout(fT);
      fT = setTimeout(async () => {
        const fd = new FormData($('#filterForm'));
        fd.append('sort', $('#sortSel') ? $('#sortSel').value : '');
        shopGrid.innerHTML = '<div class="skeleton" style="height:280px;grid-column:1/-1"></div><div class="skeleton" style="height:280px"></div><div class="skeleton" style="height:280px"></div><div class="skeleton" style="height:280px"></div>';
        try {
          const r = await fetch(SITE_URL + '/ajax/filters.php', { method: 'POST', body: fd });
          shopGrid.innerHTML = await r.text();
        } catch (e) {}
      }, 300);
    };
    $('#filterForm') && $('#filterForm').addEventListener('change', apply);
    $('#sortSel') && $('#sortSel').addEventListener('change', apply);
  }

  /* ---------- Compare ---------- */
  window.addToCompare = function (id) {
    let c = JSON.parse(localStorage.getItem('mth_compare') || '[]');
    if (c.includes(+id)) c = c.filter(x => x !== +id);
    else { if (c.length >= 4) return toast('Maximum 4 products to compare'); c.push(+id); }
    localStorage.setItem('mth_compare', JSON.stringify(c));
    toast(c.length + ' product(s) in compare');
  };
  document.addEventListener('click', e => {
    const b = e.target.closest('.js-compare');
    if (b) { e.preventDefault(); addToCompare(b.dataset.id); }
  });

  /* ---------- Newsletter ---------- */
  const nl = $('#nlForm');
  nl && nl.addEventListener('submit', async e => {
    e.preventDefault();
    const r = await fetch(SITE_URL + '/ajax/newsletter.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN }, body: JSON.stringify({ email: $('#nlEmail').value }) });
    const d = await r.json(); toast(d.msg); if (d.ok) nl.reset();
  });

  /* ---------- Count-up stats ---------- */
  $$('.count').forEach(el => {
    const io2 = new IntersectionObserver(es => es.forEach(en => {
      if (!en.isIntersecting) return; io2.unobserve(el);
      const end = +el.dataset.count, t0 = performance.now();
      (function tick(t) {
        const p = Math.min((t - t0) / 1400, 1);
        el.textContent = Math.floor(end * (1 - Math.pow(1 - p, 3))).toLocaleString('en-IN');
        if (p < 1) requestAnimationFrame(tick);
      })(t0);
    }));
    io2.observe(el);
  });
})();
