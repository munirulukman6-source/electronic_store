/* =============================================================
   ElectroStore — Admin JavaScript v2.0
   ============================================================= */
'use strict';

/* ── Toastr config ────────────────────────────────────────────── */
if (typeof toastr !== 'undefined') {
  toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 3500 };
}

/* ── Dark mode ────────────────────────────────────────────────── */
const AdminTheme = {
  init() {
    const saved = localStorage.getItem('es_admin_theme') || 'light';
    document.getElementById('htmlRoot')?.setAttribute('data-bs-theme', saved);
    const btn = document.getElementById('themeToggle');
    if (btn) btn.innerHTML = saved === 'dark' ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
    btn?.addEventListener('click', () => {
      const current = document.getElementById('htmlRoot').getAttribute('data-bs-theme');
      const next    = current === 'dark' ? 'light' : 'dark';
      document.getElementById('htmlRoot').setAttribute('data-bs-theme', next);
      localStorage.setItem('es_admin_theme', next);
      btn.innerHTML = next === 'dark' ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
    });
  }
};

/* ── Sidebar toggle ───────────────────────────────────────────── */
const Sidebar = {
  init() {
    const sidebar  = document.getElementById('adminSidebar');
    const main     = document.getElementById('adminMain');
    const toggleBtns = document.querySelectorAll('#sidebarToggle, #sidebarToggleBtn');
    const isMobile = () => window.innerWidth < 992;

    toggleBtns.forEach(btn => btn?.addEventListener('click', () => {
      if (isMobile()) {
        sidebar?.classList.toggle('mobile-open');
        this.toggleBackdrop(sidebar?.classList.contains('mobile-open'));
      } else {
        sidebar?.classList.toggle('collapsed');
        main?.classList.toggle('expanded');
        localStorage.setItem('es_sidebar_collapsed', sidebar?.classList.contains('collapsed'));
      }
    }));

    // Restore state
    if (!isMobile() && localStorage.getItem('es_sidebar_collapsed') === 'true') {
      sidebar?.classList.add('collapsed');
      main?.classList.add('expanded');
    }

    // Submenu accordions
    document.querySelectorAll('.submenu-toggle').forEach(a => {
      a.addEventListener('click', e => {
        e.preventDefault();
        const li = a.closest('.has-submenu');
        const isOpen = li.classList.contains('open');
        document.querySelectorAll('.has-submenu.open').forEach(el => el.classList.remove('open'));
        if (!isOpen) li.classList.add('open');
      });
    });
  },

  toggleBackdrop(show) {
    let bd = document.getElementById('sidebarBackdrop');
    if (!bd) {
      bd = document.createElement('div');
      bd.id = 'sidebarBackdrop';
      bd.className = 'overlay-backdrop';
      bd.addEventListener('click', () => {
        document.getElementById('adminSidebar')?.classList.remove('mobile-open');
        this.toggleBackdrop(false);
      });
      document.body.appendChild(bd);
    }
    bd.classList.toggle('active', show);
  }
};

/* ── DataTables init ──────────────────────────────────────────── */
function initDataTables(selector = '.data-table', options = {}) {
  document.querySelectorAll(selector).forEach(table => {
    if ($.fn.DataTable.isDataTable(table)) return;
    $(table).DataTable({
      responsive: true,
      pageLength: 25,
      language: { search: '', searchPlaceholder: 'Search…', lengthMenu: 'Show _MENU_' },
      columnDefs: [{ targets: 'no-sort', orderable: false }],
      ...options
    });
  });
}

/* ── Image upload preview ─────────────────────────────────────── */
function initImageUpload() {
  document.querySelectorAll('.upload-zone').forEach(zone => {
    const input   = zone.querySelector('input[type="file"]');
    const preview = zone.closest('form')?.querySelector('.upload-preview') || zone.nextElementSibling;

    zone.addEventListener('click', () => input?.click());
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', e => {
      e.preventDefault(); zone.classList.remove('drag-over');
      handleFiles(e.dataTransfer.files, preview);
    });

    input?.addEventListener('change', () => handleFiles(input.files, preview));
  });

  function handleFiles(files, preview) {
    if (!preview) return;
    Array.from(files).forEach(file => {
      if (!file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = e => {
        const thumb = document.createElement('div');
        thumb.className = 'upload-thumb';
        thumb.innerHTML = `<img src="${e.target.result}" alt=""><div class="remove-img" onclick="this.closest('.upload-thumb').remove()"><i class="fas fa-times"></i></div>`;
        preview.appendChild(thumb);
      };
      reader.readAsDataURL(file);
    });
  }
}

