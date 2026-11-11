<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($judul ?? 'Jadwal & Absensi') ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <strong>Jadwal &amp; Absensi</strong>
  <a href="<?= BASE_URL ?>/index.php">Dashboard</a>
  <a href="<?= BASE_URL ?>/jadwal/index.php">Jadwal</a>
  <a href="<?= BASE_URL ?>/dosen/index.php">Dosen</a>
  <a href="<?= BASE_URL ?>/mata_kuliah/index.php">Mata Kuliah</a>
  <a href="<?= BASE_URL ?>/mahasiswa/index.php">Mahasiswa</a>
  <a href="<?= BASE_URL ?>/absensi/input.php">Input Absensi</a>
  <a href="<?= BASE_URL ?>/absensi/rekap.php">Rekap</a>
  
  <div class="right" style="display:flex; align-items:center;">
      <a href="<?= BASE_URL ?>/auth/logout.php" style="color: #ef4444; font-weight:bold;">Logout</a>
      <!-- Tombol Toggle Tema (Bulan/Matahari) -->
      <button id="theme-toggle">🌙</button>
  </div>
</nav>

<main class="container">
<?php if ($f = flash()): ?>
  <div class="alert <?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
<?php endif; ?>

<!-- Script Mode Terang/Gelap -->
<script>
  const toggleBtn = document.getElementById('theme-toggle');
  const body = document.body;
  const currentTheme = localStorage.getItem('theme');
  
  // Cek preferensi saat halaman dimuat
  if (currentTheme === 'dark') {
      body.classList.add('dark-mode');
      toggleBtn.innerHTML = '☀️';
  }
  
  // Fungsi ketika tombol ditekan
  toggleBtn.addEventListener('click', () => {
      body.classList.toggle('dark-mode');
      if (body.classList.contains('dark-mode')) {
          localStorage.setItem('theme', 'dark');
          toggleBtn.innerHTML = '☀️';
      } else {
          localStorage.setItem('theme', 'light');
          toggleBtn.innerHTML = '🌙';
      }
  });
</script>