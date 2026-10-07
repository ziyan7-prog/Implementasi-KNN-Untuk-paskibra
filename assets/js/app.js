/**
 * SiPaskibraka — Global JavaScript
 */

/* ── Sidebar Toggle ── */
const sidebar        = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebar-overlay');
const btnMenu        = document.getElementById('btn-menu');

const SIDEBAR_KEY = 'sipaskibraka_sidebar_collapsed';

// Pindah class dari <html> ke <body> setelah DOM siap, lalu aktifkan transisi
(function initSidebar() {
  const isCollapsed = localStorage.getItem(SIDEBAR_KEY) === '1';
  if (isCollapsed && window.innerWidth >= 1024) {
    document.body.classList.add('sidebar-collapsed');
  }
  // Hapus pre-collapsed dari html dan aktifkan transisi normal
  requestAnimationFrame(() => {
    document.documentElement.classList.remove('sidebar-pre-collapsed');
  });
})();

function isMobile() { return window.innerWidth < 1024; }

function openSidebar()  { sidebar?.classList.add('open'); }
function closeSidebar() { sidebar?.classList.remove('open'); }

function toggleSidebar() {
  if (isMobile()) {
    sidebar?.classList.contains('open') ? closeSidebar() : openSidebar();
  } else {
    const collapsed = document.body.classList.toggle('sidebar-collapsed');
    localStorage.setItem(SIDEBAR_KEY, collapsed ? '1' : '0');
  }
}

btnMenu?.addEventListener('click', toggleSidebar);
sidebarOverlay?.addEventListener('click', closeSidebar);

window.addEventListener('resize', () => {
  if (isMobile()) closeSidebar();
});

/* ── Tab System ── */
function initTabs(containerSelector) {
  const container = document.querySelector(containerSelector);
  if (!container) return;
  const btns   = container.querySelectorAll('.tab-btn');
  const panels = container.querySelectorAll('.tab-panel');
  btns.forEach(btn => {
    btn.addEventListener('click', () => {
      btns.forEach(b => b.classList.remove('active'));
      panels.forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      const target = document.getElementById(btn.dataset.tab);
      if (target) target.classList.add('active');
      // Lazy init DataTable jika ada
      if (btn.dataset.dt) initDataTable(btn.dataset.dt);
    });
  });
  // Aktifkan tab pertama
  if (btns.length) btns[0].click();
}

/* ── Modal System ── */
function openModal(id)  {
  const m = document.getElementById(id);
  m?.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  const m = document.getElementById(id);
  m?.classList.remove('open');
  document.body.style.overflow = '';
}

// Tutup modal saat klik backdrop
document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
  backdrop.addEventListener('click', e => {
    if (e.target === backdrop) closeModal(backdrop.id);
  });
});

// Tutup dengan Escape
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-backdrop.open').forEach(m => closeModal(m.id));
  }
});

