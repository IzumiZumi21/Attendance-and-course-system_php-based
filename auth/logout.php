<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /jadwal-absensi/auth/login.php');
exit;