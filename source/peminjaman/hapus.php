<?php
/** Hapus Peminjaman. Jika masih dipinjam, stok buku dikembalikan 1. */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'peminjaman.delete');

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM peminjaman WHERE id = ?');
$stmt->execute([$id]);
$pinjam = $stmt->fetch();

if (!$pinjam) {
    setFlash('danger', 'Data peminjaman tidak ditemukan.');
    redirect('/peminjaman/index.php');
}

$pdo->beginTransaction();
try {
    if (empty($pinjam['tanggal_kembali'])) {
        $pdo->prepare('UPDATE buku SET stok = stok + 1 WHERE id = ?')->execute([$pinjam['buku_id']]);
    }
    $pdo->prepare('DELETE FROM peminjaman WHERE id = ?')->execute([$id]);
    $pdo->commit();
    setFlash('success', 'Data peminjaman berhasil dihapus.');
} catch (Exception $e) {
    $pdo->rollBack();
    setFlash('danger', 'Gagal menghapus data peminjaman.');
}
redirect('/peminjaman/index.php');
