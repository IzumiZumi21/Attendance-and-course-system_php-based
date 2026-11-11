<?php
require __DIR__ . '/includes/auth.php';
$judul = 'Dashboard';

$stat = $pdo->query(
    'SELECT (SELECT COUNT(*) FROM dosen) AS dosen,
            (SELECT COUNT(*) FROM mahasiswa) AS mhs,
            (SELECT COUNT(*) FROM mata_kuliah) AS mk,
            (SELECT COUNT(*) FROM jadwal) AS jadwal'
)->fetch();

$hariIni = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][(int)date('w')];
$st = $pdo->prepare(
    'SELECT j.jam_mulai, j.jam_selesai, j.ruang, mk.nama AS mk, d.nama AS dosen
     FROM jadwal j
     JOIN mata_kuliah mk ON mk.id = j.mata_kuliah_id
     JOIN dosen d ON d.id = j.dosen_id
     WHERE j.hari = :h ORDER BY j.jam_mulai'
);
$st->execute([':h' => $hariIni]);
$hariIniRows = $st->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<h1>Dashboard</h1>

<!-- Container Layout 2 Kolom (Samping-Sampingan) -->
<div class="dashboard-grid">
  
  <!-- SISI KIRI: Kartu Statistik -->
  <div class="dash-left">
    <div class="cards">
      <div class="dash-card">
          <div class="num"><?= (int)$stat['dosen'] ?></div>
          <div class="label">Dosen</div>
      </div>
      <div class="dash-card">
          <div class="num"><?= (int)$stat['mhs'] ?></div>
          <div class="label">Mahasiswa</div>
      </div>
      <div class="dash-card">
          <div class="num"><?= (int)$stat['mk'] ?></div>
          <div class="label">Matakuliah</div>
      </div>
      <div class="dash-card">
          <div class="num"><?= (int)$stat['jadwal'] ?></div>
          <div class="label">Jadwal</div>
      </div>
    </div>
  </div>

  <!-- SISI KANAN: Tabel Jadwal Hari Ini -->
  <div class="dash-right">
    <h2>Jadwal hari ini (<?= e($hariIni) ?>)</h2>
    <table>
      <tr><th>Jam</th><th>Mata Kuliah</th><th>Dosen</th><th>Ruang</th></tr>
      <?php if (!$hariIniRows): ?><tr><td colspan="4">Tidak ada jadwal hari ini.</td></tr><?php endif; ?>
      <?php foreach ($hariIniRows as $r): ?>
      <tr>
        <td><?= e(substr($r['jam_mulai'],0,5)) ?> - <?= e(substr($r['jam_selesai'],0,5)) ?></td>
        <td><?= e($r['mk']) ?></td>
        <td><?= e($r['dosen']) ?></td>
        <td><?= e($r['ruang']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>