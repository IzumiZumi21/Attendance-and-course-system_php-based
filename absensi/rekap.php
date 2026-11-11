<?php
require __DIR__ . '/../includes/auth.php';
$judul = 'Rekap Kehadiran';
$jadwalId = (int)($_GET['jadwal_id'] ?? 0);

$jadwalList = $pdo->query(
    "SELECT j.id, j.hari, mk.nama AS mk, d.nama AS dosen
     FROM jadwal j
     JOIN mata_kuliah mk ON mk.id = j.mata_kuliah_id
     JOIN dosen d ON d.id = j.dosen_id
     ORDER BY mk.nama"
)->fetchAll();

$rows = [];
if ($jadwalId) {
    try {
        $st = $pdo->prepare(
            "SELECT m.nim, m.nama,
                    COUNT(a.id) AS total,
                    COALESCE(SUM(a.status = 'hadir'), 0) AS hadir,
                    COALESCE(SUM(a.status = 'izin'), 0)  AS izin,
                    COALESCE(SUM(a.status = 'sakit'), 0) AS sakit,
                    COALESCE(SUM(a.status = 'alpa'), 0)  AS alpa,
                    ROUND(COALESCE(SUM(a.status = 'hadir'), 0) / NULLIF(COUNT(a.id), 0) * 100, 1) AS persen
             FROM mahasiswa m
             LEFT JOIN absensi a ON a.mahasiswa_id = m.id AND a.jadwal_id = :j
             GROUP BY m.id, m.nim, m.nama
             ORDER BY m.nama"
        );
        $st->execute([':j' => $jadwalId]);
        $rows = $st->fetchAll();
    } catch (PDOException $e) {
        flash('Gagal memuat rekap.', 'error');
    }
}

require __DIR__ . '/../includes/header.php';
?>
<h1>Rekap Kehadiran</h1>
<form method="get" class="toolbar">
  <select name="jadwal_id" required>
    <option value="">-- pilih jadwal --</option>
    <?php foreach ($jadwalList as $j): ?>
      <option value="<?= (int)$j['id'] ?>" <?= $jadwalId===(int)$j['id']?'selected':'' ?>>
        <?= e($j['mk']) ?> | <?= e($j['hari']) ?> | <?= e($j['dosen']) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn gray" type="submit">Tampilkan</button>
</form>

<?php if ($jadwalId): ?>
<table>
  <tr><th>NIM</th><th>Nama</th><th>Total</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpa</th><th>Persentase</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= e($r['nim']) ?></td>
    <td><?= e($r['nama']) ?></td>
    <td><?= (int)$r['total'] ?></td>
    <td><?= (int)$r['hadir'] ?></td>
    <td><?= (int)$r['izin'] ?></td>
    <td><?= (int)$r['sakit'] ?></td>
    <td><?= (int)$r['alpa'] ?></td>
    <td class="<?= ($r['persen'] !== null && $r['persen'] < 75) ? 'low' : '' ?>">
      <?= $r['persen'] === null ? '-' : e($r['persen']) . '%' ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>