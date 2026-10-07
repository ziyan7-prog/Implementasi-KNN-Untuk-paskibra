<?php
/**
 * includes/knn.php — KNN Engine (Admin-only, tanpa juri/peserta akun)
 * Data nilai langsung di tabel `peserta` (digabung)
 */

if (defined('KNN_LOADED')) return;
define('KNN_LOADED', true);

// ── Fungsi Matematis ──────────────────────────────────────

function minMaxNormalize(float $x, float $min, float $max): float {
    return ($max == $min) ? 0.0 : ($x - $min) / ($max - $min);
}

function euclideanDistance(array $a, array $b): float {
    $sum = 0.0;
    foreach ($a as $i => $v) {
        $d   = $v - ($b[$i] ?? 0.0);
        $sum += $d * $d;
    }
    return sqrt($sum);
}

// ── Feature Fields ────────────────────────────────────────

function getFeatureFields(): array {
    return [
        'nilai_pip', 'nilai_tiu', 'tinggi_badan', 'berat_badan', 'kesehatan',
        'push_up', 'sit_up', 'back_up', 'lari', 'pbb', 'kepribadian'
    ];
}

function getDataLatih(): array {
    return getDB()->query("SELECT * FROM data_latih ORDER BY id ASC")->fetchAll();
}

function hitungMinMax(array $dataLatih, array $dataUji): array {
    $fields = getFeatureFields();
    $res    = ['min' => [], 'max' => []];
    foreach ($fields as $f) {
        $vals       = array_column($dataLatih, $f);
        $vals[]     = (float)($dataUji[$f] ?? 0);
        $res['min'][$f] = (float)min($vals);
        $res['max'][$f] = (float)max($vals);
    }
    return $res;
}

// ── KNN Utama ─────────────────────────────────────────────

/**
 * Hitung KNN dari data array peserta (bukan dari JOIN tabel juri)
 * $dataUji berisi field: nilai_pip, nilai_tiu, tinggi_badan, berat_badan,
 *   kesehatan, push_up, sit_up, back_up, lari, pbb, kepribadian
 */
function hitungKNNDariData(array $dataUji, int $k = KNN_K): array {
    $dataLatih = getDataLatih();
    if (count($dataLatih) < $k) {
        return ['error' => true, 'message' => "Data latih hanya " . count($dataLatih) . " record, kurang dari K=$k."];
    }

    $fields = getFeatureFields();
    $minMax = hitungMinMax($dataLatih, $dataUji);

    // Normalisasi data uji
    $ujiNorm = [];
    foreach ($fields as $f) {
        $ujiNorm[] = minMaxNormalize((float)($dataUji[$f] ?? 0), (float)$minMax['min'][$f], (float)$minMax['max'][$f]);
    }

    // Hitung jarak ke semua data latih
    $jarak = [];
    foreach ($dataLatih as $latih) {
        $latihNorm = [];
        foreach ($fields as $f) {
            $latihNorm[] = minMaxNormalize((float)$latih[$f], (float)$minMax['min'][$f], (float)$minMax['max'][$f]);
        }
        $jarak[] = [
            'id_latih'       => $latih['id'],
            'nama_referensi' => $latih['nama_referensi'] ?? 'Data-' . $latih['id'],
            'jarak'          => euclideanDistance($ujiNorm, $latihNorm),
            'label'          => $latih['label'],
            'data_latih'     => $latih,
            'latih_norm'     => $latihNorm,
        ];
    }

    // Urutkan ASC jarak
    usort($jarak, fn($a, $b) => $a['jarak'] <=> $b['jarak']);

    // K tetangga terdekat
    $neighbors = array_slice($jarak, 0, $k);
    foreach ($neighbors as $i => &$n) { $n['rank'] = $i + 1; }
    unset($n);

    // Majority voting
    $votes = ['Lulus' => 0, 'Tidak Lulus' => 0];
    foreach ($neighbors as $n) { $votes[$n['label']]++; }

    $label = ($votes['Lulus'] >= $votes['Tidak Lulus']) ? 'Lulus' : 'Tidak Lulus';

    return [
        'error'          => false,
        'label'          => $label,
        'jarak_terdekat' => round($neighbors[0]['jarak'], 8),
        'votes'          => $votes,
        'k_neighbors'    => $neighbors,
        'k_used'         => $k,
        'uji_normalized' => $ujiNorm,
        'minmax'         => $minMax,
        'fields'         => $fields,
    ];
}

/**
 * Proses KNN untuk satu peserta berdasarkan id_peserta,
 * ambil nilai dari tabel peserta langsung.
 */
