/* =============================================================
   ElectroStore — Main JavaScript v2.0
   ============================================================= */
'use strict';

/* ── Toastr config ────────────────────────────────────────────── */
toastr.options = {
  closeButton: true, progressBar: true,
  positionClass: 'toast-top-right',
  timeOut: 3500, extendedTimeOut: 1500,
  preventDuplicates: true,
};

/* ── Dark Mode ────────────────────────────────────────────────── */
const ThemeManager = {
  init() {
    const saved = localStorage.getItem('es_theme') || 'light';
    this.apply(saved);
    document.getElementById('themeToggle')?.addEventListener('click', () => {
      const current = document.getElementById('htmlRoot').getAttribute('data-bs-theme');
      this.apply(current === 'dark' ? 'light' : 'dark');
    });
  },
  apply(theme) {
    document.getElementById('htmlRoot').setAttribute('data-bs-theme', theme);
    localStorage.setItem('es_theme', theme);
    const btn = document.getElementById('themeToggle');
    if (btn) btn.innerHTML = theme === 'dark' ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
  }
};

/* ── Cart ─────────────────────────────────────────────────────── */
const Cart = {
  async add(productId, qty = 1, btn = null) {
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>'; }
    try {
      const res = await fetch(`${BASE_URL}ajax/cart.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add&product_id=${productId}&qty=${qty}&csrf_token=${CSRF_TOKEN}`
      });
      const data = await res.json();
      if (data.success) {
        toastr.success(data.message || 'Added to cart!');
        Cart.updateBadge(data.count);
        Cart.animateBadge();
      } else {
        toastr.warning(data.message || 'Could not add to cart.');
      }
    } catch { toastr.error('Network error. Please try again.'); }
    finally {
      if (btn) { btn.disabled = false; btn.innerHTML = btn.dataset.originalText || '<i class="fas fa-cart-plus me-1"></i>Add to Cart'; }
    }
  },

  async update(productId, qty) {
    const res  = await fetch(`${BASE_URL}ajax/cart.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=update&product_id=${productId}&qty=${qty}&csrf_token=${CSRF_TOKEN}`
    });
    const data = await res.json();
    if (data.success) {
      Cart.refreshTotals(data);
    }
  },

  async remove(productId) {
    const res  = await fetch(`${BASE_URL}ajax/cart.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=remove&product_id=${productId}&csrf_token=${CSRF_TOKEN}`
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById(`cart-row-${productId}`)?.remove();
      Cart.updateBadge(data.count);
      Cart.refreshTotals(data);
      if (data.count === 0) location.reload();
    }
  },

  updateBadge(count) {
    document.querySelectorAll('#cartCount').forEach(el => {
      el.textContent = count > 0 ? count : '';
    });
  },

  animateBadge() {
    const badge = document.getElementById('cartCount');
    if (!badge) return;
    badge.classList.add('animate__animated', 'animate__bounceIn');
    badge.addEventListener('animationend', () => badge.classList.remove('animate__animated', 'animate__bounceIn'), { once: true });
  },

  refreshTotals(data) {
    if (data.subtotal !== undefined) document.getElementById('cartSubtotal') && (document.getElementById('cartSubtotal').textContent = data.subtotal_fmt);
    if (data.total !== undefined)    document.getElementById('cartTotal')    && (document.getElementById('cartTotal').textContent    = data.total_fmt);
  }
};

/* ── Wishlist ─────────────────────────────────────────────────── */
const Wishlist = {
  async toggle(productId, btn) {
    if (!IS_LOGGED_IN) { window.location.href = `${BASE_URL}auth/login.php`; return; }
    const icon = btn.querySelector('i');
    try {
      const res  = await fetch(`${BASE_URL}ajax/wishlist.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=toggle&product_id=${productId}&csrf_token=${CSRF_TOKEN}`
      });
      const data = await res.json();
      if (data.success) {
        if (data.added) {
          btn.classList.add('wishlisted');
          if (icon) icon.className = 'fas fa-heart';
          toastr.success('Added to wishlist!');
        } else {
          btn.classList.remove('wishlisted');
          if (icon) icon.className = 'far fa-heart';
          toastr.info('Removed from wishlist.');
        }
        document.querySelectorAll('.wishlist-count').forEach(el => el.textContent = data.count);
      }
    } catch { toastr.error('Error updating wishlist.'); }
  }
};

