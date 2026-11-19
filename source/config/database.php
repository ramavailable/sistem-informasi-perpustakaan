<?php
/**
 * Konfigurasi koneksi database (PDO MySQL)
 * Sistem Informasi Perpustakaan — UAS
 *
 * Sesuaikan nilai di bawah ini dengan pengaturan hosting/MySQL Anda.
 * Pengaturan default di bawah cocok untuk XAMPP (user root tanpa password).
 */

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'perpustakaan');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Denda keterlambatan pengembalian buku (Rupiah per hari)
define('DENDA_PER_HARI', 1000);

// Lama peminjaman default (hari)
define('LAMA_PINJAM_HARI', 7);

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die('Koneksi database gagal. Periksa pengaturan di config/database.php dan pastikan database sudah diimpor dari database.sql.<br>Detail: ' . htmlspecialchars($e->getMessage()));
}