function prosesKNNPeserta(int $idPeserta, int $k = KNN_K): array {
    $pdo = getDB();

    // Ambil data nilai peserta
    $s = $pdo->prepare("SELECT nilai_pip,nilai_tiu,tinggi_badan,berat_badan,kesehatan,
                               push_up,sit_up,back_up,lari,pbb,kepribadian
                        FROM peserta WHERE id=? AND is_active=1 LIMIT 1");
    $s->execute([$idPeserta]);
    $dataUji = $s->fetch();

    if (!$dataUji) {
        return ['error' => true, 'message' => "Peserta ID=$idPeserta tidak ditemukan."];
    }

    // Cek kelengkapan nilai
    $fields = getFeatureFields();
    foreach ($fields as $f) {
        if ($dataUji[$f] === null || $dataUji[$f] === '') {
            return ['error' => true, 'message' => "Nilai peserta belum lengkap (field: $f)."];
        }
    }

    return hitungKNNDariData($dataUji, $k);
}

// ── Simpan Hasil ──────────────────────────────────────────

function simpanHasilKNN(int $idPeserta, array $hasil, int $k, int $adminId): bool {
    if ($hasil['error']) return false;
    $pdo  = getDB();
    $json = json_encode($hasil['k_neighbors'], JSON_UNESCAPED_UNICODE);
    try {
        $pdo->prepare("INSERT INTO hasil_knn
            (id_peserta,status_kelulusan,skor_knn,k_value,vote_lulus,vote_tidak_lulus,detail_knn,processed_by,processed_at)
            VALUES (?,?,?,?,?,?,?,?,NOW())
            ON DUPLICATE KEY UPDATE
                status_kelulusan=VALUES(status_kelulusan), skor_knn=VALUES(skor_knn),
                k_value=VALUES(k_value), vote_lulus=VALUES(vote_lulus),
                vote_tidak_lulus=VALUES(vote_tidak_lulus), detail_knn=VALUES(detail_knn),
                processed_by=VALUES(processed_by), processed_at=NOW()")
            ->execute([
                $idPeserta, $hasil['label'], $hasil['jarak_terdekat'], $k,
                $hasil['votes']['Lulus'], $hasil['votes']['Tidak Lulus'], $json, $adminId
            ]);
        return true;
    } catch (Throwable $e) { return false; }
}

// ── Kuota ─────────────────────────────────────────────────

function terapkanKuota(int $kuotaL, int $kuotaP, int $adminId): array {
    $pdo  = getDB();
    $rows = $pdo->query("
        SELECT hk.id_peserta, hk.skor_knn, hk.vote_lulus, hk.status_kelulusan, p.jenis_kelamin
        FROM hasil_knn hk
        JOIN peserta p ON p.id = hk.id_peserta
        WHERE hk.skor_knn IS NOT NULL AND p.is_active=1
        ORDER BY hk.vote_lulus DESC, hk.skor_knn ASC
    ")->fetchAll();

    if (empty($rows)) {
        return ['ok' => false, 'msg' => 'Belum ada data KNN. Jalankan proses KNN terlebih dahulu.'];
    }

    // Pisahkan per jenis kelamin, lalu pisahkan lagi:
    // - kandidat: hanya yang label KNN-nya 'Lulus' (bisa dapat kuota)
    // - sudah_gugur: yang label KNN-nya 'Tidak Lulus' (tetap tidak lulus, tidak boleh masuk kuota)
    $lakiLulus     = array_values(array_filter($rows, fn($r) => $r['jenis_kelamin'] === 'L' && $r['status_kelulusan'] === 'Lulus'));
    $lakiTidak     = array_values(array_filter($rows, fn($r) => $r['jenis_kelamin'] === 'L' && $r['status_kelulusan'] !== 'Lulus'));
    $perempuanLulus= array_values(array_filter($rows, fn($r) => $r['jenis_kelamin'] === 'P' && $r['status_kelulusan'] === 'Lulus'));
    $perempuanTidak= array_values(array_filter($rows, fn($r) => $r['jenis_kelamin'] === 'P' && $r['status_kelulusan'] !== 'Lulus'));

    $upd    = $pdo->prepare("UPDATE hasil_knn SET status_kelulusan=?,keterangan_kuota=?,processed_by=?,processed_at=NOW() WHERE id_peserta=?");
    $lL=0; $lP=0; $tL=0; $tP=0;

    // Proses laki-laki yang sudah lulus KNN: terapkan kuota dari yang terbaik
    foreach ($lakiLulus as $i => $r) {
        if ($i < $kuotaL) {
            // Masih dalam kuota: tetap Lulus, bersihkan keterangan kuota lama
            $upd->execute(['Lulus', null, $adminId, $r['id_peserta']]);
            $lL++;
        } else {
            // Melebihi kuota: ubah jadi Tidak Lulus
            $upd->execute(['Tidak Lulus', 'Kuota laki-laki sudah terpenuhi', $adminId, $r['id_peserta']]);
            $tL++;
        }
    }
    // Peserta laki-laki yang sudah tidak lulus dari KNN: tetap tidak lulus
    foreach ($lakiTidak as $r) {
        $upd->execute(['Tidak Lulus', $r['keterangan_kuota'] ?? null, $adminId, $r['id_peserta']]);
        $tL++;
    }

    // Proses perempuan yang sudah lulus KNN: terapkan kuota dari yang terbaik
    foreach ($perempuanLulus as $i => $r) {
        if ($i < $kuotaP) {
            $upd->execute(['Lulus', null, $adminId, $r['id_peserta']]);
            $lP++;
        } else {
            $upd->execute(['Tidak Lulus', 'Kuota perempuan sudah terpenuhi', $adminId, $r['id_peserta']]);
            $tP++;
        }
    }
    // Peserta perempuan yang sudah tidak lulus dari KNN: tetap tidak lulus
    foreach ($perempuanTidak as $r) {
        $upd->execute(['Tidak Lulus', $r['keterangan_kuota'] ?? null, $adminId, $r['id_peserta']]);
        $tP++;
    }

    return ['ok'=>true,'lulus_l'=>$lL,'lulus_p'=>$lP,'tidak_l'=>$tL,'tidak_p'=>$tP,'total_lulus'=>$lL+$lP];
}
