/**
 * SiPaskibraka — peserta.js
 * JavaScript khusus halaman Data Peserta
 */

document.addEventListener('DOMContentLoaded', function () {

  // ── DataTable ──────────────────────────────────────────
  if (document.getElementById('tblPeserta')) {
    initDataTable('tblPeserta', {
      order: [],
      columnDefs: [
        { orderable: false, targets: [7] },
        { searchable: false, targets: [0, 5, 6, 7] },
      ],
    });
  }

  // ── Buka modal tambah jika ada ?action=add ──────────────
  const params = new URLSearchParams(window.location.search);
  if (params.get('action') === 'add') openModal('modalTambah');

  // ── Reset modal saat backdrop diklik ────────────────────
  document.getElementById('modalTambah')?.addEventListener('click', function (e) {
    if (e.target === this) resetFormPeserta();
  });
});

// Edit peserta — isi form modal dari data row
function editPeserta(p) {
  document.getElementById('modalTambahTitle').textContent = 'Edit Data Peserta';
  document.getElementById('formAction').value  = 'edit';
  document.getElementById('formId').value      = p.id;
  document.getElementById('fNama').value       = p.nama          || '';
  document.getElementById('fNomor').value      = p.nomor_peserta || '';
  document.getElementById('fJK').value         = p.jenis_kelamin || 'L';
  document.getElementById('fSekolah').value    = p.asal_sekolah  || '';
  // Tampilkan field nomor manual saat edit
  document.getElementById('groupNomor').style.display     = 'block';
  document.getElementById('groupNomorAuto').style.display = 'none';
  document.getElementById('fNomor').required = true;
  openModal('modalTambah');
}

// Reset form ke mode tambah
function resetFormPeserta() {
  document.getElementById('modalTambahTitle').textContent = 'Tambah Peserta Baru';
  document.getElementById('formAction').value = 'tambah';
  document.getElementById('formId').value     = '';
  document.getElementById('formPeserta').reset();
  // Sembunyikan field nomor manual, tampilkan preview auto
  document.getElementById('groupNomor').style.display     = 'none';
  document.getElementById('groupNomorAuto').style.display = 'block';
  document.getElementById('fNomor').required = false;
}

// Konfirmasi hapus peserta
function hapusPeserta(id, nama) {
  Swal.fire({
    html: `
      <div style="text-align:center;padding:6px 0 2px;">
        <div style="width:56px;height:56px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 16px rgba(220,38,38,.2);">
          <i class="fas fa-user-times" style="font-size:22px;color:#dc2626;"></i>
        </div>
        <h3 style="font-size:18px;font-weight:800;color:#1a0a0a;margin:0 0 8px;font-family:'Plus Jakarta Sans',sans-serif;">Hapus Peserta?</h3>
        <p style="font-size:13.5px;color:#9b8b8b;margin:0 0 4px;line-height:1.6;">Data <strong style="color:#1a0a0a;">${nama}</strong> beserta semua nilai dan hasil KNN-nya akan dihapus permanen.</p>
        <p style="font-size:12px;color:#ef4444;margin:0;"><i class="fas fa-exclamation-triangle"></i> Tindakan ini tidak dapat dibatalkan.</p>
      </div>`,
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-user-times"></i>&nbsp;Ya, Hapus Peserta',
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

// Download template CSV import
function downloadTemplate() {
  const rows = [
    ['Nama Lengkap', 'JK (L/P)', 'Asal Sekolah'],
    ['Budi Santoso', 'L', 'SMA Negeri 1 Contoh'],
    ['Siti Rahayu',  'P', 'SMA Negeri 2 Contoh'],
    ['Ahmad Fauzi',  'L', 'MAN 1 Contoh'],
  ];
  const csv  = rows.map(r => r.map(c => `"${c}"`).join(',')).join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement('a');
  a.href = url; a.download = 'template_import_peserta.csv';
  document.body.appendChild(a); a.click();
  document.body.removeChild(a); URL.revokeObjectURL(url);
}
