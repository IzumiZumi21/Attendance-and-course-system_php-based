<?php
require __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/jadwal/index.php');
}

csrf_check();

try {
    $st = $pdo->prepare('DELETE FROM jadwal WHERE id = :id');
    $st->execute([':id' => (int)($_POST['id'] ?? 0)]);
    flash('Jadwal perkuliahan berhasil dihapus.');
} catch (PDOException $e) {
    // Menangani eror jika ada kendala penghapusan database
    flash('Gagal menghapus data jadwal.', 'error');
}

redirect('/jadwal/index.php');