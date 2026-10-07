/**
 * SiPaskibraka — data_latih.js
 * JavaScript khusus halaman Data Latih KNN
 */

document.addEventListener('DOMContentLoaded', function () {

  // ── DataTable ────────────────────────────────────────
  if (document.getElementById('tblDataLatih')) {
    initDataTable('tblDataLatih', {
      order: [[0, 'asc']],
      columnDefs: [{ orderable: false, targets: [14] }],
    });
  }

  // ── Reset modal saat backdrop diklik ─────────────────
  document.getElementById('modalForm')?.addEventListener('click', function (e) {
    if (e.target === this) {
      document.getElementById('modalFormTitle').textContent = 'Tambah Data Latih';
      document.getElementById('fAction').value = 'tambah';
      document.getElementById('fId').value     = '';
      document.getElementById('formDataLatih').reset();
    }
  });
});

// Isi form edit dari data row
function editData(d) {
  document.getElementById('modalFormTitle').textContent = 'Edit Data Latih';
  document.getElementById('fAction').value = 'edit';
  document.getElementById('fId').value     = d.id;
  document.getElementById('fNama').value   = d.nama_referensi || '';
  document.getElementById('fLabel').value  = d.label          || 'Lulus';
  document.getElementById('fPIP').value    = d.nilai_pip      ?? 0;
  document.getElementById('fTIU').value    = d.nilai_tiu      ?? 0;
  document.getElementById('fTB').value     = d.tinggi_badan   ?? 0;
  document.getElementById('fBB').value     = d.berat_badan    ?? 0;
  document.getElementById('fKes').value    = d.kesehatan      ?? 1;
  document.getElementById('fPU').value     = d.push_up        ?? 0;
  document.getElementById('fSU').value     = d.sit_up         ?? 0;
  document.getElementById('fBU').value     = d.back_up        ?? 0;
  document.getElementById('fLR').value     = d.lari           ?? 0;
  document.getElementById('fPB').value     = d.pbb            ?? 0;
  document.getElementById('fKEP').value    = d.kepribadian    ?? 0;
  openModal('modalForm');
}

// Konfirmasi hapus data latih
function hapusData(id, nama) {
  Swal.fire({
    html: `
      <div style="text-align:center;padding:6px 0 2px;">
        <div style="width:56px;height:56px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 16px rgba(220,38,38,.2);">
          <i class="fas fa-database" style="font-size:22px;color:#dc2626;"></i>
        </div>
        <h3 style="font-size:18px;font-weight:800;color:#1a0a0a;margin:0 0 8px;font-family:'Plus Jakarta Sans',sans-serif;">Hapus Data Latih?</h3>
        <p style="font-size:13.5px;color:#9b8b8b;margin:0 0 4px;line-height:1.6;">Data referensi <strong style="color:#1a0a0a;">${nama}</strong> akan dihapus permanen.</p>
        <p style="font-size:12px;color:#6b7280;margin:0;"><i class="fas fa-info-circle"></i> Hasil KNN yang tersimpan tidak terpengaruh.</p>
      </div>`,
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-trash-alt"></i>&nbsp;Ya, Hapus',
    cancelButtonText:  '<i class="fas fa-times"></i>&nbsp;Batal',
    confirmButtonColor: '#dc2626', cancelButtonColor: '#f3f4f6',
    width: 380, padding: '28px 28px 24px', borderRadius: '20px',
    backdrop: 'rgba(15,10,10,0.55)',
    customClass: { popup: 'swal-popup-custom', cancelButton: 'swal-cancel-grey' },
  }).then(r => {
    if (r.isConfirmed) {
      document.getElementById('hapusId').value = id;
      document.getElementById('formHapus').submit();
    }
  });
}

// Download template CSV data latih
function downloadTemplate() {
  const header = [
    'Nama Referensi', 'Label', 'PIP', 'TIU', 'Tinggi Badan', 'Berat Badan',
    'Kesehatan (1=Sehat/0=Tidak)', 'Push Up', 'Sit Up', 'Back Up', 'Lari', 'PBB', 'Kepribadian'
  ];
  const sample = [
    ['Peserta A', 'Lulus',       80, 75, 165, 55, 1, 30, 35, 28, 4, 85, 80],
    ['Peserta B', 'Tidak Lulus', 60, 58, 158, 70, 0, 20, 22, 18, 3, 70, 65],
    ['Peserta C', 'Lulus',       85, 82, 170, 62, 1, 35, 38, 32, 5, 88, 85],
  ];
  const csv  = [header, ...sample].map(r => r.map(c => `"${c}"`).join(',')).join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const a    = document.createElement('a');
  a.href     = URL.createObjectURL(blob);
  a.download = 'template_data_latih.csv';
  document.body.appendChild(a); a.click();
  document.body.removeChild(a);
}