/* ── DataTable Helper ── */
const dtInited = {};
function initDataTable(tableId, opts = {}) {
  if (dtInited[tableId]) return;
  const el = document.getElementById(tableId);
  if (!el || typeof $.fn?.DataTable === 'undefined') return;
  $(el).DataTable({
    language: { url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json' },
    pageLength: 15,
    responsive: true,
    ...opts,
  });
  dtInited[tableId] = true;
}

/* ── Helper: icon circle ── */
function _swalIcon(icon, bg, color) {
  return `<div style="width:56px;height:56px;border-radius:50%;background:${bg};display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 16px ${bg}88;">
    <i class="${icon}" style="font-size:22px;color:${color};"></i>
  </div>`;
}

/* ── Confirm Delete ── */
function confirmDelete(message, formId) {
  Swal.fire({
    html: `
      <div style="text-align:center;padding:6px 0 2px;">
        ${_swalIcon('fas fa-trash-alt','#fef2f2','#dc2626')}
        <h3 style="font-size:18px;font-weight:800;color:#1a0a0a;margin:0 0 8px;font-family:'Plus Jakarta Sans',sans-serif;">Hapus Data?</h3>
        <p style="font-size:13.5px;color:#9b8b8b;margin:0;line-height:1.6;">${message}</p>
      </div>`,
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-trash-alt"></i>&nbsp;Ya, Hapus',
    cancelButtonText:  '<i class="fas fa-times"></i>&nbsp;Batal',
    confirmButtonColor: '#dc2626',
    cancelButtonColor:  '#f3f4f6',
    width: 360, padding: '28px 28px 24px', borderRadius: '20px',
    backdrop: 'rgba(15,10,10,0.55)',
    customClass: { popup:'swal-popup-custom', cancelButton:'swal-cancel-grey' },
  }).then(r => { if (r.isConfirmed) document.getElementById(formId)?.submit(); });
}

/* ── Confirm Hapus Nilai ── */
function hapusNilai(id, nama) {
  Swal.fire({
    html: `
      <div style="text-align:center;padding:6px 0 2px;">
        ${_swalIcon('fas fa-clipboard','#fef2f2','#dc2626')}
        <h3 style="font-size:18px;font-weight:800;color:#1a0a0a;margin:0 0 8px;font-family:'Plus Jakarta Sans',sans-serif;">Hapus Nilai?</h3>
        <p style="font-size:13.5px;color:#9b8b8b;margin:0 0 4px;line-height:1.6;">Semua nilai <strong style="color:#1a0a0a;">${nama}</strong> akan dihapus.</p>
        <p style="font-size:12px;color:#ef4444;margin:0;">Tindakan ini tidak dapat dibatalkan.</p>
      </div>`,
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-trash-alt"></i>&nbsp;Ya, Hapus Nilai',
    cancelButtonText:  '<i class="fas fa-times"></i>&nbsp;Batal',
    confirmButtonColor: '#dc2626', cancelButtonColor: '#f3f4f6',
    width: 360, padding: '28px 28px 24px', borderRadius: '20px',
    backdrop: 'rgba(15,10,10,0.55)',
    customClass: { popup:'swal-popup-custom', cancelButton:'swal-cancel-grey' },
  }).then(r => {
    if (r.isConfirmed) {
      document.getElementById('hapusNilaiId').value = id;
      document.getElementById('formHapusNilai').submit();
    }
  });
}

/* ── Confirm Reset ── */
function confirmReset(message, formId) {
  Swal.fire({
    html: `
      <div style="text-align:center;padding:6px 0 2px;">
        ${_swalIcon('fas fa-undo-alt','#fff7ed','#f59e0b')}
        <h3 style="font-size:18px;font-weight:800;color:#1a0a0a;margin:0 0 8px;font-family:'Plus Jakarta Sans',sans-serif;">Reset Data?</h3>
        <p style="font-size:13.5px;color:#9b8b8b;margin:0;line-height:1.6;">${message}</p>
      </div>`,
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-undo-alt"></i>&nbsp;Ya, Reset',
    cancelButtonText:  '<i class="fas fa-times"></i>&nbsp;Batal',
    confirmButtonColor: '#f59e0b', cancelButtonColor: '#f3f4f6',
    width: 360, padding: '28px 28px 24px', borderRadius: '20px',
    backdrop: 'rgba(15,10,10,0.55)',
    customClass: { popup:'swal-popup-custom', cancelButton:'swal-cancel-grey' },
  }).then(r => { if (r.isConfirmed) document.getElementById(formId)?.submit(); });
}

/* ── Confirm Generic ── */
function confirmAction({ title, message, icon='fas fa-question-circle', iconBg='#eff6ff', iconColor='#3b82f6', confirmText='<i class="fas fa-check"></i>&nbsp;Ya', confirmColor='#3b82f6', formId=null, onConfirm=null }) {
  Swal.fire({
    html: `
      <div style="text-align:center;padding:6px 0 2px;">
        ${_swalIcon(icon, iconBg, iconColor)}
        <h3 style="font-size:18px;font-weight:800;color:#1a0a0a;margin:0 0 8px;font-family:'Plus Jakarta Sans',sans-serif;">${title}</h3>
        <p style="font-size:13.5px;color:#9b8b8b;margin:0;line-height:1.6;">${message}</p>
      </div>`,
    showCancelButton: true,
    confirmButtonText: confirmText,
    cancelButtonText:  '<i class="fas fa-times"></i>&nbsp;Batal',
    confirmButtonColor: confirmColor, cancelButtonColor: '#f3f4f6',
    width: 360, padding: '28px 28px 24px', borderRadius: '20px',
    backdrop: 'rgba(15,10,10,0.55)',
    customClass: { popup:'swal-popup-custom', cancelButton:'swal-cancel-grey' },
  }).then(r => {
    if (r.isConfirmed) {
      if (formId) document.getElementById(formId)?.submit();
      if (onConfirm) onConfirm();
    }
  });
}

/* ── Toast Notification ── */
function toast(msg, icon = 'success') {
  Swal.fire({
    toast: true, position: 'top-end', icon,
    html: `<span style="font-family:'Plus Jakarta Sans',sans-serif;font-size:13.5px;font-weight:600;">${msg}</span>`,
    showConfirmButton: false, timer: 3500, timerProgressBar: true,
    didOpen: (t) => {
      t.addEventListener('mouseenter', Swal.stopTimer);
      t.addEventListener('mouseleave', Swal.resumeTimer);
    },
  });
}

/* ── Number Formatter ── */
function fmtNum(n, dec = 2) {
  return parseFloat(n || 0).toFixed(dec);
}

/* ── Form Dirty Check ── */
function initDirtyCheck(formId) {
  const form = document.getElementById(formId);
  if (!form) return;
  let dirty = false;
  form.addEventListener('input', () => dirty = true);
  window.addEventListener('beforeunload', e => {
    if (dirty) { e.preventDefault(); e.returnValue = ''; }
  });
  form.addEventListener('submit', () => dirty = false);
}

/* ── Auto Dismiss Alert ── */
document.querySelectorAll('.alert[data-dismiss]').forEach(el => {
  const delay = parseInt(el.dataset.dismiss) || 4000;
  setTimeout(() => {
    el.style.transition = 'opacity .4s';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 400);
  }, delay);
});

