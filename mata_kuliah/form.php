<?php
require __DIR__ . '/../includes/auth.php';

$id   = (int)($_GET['id'] ?? 0);
$data = ['kode' => '', 'nama' => '', 'sks' => ''];
$errors = [];

if ($id) {
    $st = $pdo->prepare('SELECT * FROM mata_kuliah WHERE id = :id');
    $st->execute([':id' => $id]);
    $row = $st->fetch();
    if (!$row) { flash('Data tidak ditemukan.', 'error'); redirect('/mata_kuliah/index.php'); }
    $data = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $data['kode'] = trim($_POST['kode'] ?? '');
    $data['nama'] = trim($_POST['nama'] ?? '');
    $sksInput     = trim($_POST['sks'] ?? '');

    if ($data['kode'] === '') $errors[] = 'Kode mata kuliah wajib diisi.';
    if ($data['nama'] === '') $errors[] = 'Nama mata kuliah wajib diisi.';
    
    if (!filter_var($sksInput, FILTER_VALIDATE_INT) || (int)$sksInput < 1 || (int)$sksInput > 6) {
        $errors[] = 'SKS harus berupa angka antara 1 sampai 6.';
    } else {
        $data['sks'] = (int)$sksInput;
    }

    if (!$errors) {
        try {
            if ($id) {
                $sql = 'UPDATE mata_kuliah SET kode = :kode, nama = :nama, sks = :sks WHERE id = :id';
                $params = [':kode' => $data['kode'], ':nama' => $data['nama'], ':sks' => $data['sks'], ':id' => $id];
            } else {
                $sql = 'INSERT INTO mata_kuliah (kode, nama, sks) VALUES (:kode, :nama, :sks)';
                $params = [':kode' => $data['kode'], ':nama' => $data['nama'], ':sks' => $data['sks']];
            }
            $pdo->prepare($sql)->execute($params);
            flash('Data mata kuliah berhasil disimpan.');
            redirect('/mata_kuliah/index.php');
        } catch (PDOException $e) {
            $errors[] = ($e->getCode() === '23000')
                ? 'Kode mata kuliah sudah terdaftar.'
                : 'Gagal menyimpan data.';
        }
    }
}

$judul = $id ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah';
require __DIR__ . '/../includes/header.php';
?>
<h1><?= e($judul) ?></h1>
<?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
<form method="post">
  <?= csrf_field() ?>
  <label>Kode Mata Kuliah</label>
  <input type="text" name="kode" value="<?= e($data['kode']) ?>" required>
  <label>Nama Mata Kuliah</label>
  <input type="text" name="nama" value="<?= e($data['nama']) ?>" required>
  <label>SKS (1 - 6)</label>
  <input type="number" name="sks" min="1" max="6" value="<?= e($data['sks']) ?>" required>
  <button class="btn" type="submit">Simpan</button>
  <a class="btn gray" href="index.php">Batal</a>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>