<?php
/** Dashboard — ringkasan statistik dan peminjaman terbaru. */
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
requirePermission($pdo, 'dashboard.view');

$title = 'Dashboard';
$user = currentUser();

// Statistik umum (Admin & Pustakawan melihat semua data)
$totalBuku    = (int) $pdo->query('SELECT COUNT(*) FROM buku')->fetchColumn();
$totalAnggota = (int) $pdo->query('SELECT COUNT(*) FROM anggota')->fetchColumn();
$pinjamAktif  = (int) $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE tanggal_kembali IS NULL")->fetchColumn();
$terlambat    = (int) $pdo->query(
    "SELECT COUNT(*) FROM peminjaman WHERE tanggal_kembali IS NULL AND tanggal_jatuh_tempo < CURDATE()"
)->fetchColumn();
$totalDenda   = (int) $pdo->query('SELECT COALESCE(SUM(denda),0) FROM peminjaman')->fetchColumn();

// Peminjaman terbaru. Role Anggota hanya melihat pinjamannya sendiri.
if (isRole('Anggota') && !empty($user['anggota_id'])) {
    $stmt = $pdo->prepare(
        'SELECT p.*, a.nama AS nama_anggota, b.judul AS judul_buku
         FROM peminjaman p
         JOIN anggota a ON a.id = p.anggota_id
         JOIN buku b ON b.id = p.buku_id
         WHERE p.anggota_id = ?
         ORDER BY p.created_at DESC LIMIT 10'
    );
    $stmt->execute([$user['anggota_id']]);
    $pinjamanTerbaru = $stmt->fetchAll();
    // Statistik personal untuk Anggota
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE anggota_id = ? AND tanggal_kembali IS NULL");
    $stmt->execute([$user['anggota_id']]);
    $pinjamAktif = (int) $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE anggota_id = ? AND tanggal_kembali IS NULL AND tanggal_jatuh_tempo < CURDATE()");
    $stmt->execute([$user['anggota_id']]);
    $terlambat = (int) $stmt->fetchColumn();
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(denda),0) FROM peminjaman WHERE anggota_id = ?');
    $stmt->execute([$user['anggota_id']]);
    $totalDenda = (int) $stmt->fetchColumn();
} else {
    $pinjamanTerbaru = $pdo->query(
        'SELECT p.*, a.nama AS nama_anggota, b.judul AS judul_buku
         FROM peminjaman p
         JOIN anggota a ON a.id = p.anggota_id
         JOIN buku b ON b.id = p.buku_id
         ORDER BY p.created_at DESC LIMIT 10'
    )->fetchAll();
}

require __DIR__ . '/includes/header.php';
?>

<div class="alert alert-light border">
    Selamat datang, <strong><?= e($user['name']) ?></strong>! Anda login sebagai
    <span class="badge bg-primary"><?= e($user['role_name']) ?></span>.
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-bg-primary"><div class="card-body">
            <h6 class="card-title"><i class="bi bi-journal-bookmark"></i> Total Buku</h6>
            <p class="card-text fs-3 mb-0"><?= $totalBuku ?></p>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-success"><div class="card-body">
            <h6 class="card-title"><i class="bi bi-people"></i> Total Anggota</h6>
            <p class="card-text fs-3 mb-0"><?= $totalAnggota ?></p>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-warning"><div class="card-body">
            <h6 class="card-title"><i class="bi bi-arrow-left-right"></i> Dipinjam Aktif</h6>
            <p class="card-text fs-3 mb-0"><?= $pinjamAktif ?></p>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-danger"><div class="card-body">
            <h6 class="card-title"><i class="bi bi-exclamation-triangle"></i> Terlambat</h6>
            <p class="card-text fs-3 mb-0"><?= $terlambat ?></p>
            <small>Total denda: <?= rupiah($totalDenda) ?></small>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-clock-history"></i> Peminjaman Terbaru</div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr>
                <th>Anggota</th><th>Buku</th><th>Tgl Pinjam</th><th>Jatuh Tempo</th><th>Status</th>
            </tr></thead>
            <tbody>
            <?php if (empty($pinjamanTerbaru)): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">Belum ada data peminjaman.</td></tr>
            <?php else: ?>
                <?php foreach ($pinjamanTerbaru as $row): $st = statusPeminjaman($row); ?>
                <tr>
                    <td><?= e($row['nama_anggota']) ?></td>
                    <td><?= e($row['judul_buku']) ?></td>
                    <td><?= tglIndo($row['tanggal_pinjam']) ?></td>
                    <td><?= tglIndo($row['tanggal_jatuh_tempo']) ?></td>
                    <td><span class="badge bg-<?= $st['class'] ?>"><?= $st['label'] ?></span></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