/* ── BMI Calculator ── */
function hitungBMI(tbId, bbId, resultId, katId) {
  const tb  = parseFloat(document.getElementById(tbId)?.value || 0);
  const bb  = parseFloat(document.getElementById(bbId)?.value || 0);
  const res = document.getElementById(resultId);
  const kat = document.getElementById(katId);
  if (!res) return;
  if (tb > 0 && bb > 0) {
    const bmi = bb / Math.pow(tb / 100, 2);
    res.textContent = bmi.toFixed(1);
    let k = '', cls = 'badge-gray';
    if (bmi < 18.5)     { k = 'Kurus';    cls = 'badge-warning'; }
    else if (bmi < 25)  { k = 'Normal';   cls = 'badge-success'; }
    else if (bmi < 30)  { k = 'Gemuk';    cls = 'badge-warning'; }
    else                { k = 'Obesitas'; cls = 'badge-danger';  }
    if (kat) { kat.textContent = k; kat.className = 'badge ' + cls; }
    res.closest?.('.bmi-display')?.classList.remove('hidden');
  }
}

/* ── Avg Calculator ── */
function hitungRata(ids, resultId) {
  const vals = ids.map(id => parseFloat(document.getElementById(id)?.value || 0)).filter(v => !isNaN(v));
  const res  = document.getElementById(resultId);
  if (!res) return;
  if (vals.length) {
    res.textContent = (vals.reduce((a,b) => a+b, 0) / vals.length).toFixed(2);
    res.closest?.('.avg-display')?.classList.remove('hidden');
  }
}