/* ── Spec builder ─────────────────────────────────────────────── */
function initSpecBuilder() {
  const container = document.getElementById('specContainer');
  if (!container) return;

  document.getElementById('addSpecBtn')?.addEventListener('click', () => {
    const row = document.createElement('div');
    row.className = 'spec-row';
    row.innerHTML = `
      <input type="text" class="form-control" name="spec_key[]" placeholder="e.g. Display">
      <input type="text" class="form-control" name="spec_val[]" placeholder="e.g. 6.7 inch AMOLED">
      <button type="button" class="btn btn-outline-danger btn-sm remove-spec"><i class="fas fa-trash"></i></button>`;
    container.appendChild(row);
    row.querySelector('.remove-spec').addEventListener('click', () => row.remove());
  });

  container.querySelectorAll('.remove-spec').forEach(btn => btn.addEventListener('click', () => btn.closest('.spec-row').remove()));
}

/* ── Generic AJAX action (delete, toggle status, etc.) ──────────  */
async function adminAction(action, id, confirmMsg = 'Are you sure?') {
  const result = await Swal.fire({
    title: 'Confirm',
    text: confirmMsg,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    confirmButtonText: 'Yes, proceed',
  });
  if (!result.isConfirmed) return;

  try {
    const res  = await fetch(`${BASE_URL}ajax/admin_action.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=${action}&id=${id}&csrf_token=${CSRF_TOKEN}`
    });
    const data = await res.json();
    if (data.success) { toastr.success(data.message); setTimeout(() => location.reload(), 900); }
    else              { toastr.error(data.message); }
  } catch { toastr.error('Request failed.'); }
}

/* ── Order status update ──────────────────────────────────────── */
async function updateOrderStatus(orderId, status) {
  const note = await Swal.fire({
    title: 'Update Order Status',
    input: 'textarea',
    inputLabel: 'Optional note:',
    inputPlaceholder: 'Add a tracking note…',
    showCancelButton: true,
    confirmButtonText: 'Update',
  });
  if (note.isDismissed) return;

  const res  = await fetch(`${BASE_URL}ajax/admin_action.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=update_order_status&id=${orderId}&status=${status}&note=${encodeURIComponent(note.value || '')}&csrf_token=${CSRF_TOKEN}`
  });
  const data = await res.json();
  data.success ? (toastr.success(data.message), setTimeout(() => location.reload(), 900)) : toastr.error(data.message);
}

/* ── Inventory stock-in inline ────────────────────────────────── */
async function quickStockIn(productId) {
  const { value } = await Swal.fire({
    title: 'Quick Stock In',
    html: `
      <input id="swal-qty"  type="number" class="swal2-input" placeholder="Quantity" min="1">
      <input id="swal-cost" type="number" class="swal2-input" placeholder="Unit Cost (optional)" step="0.01">
      <input id="swal-ref"  type="text"   class="swal2-input" placeholder="Reference No (optional)">`,
    showCancelButton: true,
    confirmButtonText: 'Add Stock',
    preConfirm: () => ({
      qty:  document.getElementById('swal-qty').value,
      cost: document.getElementById('swal-cost').value,
      ref:  document.getElementById('swal-ref').value,
    })
  });
  if (!value) return;
  const res  = await fetch(`${BASE_URL}ajax/admin_action.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=stock_in&product_id=${productId}&qty=${value.qty}&cost=${value.cost}&ref=${encodeURIComponent(value.ref)}&csrf_token=${CSRF_TOKEN}`
  });
  const data = await res.json();
  data.success ? (toastr.success(data.message), setTimeout(() => location.reload(), 900)) : toastr.error(data.message);
}

