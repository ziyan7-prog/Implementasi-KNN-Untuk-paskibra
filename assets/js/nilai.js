/**
 * SiPaskibraka — nilai.js
 * JavaScript khusus halaman Input Nilai Peserta
 */

document.addEventListener('DOMContentLoaded', function () {

  // ── Filter daftar peserta di sidebar ──────────────────
  const searchEl = document.getElementById('searchPeserta');
  searchEl?.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.peserta-item').forEach(el => {
      el.style.display = el.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });

  // ── Inisialisasi live calculators ────────────────────
  updRata();
  updBMI2();

  // ── Dirty check: konfirmasi sebelum tinggalkan halaman ─
  initDirtyCheck('formNilai');
});

// Hitung rata-rata PIP + TIU
function updRata() {
  const pip = parseFloat(document.getElementById('inPIP')?.value || '');
  const tiu = parseFloat(document.getElementById('inTIU')?.value || '');
  const el  = document.getElementById('rataAkad');
  if (!el) return;
  if (!isNaN(pip) && !isNaN(tiu)) {
    el.textContent  = ((pip + tiu) / 2).toFixed(2);
    el.style.color  = '#111827';
    el.style.fontWeight = '700';
  } else {
    el.textContent  = '—';
    el.style.color  = '#9ca3af';
    el.style.fontWeight = '400';
  }
}

// Hitung BMI dari tinggi & berat
function updBMI2() {
  const tb  = parseFloat(document.getElementById('inTB')?.value || '');
  const bb  = parseFloat(document.getElementById('inBB')?.value || '');
  const el  = document.getElementById('bmiResult');
  if (!el) return;
  if (tb > 0 && bb > 0) {
    const bmi = bb / Math.pow(tb / 100, 2);
    el.textContent  = bmi.toFixed(1);
    el.style.fontWeight = '700';
    if (bmi < 18.5)        el.style.color = '#d97706';
    else if (bmi < 25)     el.style.color = '#059669';
    else if (bmi < 30)     el.style.color = '#d97706';
    else                   el.style.color = '#dc2626';
  } else {
    el.textContent  = '—';
    el.style.color  = '#9ca3af';
    el.style.fontWeight = '400';
  }
}

// hapusNilai dipindah ke app.js (global)

// Scroll ke form input saat klik edit dari tabel rekap
function scrollToForm() {
  setTimeout(() => {
    const form = document.getElementById('formNilai');
    if (form) form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }, 100);
}

// Export rekap nilai ke CSV
function exportRekapCSV() {
  const tbl = document.getElementById('tblRekapNilai');
  if (!tbl) return;
  const rows = [];
  tbl.querySelectorAll('thead tr').forEach(tr => {
    rows.push([...tr.querySelectorAll('th')]
      .slice(0, -1) // hapus kolom Aksi
      .map(th => th.innerText.trim()));
  });
  tbl.querySelectorAll('tbody tr').forEach(tr => {
    const tds = [...tr.querySelectorAll('td')].slice(0, -1);
    rows.push(tds.map(td => td.innerText.replace(/\s+/g,' ').trim()));
  });
  const bom = '\uFEFF';
  const csv = bom + rows.map(r =>
    r.map(v => `"${String(v ?? '').replace(/"/g,'""')}"`).join(',')
  ).join('\r\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement('a');
  a.href = url; a.download = 'rekap_nilai_peserta.csv'; a.click();
  URL.revokeObjectURL(url);
}

// Highlight baris peserta yang sedang diedit di tabel rekap
document.addEventListener('DOMContentLoaded', function () {
  const params = new URLSearchParams(window.location.search);
  const pid    = params.get('peserta');
  if (pid) {
    const row = document.getElementById('row-' + pid);
    if (row) {
      row.style.outline = '2px solid #9f1239';
      row.style.outlineOffset = '-2px';
      row.style.borderRadius = '6px';
      // Highlight saja — tidak scroll ke bawah, user tetap di area form input
    }
  }
});
