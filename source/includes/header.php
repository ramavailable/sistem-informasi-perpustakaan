<?php
/**
 * Header + sidebar. Dipakai semua halaman yang butuh login.
 * Variabel yang diharapkan: $pdo, $title (judul halaman), $subtitle (opsional).
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
    <title><?= e($title ?? 'Sistem Informasi Perpustakaan') ?> (Sistem Informasi Perpustakaan)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= url('/assets/css/app.css') ?>" rel="stylesheet">
</head>
<body>
<header class="lib-topbar d-lg-none">
    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#libSidebar" aria-controls="libSidebar" aria-label="Buka menu">
        <i class="bi bi-list" aria-hidden="true"></i> Menu
    </button>
    <a class="lib-brand" href="<?= url('/dashboard.php') ?>"><i class="bi bi-book" aria-hidden="true"></i> Perpustakaan</a>
</header>
<div class="lib-layout">
    <!-- Sidebar -->
    <nav class="offcanvas-lg offcanvas-start lib-sidebar" tabindex="-1" id="libSidebar" aria-labelledby="libSidebarLabel">
        <div class="offcanvas-header d-lg-none">
            <span class="lib-brand" id="libSidebarLabel"><i class="bi bi-book" aria-hidden="true"></i> Perpustakaan</span>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#libSidebar" aria-label="Tutup menu"></button>
        </div>
        <div class="offcanvas-body">
            <a class="lib-brand d-none d-lg-flex" href="<?= url('/dashboard.php') ?>">
                <i class="bi bi-book" aria-hidden="true"></i>
                <span>Perpustakaan<small>Sistem Informasi</small></span>
            </a>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="lib-nav-link <?= menuAktif(url('/dashboard.php'), $uri) ?>" href="<?= url('/dashboard.php') ?>"><i class="bi bi-speedometer2" aria-hidden="true"></i>Dashboard</a>
                </li>
            </ul>

            <?php if (can($pdo, 'buku.view') || can($pdo, 'anggota.view')): ?>
            <div class="lib-nav-group">Katalog</div>
            <ul class="nav flex-column">
                <?php if (can($pdo, 'buku.view')): ?>
                <li class="nav-item">
                    <a class="lib-nav-link <?= menuAktif(url('/buku/'), $uri) ?>" href="<?= url('/buku/index.php') ?>"><i class="bi bi-journal-bookmark" aria-hidden="true"></i>Data Buku</a>
                </li>
                <?php endif; ?>
                <?php if (can($pdo, 'anggota.view')): ?>
                <li class="nav-item">
                    <a class="lib-nav-link <?= menuAktif(url('/anggota/'), $uri) ?>" href="<?= url('/anggota/index.php') ?>"><i class="bi bi-people" aria-hidden="true"></i>Data Anggota</a>
                </li>
                <?php endif; ?>
            </ul>
            <?php endif; ?>

            <?php if (can($pdo, 'peminjaman.view') || can($pdo, 'pengembalian.process')): ?>
            <div class="lib-nav-group">Sirkulasi</div>
            <ul class="nav flex-column">
                <?php if (can($pdo, 'peminjaman.view')): ?>
                <li class="nav-item">
                    <a class="lib-nav-link <?= menuAktif(url('/peminjaman/'), $uri) ?>" href="<?= url('/peminjaman/index.php') ?>"><i class="bi bi-arrow-left-right" aria-hidden="true"></i>Peminjaman</a>
                </li>
                <?php endif; ?>
                <?php if (can($pdo, 'pengembalian.process')): ?>
                <li class="nav-item">
                    <a class="lib-nav-link <?= menuAktif(url('/pengembalian/'), $uri) ?>" href="<?= url('/pengembalian/index.php') ?>"><i class="bi bi-arrow-return-left" aria-hidden="true"></i>Pengembalian</a>
                </li>
                <?php endif; ?>
            </ul>
            <?php endif; ?>

            <?php if (can($pdo, 'users.manage') || can($pdo, 'roles.manage')): ?>
            <div class="lib-nav-group">Pengaturan</div>
            <ul class="nav flex-column">
                <?php if (can($pdo, 'users.manage')): ?>
                <li class="nav-item">
                    <a class="lib-nav-link <?= menuAktif(url('/users/'), $uri) ?>" href="<?= url('/users/index.php') ?>"><i class="bi bi-person-gear" aria-hidden="true"></i>Manajemen User</a>
                </li>
                <?php endif; ?>
                <?php if (can($pdo, 'roles.manage')): ?>
                <li class="nav-item">
                    <a class="lib-nav-link <?= menuAktif(url('/roles/'), $uri) ?>" href="<?= url('/roles/index.php') ?>"><i class="bi bi-shield-lock" aria-hidden="true"></i>Role &amp; Hak Akses</a>
                </li>
                <?php endif; ?>
            </ul>
            <?php endif; ?>

            <div class="lib-user">
                <div class="lib-user-name"><i class="bi bi-person-circle" aria-hidden="true"></i> <?= e($user['name']) ?></div>
                <span class="lib-role-badge"><?= e($user['role_name']) ?></span>
                <a href="<?= url('/logout.php') ?>" class="btn btn-outline-secondary btn-sm lib-logout"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Logout</a>
            </div>
        </div>
    </nav>

    <!-- Konten -->
    <div class="lib-content">
        <main class="lib-main">
            <h1 class="page-title"><?= e($title ?? 'Dashboard') ?></h1>
            <?php if (!empty($subtitle)): ?>
                <p class="page-subtitle"><?= e($subtitle) ?></p>
            <?php endif; ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>
