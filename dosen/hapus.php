<?php
require __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/dosen/index.php');
csrf_check();

try {
    $st = $pdo->prepare('DELETE FROM dosen WHERE id = :id');
    $st->execute([':id' => (int)($_POST['id'] ?? 0)]);
    flash('Dosen berhasil dihapus.');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        flash('Dosen masih dipakai pada jadwal, tidak bisa dihapus.', 'error');
    } else {
        flash('Gagal menghapus data.', 'error');
    }
}
redirect('/dosen/index.php');