/* ── Product Comparison ───────────────────────────────────────── */
const Compare = {
  items: JSON.parse(localStorage.getItem('es_compare') || '[]'),

  toggle(id, name, img) {
    const idx = this.items.findIndex(i => i.id == id);
    if (idx > -1) {
      this.items.splice(idx, 1);
      toastr.info(`${name} removed from comparison.`);
    } else if (this.items.length >= 4) {
      toastr.warning('You can compare up to 4 products at once.');
      return;
    } else {
      this.items.push({ id, name, img });
      toastr.success(`${name} added to comparison!`);
    }
    this.save(); this.render();
  },

  save() { localStorage.setItem('es_compare', JSON.stringify(this.items)); },

  render() {
    const bar   = document.getElementById('compareBar');
    const cont  = document.getElementById('compareItems');
    if (!bar || !cont) return;
    if (this.items.length === 0) { bar.classList.add('d-none'); return; }
    bar.classList.remove('d-none');
    cont.innerHTML = this.items.map(i =>
      `<div class="d-flex align-items-center gap-1 bg-white bg-opacity-10 rounded px-2 py-1">
        <img src="${i.img}" class="compare-item-thumb" alt="${i.name}">
        <small class="text-white">${i.name.substring(0, 20)}</small>
        <button class="btn-close btn-close-white btn-sm" onclick="Compare.toggle(${i.id},'${i.name}','${i.img}')"></button>
       </div>`
    ).join('');
    document.getElementById('compareLink') && (document.getElementById('compareLink').href = `${BASE_URL}compare.php?ids=${this.items.map(i => i.id).join(',')}`);
  },

  clear() { this.items = []; this.save(); this.render(); }
};

/* ── Live Search ──────────────────────────────────────────────── */
const LiveSearch = {
  input:    document.getElementById('searchInput'),
  dropdown: document.getElementById('searchDropdown'),
  timer:    null,

  init() {
    if (!this.input) return;
    this.input.addEventListener('input', () => {
      clearTimeout(this.timer);
      const q = this.input.value.trim();
      if (q.length < 2) { this.hide(); return; }
      this.timer = setTimeout(() => this.search(q), 300);
    });
    document.addEventListener('click', e => {
      if (!this.input.contains(e.target) && !this.dropdown?.contains(e.target)) this.hide();
    });
    this.input.addEventListener('focus', () => {
      if (this.input.value.trim().length >= 2) this.search(this.input.value.trim());
    });
  },

  async search(q) {
    try {
      const res  = await fetch(`${BASE_URL}ajax/search.php?q=${encodeURIComponent(q)}`);
      const data = await res.json();
      this.render(data);
    } catch {}
  },

  render(results) {
    if (!this.dropdown) return;
    if (!results.length) {
      this.dropdown.innerHTML = '<div class="p-3 text-muted small">No products found.</div>';
    } else {
      this.dropdown.innerHTML = results.map(r => `
        <a href="${BASE_URL}product/${r.slug}" class="search-item">
          <img src="${r.primary_image || BASE_URL + 'assets/images/placeholder.jpg'}" alt="${r.product_name}">
          <div class="flex-grow-1 min-w-0">
            <div class="small fw-semibold text-truncate">${r.product_name}</div>
            <small class="text-muted">${r.brand_name} · ${r.category_name}</small>
          </div>
          <div class="fw-bold small text-primary">${r.effective_price_fmt}</div>
        </a>`
      ).join('') + `<a href="${BASE_URL}shop.php?q=${encodeURIComponent(this.input.value)}" class="d-block text-center small p-2 text-primary border-top">See all results →</a>`;
    }
    this.dropdown.classList.remove('d-none');
  },

  hide() { this.dropdown?.classList.add('d-none'); }
};

/* ── Countdown timers ─────────────────────────────────────────── */
function initCountdowns() {
  document.querySelectorAll('.countdown').forEach(el => {
    const end = parseInt(el.dataset.end);
    const tick = () => {
      const diff = Math.floor((end - Date.now()) / 1000);
      if (diff <= 0) { el.textContent = 'Ended'; el.closest('.flash-card')?.classList.add('opacity-50'); return; }
      const h = String(Math.floor(diff / 3600)).padStart(2, '0');
      const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
      const s = String(diff % 60).padStart(2, '0');
      el.textContent = `${h}:${m}:${s}`;
    };
    tick(); setInterval(tick, 1000);
  });
}

/* ── Quantity control ─────────────────────────────────────────── */
function initQtyControls() {
  document.querySelectorAll('.qty-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.closest('.qty-control')?.querySelector('.qty-input');
      if (!input) return;
      const max = parseInt(input.dataset.max || 999);
      let val = parseInt(input.value) || 1;
      btn.classList.contains('qty-minus') ? (val = Math.max(1, val - 1)) : (val = Math.min(max, val + 1));
      input.value = val;
      input.dispatchEvent(new Event('change'));
    });
  });
}

