<?php
/** Hapus Buku. */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'buku.delete');

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT judul FROM buku WHERE id = ?');
$stmt->execute([$id]);
$buku = $stmt->fetch();

if (!$buku) {
    setFlash('danger', 'Data buku tidak ditemukan.');
    redirect('/buku/index.php');
}

try {
    $stmt = $pdo->prepare('DELETE FROM buku WHERE id = ?');
    $stmt->execute([$id]);
    setFlash('success', 'Buku "' . $buku['judul'] . '" berhasil dihapus.');
} catch (PDOException $e) {
    // Gagal jika buku masih dipakai di data peminjaman (foreign key).
    setFlash('danger', 'Buku tidak bisa dihapus karena masih ada riwayat peminjaman.');
}
redirect('/buku/index.php');
