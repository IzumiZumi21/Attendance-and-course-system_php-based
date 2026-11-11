<?php
require __DIR__ . '/../includes/auth.php';
$judul = 'Data Jadwal';

$hariValid = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$q       = trim($_GET['q'] ?? '');
$hari    = in_array($_GET['hari'] ?? '', $hariValid, true) ? $_GET['hari'] : '';
$dosenId = (int)($_GET['dosen_id'] ?? 0);
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 10;
$offset  = ($page - 1) * $limit;

$where = []; $params = [];
if ($q !== '') {
    $where[] = '(mk.nama LIKE :q1 OR mk.kode LIKE :q2 OR j.ruang LIKE :q3)';
    $params[':q1'] = $params[':q2'] = $params[':q3'] = "%$q%";
}
if ($hari !== '')    { $where[] = 'j.hari = :hari';       $params[':hari'] = $hari; }
if ($dosenId > 0)    { $where[] = 'j.dosen_id = :dosen';  $params[':dosen'] = $dosenId; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$rows = []; $totalPages = 1; $dosenList = [];
try {
    $dosenList = $pdo->query('SELECT id, nama FROM dosen ORDER BY nama')->fetchAll();

    $from = 'FROM jadwal j
             JOIN mata_kuliah mk ON mk.id = j.mata_kuliah_id
             JOIN dosen d        ON d.id  = j.dosen_id';

    $cnt = $pdo->prepare("SELECT COUNT(*) $from $whereSql");
    $cnt->execute($params);
    $totalPages = max(1, (int)ceil($cnt->fetchColumn() / $limit));

    $stmt = $pdo->prepare(
        "SELECT j.*, mk.kode, mk.nama AS mata_kuliah, d.nama AS dosen
         $from $whereSql
         ORDER BY FIELD(j.hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), j.jam_mulai
         LIMIT :lim OFFSET :off"
    );
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    flash('Gagal memuat jadwal.', 'error');
}

require __DIR__ . '/../includes/header.php';
?>
<h1>Data Jadwal</h1>
<div class="toolbar">
  <a class="btn" href="form.php">+ Tambah Jadwal</a>
  <form method="get" style="display:flex;gap:8px;flex-wrap:wrap">
    <input type="text" name="q" placeholder="Cari mata kuliah / ruang" value="<?= e($q) ?>">
    <select name="hari">
      <option value="">Semua hari</option>
      <?php foreach ($hariValid as $h): ?>
        <option value="<?= e($h) ?>" <?= $hari === $h ? 'selected' : '' ?>><?= e($h) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="dosen_id">
      <option value="0">Semua dosen</option>
      <?php foreach ($dosenList as $d): ?>
        <option value="<?= (int)$d['id'] ?>" <?= $dosenId === (int)$d['id'] ? 'selected' : '' ?>>
          <?= e($d['nama']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn gray" type="submit">Filter</button>
  </form>
</div>

<table>
  <tr><th>Hari</th><th>Jam</th><th>Mata Kuliah</th><th>Dosen</th><th>Ruang</th><th>Aksi</th></tr>
  <?php if (!$rows): ?><tr><td colspan="6">Data tidak ditemukan.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= e($r['hari']) ?></td>
    <td><?= e(substr($r['jam_mulai'],0,5)) ?> - <?= e(substr($r['jam_selesai'],0,5)) ?></td>
    <td><?= e($r['kode']) ?> - <?= e($r['mata_kuliah']) ?></td>
    <td><?= e($r['dosen']) ?></td>
    <td><?= e($r['ruang']) ?></td>
    <td>
      <a class="btn" href="form.php?id=<?= (int)$r['id'] ?>">Edit</a>
      <form method="post" action="hapus.php" style="display:inline"
            onsubmit="return confirm('Hapus jadwal ini? Absensinya ikut terhapus.')">
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