/* ── Report export ────────────────────────────────────────────── */
function exportReport(type, from, to) {
  window.open(`${BASE_URL}admin/reports/export.php?type=${type}&from=${from}&to=${to}&csrf_token=${CSRF_TOKEN}`, '_blank');
}

/* ── Notification load for admin ─────────────────────────────── */
const AdminNotif = {
  loaded: false,
  async load() {
    if (this.loaded) return;
    try {
      const res  = await fetch(`${BASE_URL}ajax/notifications.php?action=list`);
      const data = await res.json();
      const list = document.getElementById('notifList');
      if (!list) return;
      if (!data.length) { list.innerHTML = '<div class="p-3 text-center text-muted small"><i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>All clear!</div>'; }
      else {
        list.innerHTML = data.map(n => `
          <div class="d-flex gap-2 p-2 border-bottom ${n.is_read == 0 ? 'bg-primary bg-opacity-5' : ''}" style="cursor:pointer" onclick="AdminNotif.markRead(${n.id}, this, '${n.link || ''}')">
            <div style="width:8px;height:8px;border-radius:50%;background:${n.is_read == 0 ? '#0d6efd' : 'transparent'};flex-shrink:0;margin-top:6px"></div>
            <div class="flex-grow-1 min-w-0">
              <div class="small fw-semibold">${n.title}</div>
              <div style="font-size:.75rem" class="text-muted text-truncate">${n.message || ''}</div>
              <div style="font-size:.7rem" class="text-muted mt-1">${n.time_ago}</div>
            </div>
          </div>`
        ).join('');
      }
      this.loaded = true;
    } catch {}
  },
  async markRead(id, el, link) {
    await fetch(`${BASE_URL}ajax/notifications.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=mark_read&id=${id}&csrf_token=${CSRF_TOKEN}`
    });
    el.classList.remove('bg-primary', 'bg-opacity-5');
    el.querySelector('div[style*="background:#0d6efd"]') && (el.querySelector('div[style*="background:#0d6efd"]').style.background = 'transparent');
    const badge = document.getElementById('notifBadge');
    if (badge) { const c = Math.max(0, parseInt(badge.textContent) - 1); badge.textContent = c > 0 ? c : ''; if (c === 0) badge.style.display = 'none'; }
    if (link && link !== '') window.location.href = link;
  },
  async markAllRead() {
    await fetch(`${BASE_URL}ajax/notifications.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=mark_all_read&csrf_token=${CSRF_TOKEN}`
    });
    document.getElementById('notifBadge') && (document.getElementById('notifBadge').textContent = '');
    this.loaded = false; this.load();
  }
};

/* ── Init ─────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  AdminTheme.init();
  Sidebar.init();
  initDataTables();
  initImageUpload();
  initSpecBuilder();

  /* Notification dropdown */
  document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(btn => {
    if (btn.nextElementSibling?.querySelector('#notifList')) {
      btn.addEventListener('click', () => AdminNotif.load());
    }
  });

  /* Mark all read */
  document.querySelectorAll('.mark-all-read').forEach(a => a.addEventListener('click', e => { e.preventDefault(); AdminNotif.markAllRead(); }));

  /* Confirm delete */
  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', async e => {
      e.preventDefault();
      const result = await Swal.fire({
        title: btn.dataset.confirmTitle || 'Are you sure?',
        text:  btn.dataset.confirm,
        icon:  'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: btn.dataset.confirmBtn || 'Yes, delete',
      });
      if (result.isConfirmed) window.location.href = btn.href || btn.dataset.href;
    });
  });

  /* Auto-dismiss alerts */
  setTimeout(() => { document.querySelectorAll('.alert:not(.alert-permanent)').forEach(a => { const bs = bootstrap.Alert.getOrCreateInstance(a); bs.close(); }); }, 5000);

  /* Slug auto-gen from product name */
  document.getElementById('productName')?.addEventListener('input', function () {
    const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    const slugInput = document.getElementById('productSlug');
    if (slugInput && !slugInput.dataset.manual) slugInput.value = slug;
  });
  document.getElementById('productSlug')?.addEventListener('input', function () { this.dataset.manual = true; });

  /* Select2 */
  if (typeof $ !== 'undefined' && $.fn.select2) {
    $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
  }
});
