<?php
require __DIR__ . '/../includes/auth.php';
$judul = 'Input Absensi';

$statusValid = ['hadir','izin','sakit','alpa'];
$jadwalId  = (int)($_REQUEST['jadwal_id'] ?? 0);
$pertemuan = (int)($_REQUEST['pertemuan_ke'] ?? 0);
$tanggal   = $_REQUEST['tanggal'] ?? date('Y-m-d');
$errors = [];

// Simpan absensi (TRANSACTION)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $d = DateTime::createFromFormat('Y-m-d', $tanggal);
    if (!$jadwalId)                              $errors[] = 'Pilih jadwal.';
    if ($pertemuan < 1 || $pertemuan > 16)       $errors[] = 'Pertemuan harus 1 sampai 16.';
    if (!$d || $d->format('Y-m-d') !== $tanggal) $errors[] = 'Tanggal tidak valid.';
    if (empty($_POST['status']) || !is_array($_POST['status'])) $errors[] = 'Tidak ada data absensi.';

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $st = $pdo->prepare(
                'INSERT INTO absensi (jadwal_id, mahasiswa_id, pertemuan_ke, tanggal, status)
                 VALUES (:j, :m, :p, :t, :s)
                 ON DUPLICATE KEY UPDATE status = VALUES(status), tanggal = VALUES(tanggal)'
            );
            foreach ($_POST['status'] as $mhsId => $status) {
                if (!in_array($status, $statusValid, true)) {
                    throw new Exception('Status tidak valid.');
                }
                $st->execute([
                    ':j' => $jadwalId, ':m' => (int)$mhsId, ':p' => $pertemuan,
                    ':t' => $tanggal,  ':s' => $status,
                ]);
            }
            $pdo->commit();
            flash('Absensi pertemuan ke-' . $pertemuan . ' berhasil disimpan.');
            redirect('/absensi/input.php?jadwal_id=' . $jadwalId .
                     '&pertemuan_ke=' . $pertemuan . '&tanggal=' . urlencode($tanggal));
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Gagal menyimpan absensi. Tidak ada data yang berubah.';
        }
    }
}

// Data untuk tampilan
$jadwalList = $pdo->query(
    "SELECT j.id, j.hari, j.jam_mulai, mk.nama AS mk, d.nama AS dosen
     FROM jadwal j
     JOIN mata_kuliah mk ON mk.id = j.mata_kuliah_id
     JOIN dosen d ON d.id = j.dosen_id
     ORDER BY mk.nama, FIELD(j.hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')"
)->fetchAll();

$mahasiswa = [];
if ($jadwalId && $pertemuan >= 1 && $pertemuan <= 16) {
    $st = $pdo->prepare(
        'SELECT m.id, m.nim, m.nama, a.status
         FROM mahasiswa m
         LEFT JOIN absensi a ON a.mahasiswa_id = m.id
                            AND a.jadwal_id = :j AND a.pertemuan_ke = :p
         ORDER BY m.nama'
    );
    $st->execute([':j' => $jadwalId, ':p' => $pertemuan]);
    $mahasiswa = $st->fetchAll();
}

require __DIR__ . '/../includes/header.php';
?>
<h1>Input Absensi</h1>
<?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>

<form method="get" class="toolbar">
  <select name="jadwal_id" required>
    <option value="">-- pilih jadwal --</option>
    <?php foreach ($jadwalList as $j): ?>
      <option value="<?= (int)$j['id'] ?>" <?= $jadwalId===(int)$j['id']?'selected':'' ?>>
        <?= e($j['mk']) ?> | <?= e($j['hari']) ?> <?= e(substr($j['jam_mulai'],0,5)) ?> | <?= e($j['dosen']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <input type="number" name="pertemuan_ke" min="1" max="16" placeholder="Pertemuan ke"
         value="<?= $pertemuan ?: '' ?>" required>
  <input type="date" name="tanggal" value="<?= e($tanggal) ?>" required>
  <button class="btn gray" type="submit">Tampilkan</button>
</form>

<?php if ($mahasiswa): ?>
<form method="post">
  <?= csrf_field() ?>
  <input type="hidden" name="jadwal_id" value="<?= $jadwalId ?>">
  <input type="hidden" name="pertemuan_ke" value="<?= $pertemuan ?>">
  <input type="hidden" name="tanggal" value="<?= e($tanggal) ?>">
  <table>
    <tr><th>NIM</th><th>Nama</th><?php foreach ($statusValid as $s): ?><th><?= e(ucfirst($s)) ?></th><?php endforeach; ?></tr>
    <?php foreach ($mahasiswa as $m): ?>
    <tr>
      <td><?= e($m['nim']) ?></td>
      <td><?= e($m['nama']) ?></td>
      <?php foreach ($statusValid as $s): ?>
        <td><input type="radio" name="status[<?= (int)$m['id'] ?>]" value="<?= e($s) ?>"
              <?= ($m['status'] ?? 'hadir') === $s ? 'checked' : '' ?>></td>
      <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
  </table>
  <br><button class="btn" type="submit">Simpan Absensi</button>
</form>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>