/* ── Notifications ────────────────────────────────────────────── */
const NotifPanel = {
  loaded: false,
  async load() {
    if (this.loaded || !IS_LOGGED_IN) return;
    const res  = await fetch(`${BASE_URL}ajax/notifications.php?action=list`);
    const data = await res.json();
    const list = document.getElementById('notifList');
    if (!list) return;
    if (!data.length) {
      list.innerHTML = '<div class="p-3 text-center text-muted small"><i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>No notifications</div>';
    } else {
      list.innerHTML = data.map(n => `
        <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}" onclick="NotifPanel.markRead(${n.id}, this, '${n.link || ''}')">
          <div class="notif-icon" style="background:${n.color || '#dee2e6'}22;color:${n.color || '#6c757d'}">
            <i class="${n.icon || 'fas fa-bell'}"></i>
          </div>
          <div class="flex-grow-1 min-w-0">
            <div class="small fw-semibold text-truncate">${n.title}</div>
            <div class="x-small text-muted">${n.message || ''}</div>
            <div style="font-size:.7rem" class="text-muted mt-1">${n.time_ago}</div>
          </div>
          ${n.is_read == 0 ? '<div class="rounded-circle bg-primary" style="width:8px;height:8px;flex-shrink:0;margin-top:4px"></div>' : ''}
        </div>`
      ).join('');
    }
    this.loaded = true;
  },

  async markRead(id, el, link) {
    await fetch(`${BASE_URL}ajax/notifications.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=mark_read&id=${id}&csrf_token=${CSRF_TOKEN}`
    });
    el.classList.remove('unread');
    el.querySelector('.bg-primary.rounded-circle')?.remove();
    const badge = document.getElementById('notifBadge');
    if (badge) { const c = Math.max(0, (parseInt(badge.textContent) || 0) - 1); badge.textContent = c > 0 ? c : ''; }
    if (link) window.location.href = link;
  },

  async markAllRead() {
    await fetch(`${BASE_URL}ajax/notifications.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=mark_all_read&csrf_token=${CSRF_TOKEN}`
    });
    document.querySelectorAll('.notif-item').forEach(el => el.classList.remove('unread'));
    const badge = document.getElementById('notifBadge');
    if (badge) badge.textContent = '';
  }
};

