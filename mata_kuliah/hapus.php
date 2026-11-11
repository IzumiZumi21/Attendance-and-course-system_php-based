<?php
require __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/mata_kuliah/index.php');
csrf_check();

try {
    $st = $pdo->prepare('DELETE FROM mata_kuliah WHERE id = :id');
    $st->execute([':id' => (int)($_POST['id'] ?? 0)]);
    flash('Mata kuliah berhasil dihapus.');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        flash('Mata kuliah masih dipakai pada jadwal, tidak bisa dihapus.', 'error');
    } else {
        flash('Gagal menghapus data.', 'error');
    }
}
redirect('/mata_kuliah/index.php');