<?php
/**
 * Helper autentikasi, otorisasi (RBAC) dan utilitas umum.
 * Dipakai di semua halaman setelah config/database.php dimuat.
 */
session_start();
/**
 * Base path aplikasi relatif terhadap document root, mis. '/perpustakaan'
 * jika dipasang di subfolder, atau '' jika dipasang di root hosting.
 * Dihitung otomatis dari lokasi file ini, jadi tidak perlu diubah manual.
 */
$__docRoot = '';
if (!empty($_SERVER['DOCUMENT_ROOT'])) {
    $__rp = realpath($_SERVER['DOCUMENT_ROOT']);
    if ($__rp !== false) {
        $__docRoot = str_replace('\\', '/', $__rp);
    }
}
$__appRoot = str_replace('\\', '/', realpath(__DIR__ . '/..'));
if (!defined('BASE_PATH')) {
    define('BASE_PATH', ($__docRoot !== '' && strpos($__appRoot, $__docRoot) === 0)
        ? substr($__appRoot, strlen($__docRoot)) : '');
}

/** URL aplikasi: tambahkan BASE_PATH di depan path absolut aplikasi. */
function url($path)
{
    if ($path === '' || $path[0] !== '/') {
        return BASE_PATH . '/' . ltrim($path, '/');
    }
    return BASE_PATH . $path;
}


/** Escape output HTML (mencegah XSS). */
function e($str)
{
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}

/** Redirect ke URL lain lalu berhenti. */
function redirect($url)
{
    if (strpos($url, '/') === 0) {
        $url = url($url);
    }
    header('Location: ' . $url);
    exit;
}

/** Pesan flash satu kali tampil. */
function setFlash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash()
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/** Ambil data user yang sedang login (atau null). */
function currentUser()
{
    return $_SESSION['user'] ?? null;
}

/** Paksa login; kalau belum login arahkan ke halaman login. */
function requireLogin()
{
    if (!currentUser()) {
        setFlash('warning', 'Silakan login terlebih dahulu.');
        redirect('/login.php');
    }
}

/**
 * Ambil daftar nama permission milik role user yang login (dari database).
 * Selalu dibaca dari database agar perubahan RBAC oleh Admin langsung berlaku.
 */
function userPermissions(PDO $pdo)
{
    $user = currentUser();
    if (!$user) {
        return [];
    }
    $stmt = $pdo->prepare(
        'SELECT p.name FROM permissions p
         JOIN role_permissions rp ON rp.permission_id = p.id
         WHERE rp.role_id = ?'
    );
    $stmt->execute([$user['role_id']]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/** Cek apakah user yang login punya permission tertentu. */
function can(PDO $pdo, $permission)
{
    return in_array($permission, userPermissions($pdo), true);
}

/**
 * Penegakan RBAC sisi server.
 * Halaman yang memanggil fungsi ini akan menolak akses (403) jika
 * user tidak punya permission — bukan sekadar menyembunyikan menu.
 */
function requirePermission(PDO $pdo, $permission)
{
    requireLogin();
    if (!can($pdo, $permission)) {
        http_response_code(403);
        $title = 'Akses Ditolak';
        require __DIR__ . '/header.php';
        echo '<div class="alert alert-danger"><h4 class="alert-heading">Akses Ditolak (403)</h4>'
            . '<p>Role Anda (<strong>' . e(currentUser()['role_name']) . '</strong>) tidak memiliki izin '
            . '"<code>' . e($permission) . '</code>". Silakan hubungi Admin.</p>'
            . '<a href="' . url('/dashboard.php') . '" class="btn btn-primary">Kembali ke Dashboard</a></div>';
        require __DIR__ . '/footer.php';
        exit;
    }
}

/** Cek apakah user yang login punya role tertentu. */
function isRole($roleName)
{
    $user = currentUser();
    return $user && $user['role_name'] === $roleName;
}

/** Format tanggal Indonesia, mis. 19 November 2026. */
function tglIndo($tanggal)
{
    if (empty($tanggal)) {
        return '-';
    }
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];
    $ts = strtotime($tanggal);
    return date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

/** Format angka menjadi Rupiah, mis. Rp10.000 */
function rupiah($angka)
{
    return 'Rp' . number_format((int) $angka, 0, ',', '.');
}

/**
 * Hitung denda keterlambatan.
 * $tanggalJatuhTempo dan $tanggalKembali dalam format Y-m-d.
 * Denda = jumlah hari terlambat x DENDA_PER_HARI.
 */
function hitungDenda($tanggalJatuhTempo, $tanggalKembali)
{
    $due = new DateTime($tanggalJatuhTempo);
    $ret = new DateTime($tanggalKembali);
    if ($ret <= $due) {
        return 0;
    }
    $hari = (int) $due->diff($ret)->format('%a');
    return $hari * DENDA_PER_HARI;
}

/** Status tampil peminjaman (dihitung dinamis dari tanggal). */
function statusPeminjaman($row)
{
    if (!empty($row['tanggal_kembali'])) {
        return ['label' => 'Dikembalikan', 'class' => 'success'];
    }
    if (date('Y-m-d') > $row['tanggal_jatuh_tempo']) {
        return ['label' => 'Terlambat', 'class' => 'danger'];
    }
    return ['label' => 'Dipinjam', 'class' => 'primary'];
}
