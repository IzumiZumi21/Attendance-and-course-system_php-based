<?php
require __DIR__ . '/../includes/auth.php';
$judul = 'Data Mahasiswa';

$q      = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;
$like   = "%$q%";

$rows = [];
$totalPages = 1;
try {
    $cnt = $pdo->prepare('SELECT COUNT(*) FROM mahasiswa WHERE nama LIKE :q1 OR nim LIKE :q2');
    $cnt->execute([':q1' => $like, ':q2' => $like]);
    $totalPages = max(1, (int)ceil($cnt->fetchColumn() / $limit));

    $stmt = $pdo->prepare('SELECT * FROM mahasiswa
                           WHERE nama LIKE :q1 OR nim LIKE :q2
                           ORDER BY nim
                           LIMIT :lim OFFSET :off');
    $stmt->bindValue(':q1', $like);
    $stmt->bindValue(':q2', $like);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    flash('Gagal memuat data mahasiswa.', 'error');
}

require __DIR__ . '/../includes/header.php';
?>
<h1>Data Mahasiswa</h1>
<div class="toolbar">
  <a class="btn" href="form.php">+ Tambah Mahasiswa</a>
  <form method="get" style="display:flex;gap:8px">
    <input type="text" name="q" placeholder="Cari nama / NIM" value="<?= e($q) ?>">
    <button class="btn gray" type="submit">Cari</button>
  </form>
</div>

<table>
  <tr><th>No</th><th>NIM</th><th>Nama</th><th>Aksi</th></tr>
  <?php if (!$rows): ?>
    <tr><td colspan="4">Data tidak ditemukan.</td></tr>
  <?php endif; ?>
  <?php foreach ($rows as $i => $r): ?>
  <tr>
    <td><?= $offset + $i + 1 ?></td>
    <td><?= e($r['nim']) ?></td>
    <td><?= e($r['nama']) ?></td>
    <td>
      <a class="btn" href="form.php?id=<?= (int)$r['id'] ?>">Edit</a>
      <form method="post" action="hapus.php" style="display:inline"
            onsubmit="return confirm('Hapus mahasiswa ini? Data absensinya akan ikut terhapus.')">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
        <button class="btn danger" type="submit">Hapus</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?= paginate_links($page, $totalPages) ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>