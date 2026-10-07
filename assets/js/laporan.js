/**
 * laporan.js — fungsi cetak dan export laporan
 */

// Cetak laporan formal (hanya #print-area via @media print)
function cetakLaporan() {
  window.print();
}

// Export tabel lulus ke CSV
function exportLaporanCSV() {
  const rows = [['No','No Peserta','Nama','JK','Sekolah','Status']];
  document.querySelectorAll('#tblLulus tbody tr').forEach((tr, i) => {
    const td = tr.querySelectorAll('td');
    rows.push([
      i + 1,
      td[1]?.innerText?.trim() ?? '',
      td[2]?.innerText?.trim() ?? '',
      td[3]?.innerText?.trim() ?? '',
      td[4]?.innerText?.trim() ?? '',
      'Lulus',
    ]);
  });
  document.querySelectorAll('#tblTidak tbody tr').forEach((tr, i) => {
    const td = tr.querySelectorAll('td');
    rows.push([
      i + 1,
      td[1]?.innerText?.trim() ?? '',
      td[2]?.innerText?.trim() ?? '',
      td[3]?.innerText?.trim() ?? '',
      td[4]?.innerText?.trim() ?? '',
      'Tidak Lulus',
    ]);
  });
  downloadCSV(rows, 'laporan_kelulusan.csv');
}

// Export hasil import KNN ke CSV
function exportImportCSV() {
  const rows = [['Baris','Nama','K','Jarak','Vote L','Vote TL','Hasil']];
  document.querySelectorAll('#tblImport tbody tr').forEach(tr => {
    const td = tr.querySelectorAll('td');
    if (td.length >= 6) {
      rows.push([
        td[0]?.innerText?.trim(),
        td[1]?.innerText?.trim(),
        td[2]?.innerText?.trim(),
        td[3]?.innerText?.trim(),
        td[4]?.innerText?.trim(),
        td[5]?.innerText?.trim(),
        td[6]?.innerText?.trim() ?? '',
      ]);
    }
  });
  downloadCSV(rows, 'hasil_import_knn.csv');
}

function downloadCSV(rows, filename) {
  const bom  = '\uFEFF';
  const csv  = bom + rows.map(r => r.map(v => `"${String(v ?? '').replace(/"/g,'""')}"`).join(',')).join('\r\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement('a');
  a.href     = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
}
