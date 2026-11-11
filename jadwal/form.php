<?php
require __DIR__ . '/../includes/auth.php';

$hariValid = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$id   = (int)($_GET['id'] ?? 0);
$data = ['mata_kuliah_id'=>0,'dosen_id'=>0,'hari'=>'Senin','jam_mulai'=>'','jam_selesai'=>'','ruang'=>''];
$errors = [];

if ($id) {
    $st = $pdo->prepare('SELECT * FROM jadwal WHERE id = :id');
    $st->execute([':id' => $id]);
    $row = $st->fetch();
    if (!$row) { flash('Jadwal tidak ditemukan.', 'error'); redirect('/jadwal/index.php'); }
    $data = $row;
    $data['jam_mulai']   = substr($row['jam_mulai'], 0, 5);
    $data['jam_selesai'] = substr($row['jam_selesai'], 0, 5);
}

$mkList    = $pdo->query('SELECT id, kode, nama FROM mata_kuliah ORDER BY nama')->fetchAll();
$dosenList = $pdo->query('SELECT id, nama FROM dosen ORDER BY nama')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $data['mata_kuliah_id'] = (int)($_POST['mata_kuliah_id'] ?? 0);
    $data['dosen_id']       = (int)($_POST['dosen_id'] ?? 0);
    $data['hari']           = $_POST['hari'] ?? '';
    $data['jam_mulai']      = $_POST['jam_mulai'] ?? '';
    $data['jam_selesai']    = $_POST['jam_selesai'] ?? '';
    $data['ruang']          = trim($_POST['ruang'] ?? '');

    if (!$data['mata_kuliah_id']) $errors[] = 'Pilih mata kuliah.';
    if (!$data['dosen_id'])       $errors[] = 'Pilih dosen.';
    if (!in_array($data['hari'], $hariValid, true)) $errors[] = 'Hari tidak valid.';
    if ($data['ruang'] === '')    $errors[] = 'Ruang wajib diisi.';
    if (!preg_match('/^\d{2}:\d{2}$/', $data['jam_mulai']) ||
        !preg_match('/^\d{2}:\d{2}$/', $data['jam_selesai'])) {
        $errors[] = 'Format jam tidak valid.';
    } elseif ($data['jam_mulai'] >= $data['jam_selesai']) {
        $errors[] = 'Jam selesai harus lebih besar dari jam mulai.';
    }

    if (!$errors) {
        try {
            if (cekBentrok($pdo, $data['hari'], $data['ruang'], $data['dosen_id'],
                           $data['jam_mulai'], $data['jam_selesai'], $id)) {
                $errors[] = 'Jadwal bentrok: ruang atau dosen yang sama sudah terpakai pada hari dan jam tersebut.';
            } else {
                $p = [
                    ':mk' => $data['mata_kuliah_id'], ':dosen' => $data['dosen_id'],
                    ':hari' => $data['hari'], ':mulai' => $data['jam_mulai'],
                    ':selesai' => $data['jam_selesai'], ':ruang' => $data['ruang'],
                ];
                if ($id) {
                    $p[':id'] = $id;
                    $sql = 'UPDATE jadwal SET mata_kuliah_id=:mk, dosen_id=:dosen, hari=:hari,
                            jam_mulai=:mulai, jam_selesai=:selesai, ruang=:ruang WHERE id=:id';
                } else {
                    $sql = 'INSERT INTO jadwal (mata_kuliah_id, dosen_id, hari, jam_mulai, jam_selesai, ruang)
                            VALUES (:mk, :dosen, :hari, :mulai, :selesai, :ruang)';
                }
                $pdo->prepare($sql)->execute($p);
                flash('Jadwal berhasil disimpan.');
                redirect('/jadwal/index.php');
            }
        } catch (PDOException $e) {
            $errors[] = 'Gagal menyimpan jadwal.';
        }
    }
}

$judul = $id ? 'Edit Jadwal' : 'Tambah Jadwal';
require __DIR__ . '/../includes/header.php';
?>
<h1><?= e($judul) ?></h1>
<?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
<form method="post">
  <?= csrf_field() ?>
  <label>Mata Kuliah</label>
  <select name="mata_kuliah_id" required>
    <option value="">-- pilih --</option>
    <?php foreach ($mkList as $m): ?>
      <option value="<?= (int)$m['id'] ?>" <?= $data['mata_kuliah_id']==$m['id']?'selected':'' ?>>
        <?= e($m['kode']) ?> - <?= e($m['nama']) ?></option>
    <?php endforeach; ?>
  </select>
  <label>Dosen</label>
  <select name="dosen_id" required>
    <option value="">-- pilih --</option>
    <?php foreach ($dosenList as $d): ?>
      <option value="<?= (int)$d['id'] ?>" <?= $data['dosen_id']==$d['id']?'selected':'' ?>>
        <?= e($d['nama']) ?></option>
    <?php endforeach; ?>
  </select>
  <label>Hari</label>
  <select name="hari">
    <?php foreach ($hariValid as $h): ?>
      <option value="<?= e($h) ?>" <?= $data['hari']===$h?'selected':'' ?>><?= e($h) ?></option>
    <?php endforeach; ?>
  </select>
  <label>Jam Mulai</label>
  <input type="time" name="jam_mulai" value="<?= e($data['jam_mulai']) ?>" required>
  <label>Jam Selesai</label>
  <input type="time" name="jam_selesai" value="<?= e($data['jam_selesai']) ?>" required>
  <label>Ruang</label>
  <input type="text" name="ruang" value="<?= e($data['ruang']) ?>" required>
  <button class="btn" type="submit">Simpan</button>
  <a class="btn gray" href="index.php">Batal</a>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>