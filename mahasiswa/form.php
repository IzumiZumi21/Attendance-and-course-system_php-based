<?php
require __DIR__ . '/../includes/auth.php';

$id   = (int)($_GET['id'] ?? 0);
$data = ['nim' => '', 'nama' => ''];
$errors = [];

if ($id) {
    $st = $pdo->prepare('SELECT * FROM mahasiswa WHERE id = :id');
    $st->execute([':id' => $id]);
    $row = $st->fetch();
    if (!$row) { flash('Data tidak ditemukan.', 'error'); redirect('/mahasiswa/index.php'); }
    $data = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $data['nim']  = trim($_POST['nim'] ?? '');
    $data['nama'] = trim($_POST['nama'] ?? '');

    if ($data['nim'] === '')  $errors[] = 'NIM wajib diisi.';
    if ($data['nama'] === '') $errors[] = 'Nama wajib diisi.';

    if (!$errors) {
        try {
            if ($id) {
                $sql = 'UPDATE mahasiswa SET nim = :nim, nama = :nama WHERE id = :id';
                $params = [':nim' => $data['nim'], ':nama' => $data['nama'], ':id' => $id];
            } else {
                $sql = 'INSERT INTO mahasiswa (nim, nama) VALUES (:nim, :nama)';
                $params = [':nim' => $data['nim'], ':nama' => $data['nama']];
            }
            $pdo->prepare($sql)->execute($params);
            flash('Data mahasiswa berhasil disimpan.');
            redirect('/mahasiswa/index.php');
        } catch (PDOException $e) {
            $errors[] = ($e->getCode() === '23000')
                ? 'NIM sudah terdaftar.'
                : 'Gagal menyimpan data.';
        }
    }
}

$judul = $id ? 'Edit Mahasiswa' : 'Tambah Mahasiswa';
require __DIR__ . '/../includes/header.php';
?>
<h1><?= e($judul) ?></h1>
<?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
<form method="post">
  <?= csrf_field() ?>
  <label>NIM</label>
  <input type="text" name="nim" value="<?= e($data['nim']) ?>" required>
  <label>Nama</label>
  <input type="text" name="nama" value="<?= e($data['nama']) ?>" required>
  <button class="btn" type="submit">Simpan</button>
  <a class="btn gray" href="index.php">Batal</a>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>