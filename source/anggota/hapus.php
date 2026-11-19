<?php
/** Hapus Anggota. */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'anggota.delete');

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT nama FROM anggota WHERE id = ?');
$stmt->execute([$id]);
$anggota = $stmt->fetch();

if (!$anggota) {
    setFlash('danger', 'Data anggota tidak ditemukan.');
    redirect('/anggota/index.php');
}

try {
    $stmt = $pdo->prepare('DELETE FROM anggota WHERE id = ?');
    $stmt->execute([$id]);
    setFlash('success', 'Anggota "' . $anggota['nama'] . '" berhasil dihapus.');
} catch (PDOException $e) {
    // Gagal jika anggota masih punya riwayat peminjaman (foreign key).
    setFlash('danger', 'Anggota tidak bisa dihapus karena masih ada riwayat peminjaman.');
}
redirect('/anggota/index.php');
