<?php
require __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/mahasiswa/index.php');
csrf_check();

try {
    $st = $pdo->prepare('DELETE FROM mahasiswa WHERE id = :id');
    $st->execute([':id' => (int)($_POST['id'] ?? 0)]);
    flash('Mahasiswa berhasil dihapus.');
} catch (PDOException $e) {
    flash('Gagal menghapus data mahasiswa.', 'error');
}
redirect('/mahasiswa/index.php');