/* ── Search Highlight ── */
function highlightSearch(tableId, searchVal) {
  const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
  const q    = searchVal.toLowerCase();
  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = (!q || text.includes(q)) ? '' : 'none';
  });
}

// ── Konfirmasi Logout ────────────────────────────────────────────
function konfirmasiLogout() {
  Swal.fire({
    html: `
      <div style="text-align:center;padding:8px 0 4px;">
        <div style="margin:0 auto 18px;width:64px;height:64px;border-radius:18px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 6px 24px rgba(159,18,57,.3);flex-shrink:0;">
          <div style="width:100%;height:50%;background:#CC0001;"></div>
          <div style="width:100%;height:50%;background:#f5f0eb;"></div>
        </div>
        <h2 style="font-size:20px;font-weight:800;color:#1a0a0a;margin:0 0 8px;font-family:'Plus Jakarta Sans',sans-serif;">Keluar dari Sesi?</h2>
        <p style="font-size:13px;color:#9b8b8b;margin:0 0 20px;line-height:1.6;">Kamu akan keluar dari panel admin.<br>Pastikan semua pekerjaan sudah disimpan.</p>
        <div style="display:flex;flex-direction:column;gap:10px;">
          <button onclick="doLogout()"
            style="width:100%;padding:13px;background:linear-gradient(135deg,#9f1239,#7f1d1d);color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;font-family:'Plus Jakarta Sans',sans-serif;box-shadow:0 4px 16px rgba(159,18,57,.3);">
            <i class="fas fa-sign-out-alt"></i> Ya, Keluar Sekarang
          </button>
          <button onclick="Swal.close()"
            style="width:100%;padding:12px;background:#f3f4f6;color:#6b7280;border:none;border-radius:12px;font-size:13.5px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;font-family:'Plus Jakarta Sans',sans-serif;">
            <i class="fas fa-arrow-left"></i> Batal, Kembali
          </button>
        </div>
      </div>
    `,
    showConfirmButton: false,
    showCloseButton: true,
    width: 360,
    padding: '28px',
    borderRadius: '20px',
    background: '#fff',
    backdrop: 'rgba(15,10,10,0.6)',
    customClass: { popup: 'swal-popup-custom' },
  });
}

function doLogout() {
  // Tentukan URL logout berdasarkan BASE_URL yang sudah di-set di layout
  var logoutUrl = window.LOGOUT_URL || (window.BASE_URL ? window.BASE_URL + '/logout.php' : '/sipaskibraka/logout.php');
  Swal.fire({
    html: `
      <div style="text-align:center;padding:16px 0 8px;">
        <div style="width:48px;height:48px;border:3px solid #fecdd3;border-top-color:#9f1239;border-radius:50%;animation:swalSpin .7s linear infinite;margin:0 auto 14px;"></div>
        <p style="font-size:14px;font-weight:600;color:#9f1239;margin:0;font-family:'Plus Jakarta Sans',sans-serif;">Keluar dari sesi...</p>
      </div>
      <style>@keyframes swalSpin{to{transform:rotate(360deg)}}</style>
    `,
    showConfirmButton: false,
    width: 240,
    padding: '24px',
    borderRadius: '16px',
    allowOutsideClick: false,
    timer: 800,
    didClose: function() { window.location.href = logoutUrl; }
  });
}


// Style tambahan untuk SweetAlert custom
(function() {
  const style = document.createElement('style');
  style.textContent = `
    .swal-popup-custom { font-family: 'Plus Jakarta Sans', sans-serif !important; }
    .swal-close-custom { color: #9b8b8b !important; font-size: 20px !important; }
    .swal-close-custom:hover { color: #9f1239 !important; }
    .swal-cancel-grey { color: #374151 !important; }
    .swal2-timer-progress-bar { background: #9f1239 !important; }
  `;
  document.head.appendChild(style);
})();
