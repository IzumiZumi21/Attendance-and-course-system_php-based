<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

if (!empty($_SESSION['admin_id'])) redirect('/index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $stmt = $pdo->prepare('SELECT id, username, password FROM admin WHERE username = :u');
        $stmt->execute([':u' => trim($_POST['username'] ?? '')]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($_POST['password'] ?? '', $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            redirect('/index.php');
        }
        $error = 'Username atau password salah.';
    } catch (PDOException $e) {
        $error = 'Terjadi kesalahan sistem.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Admin</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  
  <!-- Script toggle diletakkan di sini juga karena login.php tidak memakai header.php -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
      }
    });
  </script>
</head>
<body>
<div class="container" style="margin-top: 10vh;">
  <!-- Menggunakan class card-retro -->
  <div class="card-retro">
    <h1>Login Admin</h1>
    
    <?php if ($error): ?>
      <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <?= csrf_field() ?>
      
      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="<?= e($_POST['username'] ?? '') ?>" required autofocus>
      
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
      
      <button class="btn" type="submit">Masuk</button>
    </form>
  </div>
</div>
</body>
</html>