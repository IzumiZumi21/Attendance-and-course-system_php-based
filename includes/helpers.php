<?php
const BASE_URL = '/jadwal-absensi';

// Sanitasi output dari XSS
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
}

// Flash message
function flash($msg = null, $type = 'success') {
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

// Proteksi CSRF
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        die('Token tidak valid. Muat ulang halaman.');
    }
}

// Tautan Pagination
function paginate_links($page, $totalPages) {
    if ($totalPages <= 1) return '';
    $out = '<nav class="pagination">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $params = $_GET;
        $params['page'] = $i;
        $cls = ($i === $page) ? 'active' : '';
        $out .= '<a class="' . $cls . '" href="?' . e(http_build_query($params)) . '">' . $i . '</a>';
    }
    return $out . '</nav>';
}

// Validasi bentrok jadwal (sesuai kolom database: dosen_id, ruang, jam_mulai, jam_selesai)
function cekBentrok(PDO $pdo, $hari, $ruang, $dosenId, $mulai, $selesai, $excludeId = 0) {
    $sql = 'SELECT COUNT(*) FROM jadwal
            WHERE hari = :hari
              AND (ruang = :ruang OR dosen_id = :dosen)
              AND jam_mulai < :selesai
              AND jam_selesai > :mulai
              AND id <> :id';
    $st = $pdo->prepare($sql);
    $st->execute([
        ':hari'    => $hari,
        ':ruang'   => $ruang,
        ':dosen'   => $dosenId,
        ':mulai'   => $mulai,
        ':selesai' => $selesai,
        ':id'      => $excludeId,
    ]);
    return (int)$st->fetchColumn() > 0;
}