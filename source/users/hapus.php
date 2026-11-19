<?php
/** Hapus User (khusus Admin). User tidak bisa menghapus akunnya sendiri. */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'users.manage');

$id = (int) ($_GET['id'] ?? 0);
if ($id === (int) currentUser()['id']) {
    setFlash('danger', 'Anda tidak bisa menghapus akun Anda sendiri.');
    redirect('/users/index.php');
}

$stmt = $pdo->prepare('SELECT username FROM users WHERE id = ?');
$stmt->execute([$id]);
$userHapus = $stmt->fetch();

if (!$userHapus) {
    setFlash('danger', 'Data user tidak ditemukan.');
    redirect('/users/index.php');
}

$pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
setFlash('success', 'User "' . $userHapus['username'] . '" berhasil dihapus.');
redirect('/users/index.php');
