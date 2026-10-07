/**
 * SiPaskibraka — knn.js
 * JavaScript khusus halaman Proses KNN
 */

document.addEventListener('DOMContentLoaded', function () {

  // ── DataTable hasil KNN ───────────────────────────────
  if (document.getElementById('tblHasilKNN')) {
    initDataTable('tblHasilKNN', {
      order: [],
      columnDefs: [
        { orderable: false, targets: [11] },
        { searchable: false, targets: [0, 5, 6, 7, 8, 10, 11] },
      ],
    });
  }

  // ── DataTable hasil import ────────────────────────────
  if (document.getElementById('tblImport')) {
    initDataTable('tblImport', { paging: false, searching: false, info: false });
    // Scroll ke hasil import
    document.getElementById('importHasil')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ── Submit form KNN: validasi peserta terpilih ────────
  document.getElementById('formKNN')?.addEventListener('submit', function (e) {
    const actionBtn = e.submitter?.value || '';
    if (actionBtn === 'proses_satu') {
      const sel = document.getElementById('selPeserta');
      if (!sel || !sel.value) {
        e.preventDefault();
        Swal.fire({
          html: `<div style="text-align:center;padding:6px 0 2px;">
            <div style="width:56px;height:56px;border-radius:50%;background:#fff7ed;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 16px rgba(245,158,11,.2);">
              <i class="fas fa-user-slash" style="font-size:22px;color:#f59e0b;"></i>
            </div>
            <h3 style="font-size:18px;font-weight:800;color:#1a0a0a;margin:0 0 8px;font-family:'Plus Jakarta Sans',sans-serif;">Pilih Peserta Dulu</h3>
            <p style="font-size:13.5px;color:#9b8b8b;margin:0;line-height:1.6;">Silakan pilih satu peserta dari dropdown, atau gunakan tombol <strong>Proses Semua</strong>.</p>
          </div>`,
          showCancelButton: false,
          confirmButtonText: '<i class="fas fa-check"></i>&nbsp;Mengerti',
          confirmButtonColor: '#9f1239',
          width: 360, padding: '28px 28px 24px', borderRadius: '20px',
          backdrop: 'rgba(15,10,10,0.45)',
          customClass: { popup: 'swal-popup-custom' },
        });
      }
    }
  });
});

// Download template import CSV
function downloadTemplateImport() {
  const header = ['Nama Peserta', 'PIP', 'TIU', 'Tinggi Badan', 'Berat Badan',
                  'Kesehatan (1/0)', 'Push Up', 'Sit Up', 'Back Up', 'Lari', 'PBB', 'Kepribadian'];
  const sample = [
    ['Budi Santoso', 78, 82, 165, 55, 1, 30, 35, 28, 4, 85, 80],
    ['Siti Rahayu',  75, 79, 158, 50, 1, 25, 30, 25, 3, 82, 78],
    ['Ahmad Fauzi',  80, 85, 170, 60, 0, 28, 32, 27, 4, 88, 83],
  ];
  const csv  = [header, ...sample].map(r => r.join(',')).join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const a    = document.createElement('a');
  a.href     = URL.createObjectURL(blob);
  a.download = 'template_import_knn.csv';
  document.body.appendChild(a); a.click();
  document.body.removeChild(a);
}

// Export hasil import ke CSV
function exportImportCSV() {
  const rows = [['No', 'Nama Peserta', 'K', 'Jarak Terdekat', 'Vote Lulus', 'Vote TL', 'Hasil KNN']];
  let no = 1;
  document.querySelectorAll('#tblImport tbody tr').forEach(tr => {
    const cells = tr.querySelectorAll('td');
    if (!cells.length) return;
    const isError = cells[2]?.colSpan > 1 || tr.style.background?.includes('fef2f2');
    if (isError) {
      rows.push([no++, cells[1]?.textContent.trim(), '', '', '', '', 'Error']);
    } else {
      const votes = cells[4]?.textContent.trim().split(':') || ['', ''];
      rows.push([
        no++,
        cells[1]?.textContent.trim(),
        cells[2]?.textContent.trim(),
        cells[3]?.textContent.trim(),
        votes[0]?.trim(),
        votes[1]?.trim(),
        cells[5]?.textContent.trim(),
      ]);
    }
  });
  const csv  = rows.map(r => r.map(c => `"${(c||'').replace(/"/g, '""')}"`).join(',')).join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const a    = document.createElement('a');
  a.href     = URL.createObjectURL(blob);
  a.download = 'hasil_import_knn.csv';
  document.body.appendChild(a); a.click();
  document.body.removeChild(a);
}

// Download template import Excel/CSV
function downloadTemplateImport() {
  const headers = ['Nama Peserta','nilai_pip','nilai_tiu','tinggi_badan','berat_badan',
                   'kesehatan','push_up','sit_up','back_up','lari','pbb','kepribadian'];
  const contoh  = ['Ahmad Contoh', 80, 75, 172, 65, 90, 30, 25, 20, 12.5, 85, 88];
  const bom  = '\uFEFF';
  const csv  = bom + [headers, contoh]
    .map(r => r.map(v => `"${String(v).replace(/"/g,'""')}"`).join(','))
    .join('\r\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement('a');
  a.href = url; a.download = 'template_import_knn.csv'; a.click();
  URL.revokeObjectURL(url);
}

// ── Konfirmasi Proses Semua KNN ──
function konfirmasiProsesSemuaKNN(jumlah) {
  Swal.fire({
    html: `
      <div style="text-align:center;padding:6px 0 2px;">
        <div style="width:56px;height:56px;border-radius:50%;background:#fff7ed;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 16px rgba(245,158,11,.2);">
          <i class="fas fa-brain" style="font-size:22px;color:#f59e0b;"></i>
        </div>
        <h3 style="font-size:18px;font-weight:800;color:#1a0a0a;margin:0 0 8px;font-family:'Plus Jakarta Sans',sans-serif;">Proses Semua KNN?</h3>
        <p style="font-size:13.5px;color:#9b8b8b;margin:0 0 4px;line-height:1.6;">
          Akan memproses KNN untuk <strong style="color:#1a0a0a;">${jumlah} peserta</strong> yang sudah memiliki nilai lengkap.
        </p>
        <p style="font-size:12px;color:#6b7280;margin:0;"><i class="fas fa-clock"></i> Proses ini mungkin membutuhkan beberapa saat.</p>
      </div>`,
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-brain"></i>&nbsp;Ya, Proses Sekarang',
    cancelButtonText:  '<i class="fas fa-times"></i>&nbsp;Batal',
    confirmButtonColor: '#f59e0b', cancelButtonColor: '#f3f4f6',
    width: 380, padding: '28px 28px 24px', borderRadius: '20px',
    backdrop: 'rgba(15,10,10,0.55)',
    customClass: { popup: 'swal-popup-custom', cancelButton: 'swal-cancel-grey' },
  }).then(r => {
    if (r.isConfirmed) {
      document.getElementById('formAction').value = 'proses_semua';
      document.getElementById('formKNN').submit();
    }
  });
}

// ── Konfirmasi Reset KNN ──
function konfirmasiResetKNN(form) {
  Swal.fire({
    html: `
      <div style="text-align:center;padding:6px 0 2px;">
        <div style="width:56px;height:56px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 16px rgba(220,38,38,.2);">
          <i class="fas fa-rotate-left" style="font-size:22px;color:#dc2626;"></i>
        </div>
        <h3 style="font-size:18px;font-weight:800;color:#1a0a0a;margin:0 0 8px;font-family:'Plus Jakarta Sans',sans-serif;">Reset Semua Hasil KNN?</h3>
        <p style="font-size:13.5px;color:#9b8b8b;margin:0 0 4px;line-height:1.6;">Seluruh hasil klasifikasi KNN akan dihapus. Data nilai dan peserta tetap aman.</p>
        <p style="font-size:12px;color:#ef4444;margin:0;"><i class="fas fa-exclamation-triangle"></i> Tindakan ini tidak dapat dibatalkan.</p>
      </div>`,
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-rotate-left"></i>&nbsp;Ya, Reset KNN',
    cancelButtonText:  '<i class="fas fa-times"></i>&nbsp;Batal',
    confirmButtonColor: '#dc2626', cancelButtonColor: '#f3f4f6',
    width: 380, padding: '28px 28px 24px', borderRadius: '20px',
    backdrop: 'rgba(15,10,10,0.55)',
    customClass: { popup: 'swal-popup-custom', cancelButton: 'swal-cancel-grey' },
  }).then(r => { if (r.isConfirmed) form.submit(); });
}