/* ── Newsletter ───────────────────────────────────────────────── */
async function handleNewsletter(e) {
  e.preventDefault();
  const form  = document.getElementById('newsletterForm');
  const email = form.querySelector('[name="email"]').value;
  const btn   = form.querySelector('button[type="submit"]');
  btn.disabled = true; btn.textContent = 'Subscribing…';
  try {
    const res  = await fetch(`${BASE_URL}ajax/newsletter.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `email=${encodeURIComponent(email)}&csrf_token=${CSRF_TOKEN}`
    });
    const data = await res.json();
    data.success ? toastr.success(data.message) : toastr.warning(data.message);
  } catch { toastr.error('Error. Please try again.'); }
  finally { btn.disabled = false; btn.textContent = 'Subscribe'; form.reset(); }
}

/* ── Back to top ──────────────────────────────────────────────── */
function initBackToTop() {
  const btn = document.getElementById('backToTop');
  if (!btn) return;
  window.addEventListener('scroll', () => btn.classList.toggle('visible', window.scrollY > 400));
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

/* ── Product gallery (detail page) ───────────────────────────── */
function initGallery() {
  document.querySelectorAll('.thumb-item').forEach(thumb => {
    thumb.addEventListener('click', () => {
      const main = document.getElementById('mainProductImage');
      if (main) main.src = thumb.dataset.full || thumb.querySelector('img')?.src;
      document.querySelectorAll('.thumb-item').forEach(t => t.classList.remove('active'));
      thumb.classList.add('active');
    });
  });
}

/* ── Coupon apply ─────────────────────────────────────────────── */
async function applyCoupon() {
  const code  = document.getElementById('couponInput')?.value?.trim();
  const points = parseInt(document.getElementById('pointsInput')?.value || 0);
  if (!code) { toastr.warning('Please enter a coupon code.'); return; }
  const btn  = document.getElementById('applyCouponBtn');
  btn.disabled = true; btn.textContent = 'Applying…';
  try {
    const res  = await fetch(`${BASE_URL}ajax/cart.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=apply_coupon&coupon=${encodeURIComponent(code)}&points=${points}&csrf_token=${CSRF_TOKEN}`
    });
    const data = await res.json();
    if (data.success) {
      toastr.success(data.coupon_msg || 'Coupon applied!');
      document.getElementById('discountRow') && (document.getElementById('discountRow').classList.remove('d-none'));
      document.getElementById('discountAmt')  && (document.getElementById('discountAmt').textContent  = data.discount_fmt);
      document.getElementById('orderTotal')   && (document.getElementById('orderTotal').textContent   = data.total_fmt);
    } else {
      toastr.warning(data.message || 'Invalid coupon.');
    }
  } catch { toastr.error('Error applying coupon.'); }
  finally { btn.disabled = false; btn.textContent = 'Apply'; }
}

/* ── Currency switcher ────────────────────────────────────────── */
document.getElementById('currencySwitcher')?.addEventListener('change', async function () {
  const res  = await fetch(`${BASE_URL}ajax/currency.php?currency=${this.value}`);
  const data = await res.json();
  if (data.success) location.reload();
});

/* ── Quick view ───────────────────────────────────────────────── */
async function quickView(slug) {
  const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
  document.getElementById('quickViewContent').innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>';
  modal.show();
  const res  = await fetch(`${BASE_URL}ajax/quick_view.php?slug=${encodeURIComponent(slug)}`);
  document.getElementById('quickViewContent').innerHTML = await res.text();
  initGallery(); initQtyControls();
}

/* ── Swiper init ──────────────────────────────────────────────── */
function initSwipers() {
  if (document.querySelector('.hero-swiper')) {
    new Swiper('.hero-swiper', {
      loop: true, autoplay: { delay: 5000, disableOnInteraction: false },
      pagination: { el: '.swiper-pagination', clickable: true },
      navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
      effect: 'fade',
    });
  }
  if (document.querySelector('.cat-swiper')) {
    new Swiper('.cat-swiper', {
      slidesPerView: 'auto', spaceBetween: 12, freeMode: true,
    });
  }
  if (document.querySelector('.related-swiper')) {
    new Swiper('.related-swiper', {
      slidesPerView: 1.5, spaceBetween: 12, breakpoints: {
        576: { slidesPerView: 2.5 }, 768: { slidesPerView: 3.5 }, 992: { slidesPerView: 4 }
      },
    });
  }
}

/* ── Init ─────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  ThemeManager.init();
  LiveSearch.init();
  initCountdowns();
  initQtyControls();
  initBackToTop();
  initGallery();
  initSwipers();
  Compare.render();

  /* Cart buttons */
  document.querySelectorAll('.btn-add-cart').forEach(btn => {
    btn.dataset.originalText = btn.innerHTML;
    btn.addEventListener('click', () => Cart.add(btn.dataset.productId, 1, btn));
  });

  /* Wishlist buttons */
  document.querySelectorAll('.btn-wishlist').forEach(btn => {
    btn.addEventListener('click', () => Wishlist.toggle(btn.dataset.productId, btn));
  });

  /* Compare buttons */
  document.querySelectorAll('.btn-compare').forEach(btn => {
    btn.addEventListener('click', () => Compare.toggle(btn.dataset.productId, btn.dataset.productName, btn.dataset.productImg));
  });

  /* Notification dropdown */
  document.querySelector('[data-bs-toggle="dropdown"] + .notif-dropdown') &&
    document.querySelector('[data-bs-toggle="dropdown"]')?.addEventListener('click', () => NotifPanel.load());

  /* Mark all read */
  document.querySelectorAll('.mark-all-read').forEach(a => a.addEventListener('click', e => { e.preventDefault(); NotifPanel.markAllRead(); }));

  /* Newsletter */
  document.getElementById('newsletterForm')?.addEventListener('submit', handleNewsletter);

  /* Clear compare */
  document.getElementById('clearCompare')?.addEventListener('click', () => Compare.clear());

  /* Inline qty changes on cart page */
  document.querySelectorAll('.qty-input').forEach(input => {
    input.addEventListener('change', () => {
      const productId = input.closest('[data-product-id]')?.dataset.productId;
      if (productId) Cart.update(productId, parseInt(input.value));
    });
  });

  /* Lazy-load product images */
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(entries => entries.forEach(e => {
      if (e.isIntersecting) { e.target.src = e.target.dataset.src; io.unobserve(e.target); }
    }));
    document.querySelectorAll('img[data-src]').forEach(img => io.observe(img));
  }
});
