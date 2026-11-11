<?php
require __DIR__ . '/../includes/auth.php';

$id   = (int)($_GET['id'] ?? 0);
$data = ['nip' => '', 'nama' => '', 'email' => ''];
$errors = [];

if ($id) {
    $st = $pdo->prepare('SELECT * FROM dosen WHERE id = :id');
    $st->execute([':id' => $id]);
    $row = $st->fetch();
    if (!$row) { flash('Data tidak ditemukan.', 'error'); redirect('/dosen/index.php'); }
    $data = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $data['nip']   = trim($_POST['nip'] ?? '');
    $data['nama']  = trim($_POST['nama'] ?? '');
    $data['email'] = trim($_POST['email'] ?? '');

    if ($data['nip'] === '')  $errors[] = 'NIP wajib diisi.';
    if ($data['nama'] === '') $errors[] = 'Nama wajib diisi.';
    if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    if (!$errors) {
        try {
            if ($id) {
                $sql = 'UPDATE dosen SET nip = :nip, nama = :nama, email = :email WHERE id = :id';
                $params = [':nip'=>$data['nip'], ':nama'=>$data['nama'], ':email'=>$data['email'] ?: null, ':id'=>$id];
            } else {
                $sql = 'INSERT INTO dosen (nip, nama, email) VALUES (:nip, :nama, :email)';
                $params = [':nip'=>$data['nip'], ':nama'=>$data['nama'], ':email'=>$data['email'] ?: null];
            }
            $pdo->prepare($sql)->execute($params);
            flash('Data dosen berhasil disimpan.');
            redirect('/dosen/index.php');
        } catch (PDOException $e) {
            $errors[] = ($e->getCode() === '23000')
                ? 'NIP sudah terdaftar.'
                : 'Gagal menyimpan data.';
        }
    }
}

$judul = $id ? 'Edit Dosen' : 'Tambah Dosen';
require __DIR__ . '/../includes/header.php';
?>
<h1><?= e($judul) ?></h1>
<?php foreach ($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
<form method="post">
  <?= csrf_field() ?>
  <label>NIP</label>
  <input type="text" name="nip" value="<?= e($data['nip']) ?>" required>
  <label>Nama</label>
  <input type="text" name="nama" value="<?= e($data['nama']) ?>" required>
  <label>Email</label>
  <input type="email" name="email" value="<?= e($data['email']) ?>">
  <button class="btn" type="submit">Simpan</button>
  <a class="btn gray" href="index.php">Batal</a>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>