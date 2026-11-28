<?php
/** Data Peminjaman — daftar + filter status. */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'peminjaman.view');

$title = 'Data Peminjaman';
$user = currentUser();
$filter = $_GET['status'] ?? 'semua';

$sql = 'SELECT p.*, a.nama AS nama_anggota, a.nomor_anggota, b.judul AS judul_buku
        FROM peminjaman p
        JOIN anggota a ON a.id = p.anggota_id
        JOIN buku b ON b.id = p.buku_id';
$where = [];
$params = [];

// Role Anggota hanya melihat pinjamannya sendiri.
if (isRole('Anggota') && !empty($user['anggota_id'])) {
    $where[] = 'p.anggota_id = ?';
    $params[] = $user['anggota_id'];
}
if ($filter === 'dipinjam') {
    $where[] = 'p.tanggal_kembali IS NULL AND p.tanggal_jatuh_tempo >= CURDATE()';
} elseif ($filter === 'terlambat') {
    $where[] = 'p.tanggal_kembali IS NULL AND p.tanggal_jatuh_tempo < CURDATE()';
} elseif ($filter === 'dikembalikan') {
    $where[] = 'p.tanggal_kembali IS NOT NULL';
}
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY p.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$peminjaman = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="btn-group" role="group">
        <a href="<?= url('/peminjaman/index.php') ?>?status=semua" class="btn btn-outline-secondary <?= $filter === 'semua' ? 'active' : '' ?>">Semua</a>
        <a href="<?= url('/peminjaman/index.php') ?>?status=dipinjam" class="btn btn-outline-primary <?= $filter === 'dipinjam' ? 'active' : '' ?>">Dipinjam</a>
        <a href="<?= url('/peminjaman/index.php') ?>?status=terlambat" class="btn btn-outline-danger <?= $filter === 'terlambat' ? 'active' : '' ?>">Terlambat</a>
        <a href="<?= url('/peminjaman/index.php') ?>?status=dikembalikan" class="btn btn-outline-success <?= $filter === 'dikembalikan' ? 'active' : '' ?>">Dikembalikan</a>
    </div>
    <?php if (can($pdo, 'peminjaman.create')): ?>
        <a href="<?= url('/peminjaman/tambah.php') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Peminjaman</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead><tr>
                <th>#</th><th>Anggota</th><th>Buku</th><th>Tgl Pinjam</th>
                <th>Jatuh Tempo</th><th>Tgl Kembali</th><th>Denda</th><th>Status</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            <?php if (empty($peminjaman)): ?>
                <tr><td colspan="9"><div class="empty-state">
                    <p>Belum ada peminjaman untuk filter ini.</p>
                    <?php if (can($pdo, 'peminjaman.create')): ?>
                        <p><a href="<?= url('/peminjaman/tambah.php') ?>" class="btn btn-primary btn-sm">Tambah Peminjaman</a></p>
                    <?php endif; ?>
                </div></td></tr>
            <?php else: ?>
                <?php foreach ($peminjaman as $i => $row): $st = statusPeminjaman($row); ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($row['nama_anggota']) ?><br><small class="text-muted"><?= e($row['nomor_anggota']) ?></small></td>
                    <td><?= e($row['judul_buku']) ?></td>
                    <td><?= tglIndo($row['tanggal_pinjam']) ?></td>
                    <td><?= tglIndo($row['tanggal_jatuh_tempo']) ?></td>
                    <td><?= tglIndo($row['tanggal_kembali']) ?></td>
                    <td><?= rupiah($row['denda']) ?></td>
                    <td><span class="stamp stamp-<?= $st['class'] ?>"><?= $st['label'] ?></span></td>
                    <td>
                        <?php if (can($pdo, 'peminjaman.edit') && empty($row['tanggal_kembali'])): ?>
                            <a href="<?= url('/peminjaman/edit.php') ?>?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                        <?php endif; ?>
                        <?php if (can($pdo, 'pengembalian.process') && empty($row['tanggal_kembali'])): ?>
                            <a href="<?= url('/pengembalian/index.php') ?>?kembalikan=<?= (int) $row['id'] ?>" class="btn btn-sm btn-success"><i class="bi bi-arrow-return-left"></i> Kembalikan</a>
                        <?php endif; ?>
                        <?php if (can($pdo, 'peminjaman.delete')): ?>
                            <a href="<?= url('/peminjaman/hapus.php') ?>?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Hapus data peminjaman ini?')"><i class="bi bi-trash"></i> Hapus</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
