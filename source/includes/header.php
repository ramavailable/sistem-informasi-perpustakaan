<?php
/**
 * Header + sidebar. Dipakai semua halaman yang butuh login.
 * Variabel yang diharapkan: $pdo, $title (judul halaman).
 */
if (!isset($pdo)) {
    require __DIR__ . '/../config/database.php';
}
requireLogin();
$user = currentUser();
$flash = getFlash();
$uri = $_SERVER['REQUEST_URI'] ?? '';
function menuAktif($path, $uri)
{
    return strpos($uri, $path) === 0 ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Sistem Informasi Perpustakaan') ?> — Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background: #1f2937; }
        .sidebar .nav-link { color: #cbd5e1; border-radius: .375rem; }
        .sidebar .nav-link:hover { background: #374151; color: #fff; }
        .sidebar .nav-link.active { background: #0d6efd; color: #fff; }
        .sidebar .nav-link i { margin-right: .5rem; }
        .sidebar .brand { color: #fff; font-weight: 700; }
        .content { padding: 1.5rem; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar p-3">
            <div class="brand fs-5 mb-1"><i class="bi bi-book"></i> Perpustakaan</div>
            <div class="text-secondary small mb-3">Sistem Informasi Perpustakaan</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?= menuAktif(url('/dashboard.php'), $uri) ?>" href="<?= url('/dashboard.php') ?>"><i class="bi bi-speedometer2"></i>Dashboard</a>
                </li>
                <?php if (can($pdo, 'buku.view')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= menuAktif(url('/buku/'), $uri) ?>" href="<?= url('/buku/index.php') ?>"><i class="bi bi-journal-bookmark"></i>Data Buku</a>
                </li>
                <?php endif; ?>
                <?php if (can($pdo, 'anggota.view')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= menuAktif(url('/anggota/'), $uri) ?>" href="<?= url('/anggota/index.php') ?>"><i class="bi bi-people"></i>Data Anggota</a>
                </li>
                <?php endif; ?>
                <?php if (can($pdo, 'peminjaman.view')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= menuAktif(url('/peminjaman/'), $uri) ?>" href="<?= url('/peminjaman/index.php') ?>"><i class="bi bi-arrow-left-right"></i>Peminjaman</a>
                </li>
                <?php endif; ?>
                <?php if (can($pdo, 'pengembalian.process')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= menuAktif(url('/pengembalian/'), $uri) ?>" href="<?= url('/pengembalian/index.php') ?>"><i class="bi bi-arrow-return-left"></i>Pengembalian</a>
                </li>
                <?php endif; ?>
                <?php if (can($pdo, 'users.manage')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= menuAktif(url('/users/'), $uri) ?>" href="<?= url('/users/index.php') ?>"><i class="bi bi-person-gear"></i>Manajemen User</a>
                </li>
                <?php endif; ?>
                <?php if (can($pdo, 'roles.manage')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= menuAktif(url('/roles/'), $uri) ?>" href="<?= url('/roles/index.php') ?>"><i class="bi bi-shield-lock"></i>Role &amp; Hak Akses</a>
                </li>
                <?php endif; ?>
            </ul>
            <hr class="border-secondary">
            <div class="text-light small">
                <i class="bi bi-person-circle"></i> <?= e($user['name']) ?><br>
                <span class="badge bg-primary"><?= e($user['role_name']) ?></span>
            </div>
            <a href="<?= url('/logout.php') ?>" class="btn btn-outline-light btn-sm w-100 mt-3"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </nav>

        <!-- Konten -->
        <main class="col-md-9 ms-sm-auto col-lg-10 content">
            <h2 class="mb-3"><?= e($title ?? 'Dashboard') ?></h2>
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>
