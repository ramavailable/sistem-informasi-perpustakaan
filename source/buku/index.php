<?php
/** Data Buku — daftar + pencarian. */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'buku.view');

$title = 'Data Buku';
$cari = trim($_GET['cari'] ?? '');

if ($cari !== '') {
    $stmt = $pdo->prepare(
        'SELECT b.*, k.nama AS nama_kategori FROM buku b
         LEFT JOIN kategori k ON k.id = b.kategori_id
         WHERE b.judul LIKE ? OR b.penulis LIKE ? OR k.nama LIKE ?
         ORDER BY b.judul'
    );
    $stmt->execute(["%$cari%", "%$cari%", "%$cari%"]);
} else {
    $stmt = $pdo->query(
        'SELECT b.*, k.nama AS nama_kategori FROM buku b
         LEFT JOIN kategori k ON k.id = b.kategori_id
         ORDER BY b.judul'
    );
}
$buku = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <form class="d-flex" method="get" action="<?= url('/buku/index.php') ?>" style="max-width: 420px;">
        <input type="search" name="cari" class="form-control me-2" placeholder="Cari judul / penulis / kategori" value="<?= e($cari) ?>">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Cari</button>
    </form>
    <?php if (can($pdo, 'buku.create')): ?>
        <a href="<?= url('/buku/tambah.php') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Buku</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead><tr>
                <th>#</th><th>Judul</th><th>Penulis</th><th>Penerbit</th>
                <th>Tahun</th><th>Kategori</th><th>Stok</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            <?php if (empty($buku)): ?>
                <tr><td colspan="8"><div class="empty-state">
                    <p>Belum ada buku di katalog.</p>
                    <?php if (can($pdo, 'buku.create')): ?>
                        <p><a href="<?= url('/buku/tambah.php') ?>" class="btn btn-primary btn-sm">Tambah Buku</a></p>
                    <?php endif; ?>
                </div></td></tr>
            <?php else: ?>
                <?php foreach ($buku as $i => $row): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($row['judul']) ?></td>
                    <td><?= e($row['penulis']) ?></td>
                    <td><?= e($row['penerbit']) ?></td>
                    <td><?= e($row['tahun']) ?></td>
                    <td><?= e($row['nama_kategori']) ?></td>
                    <td><?php if ((int) $row['stok'] > 0): ?><span class="mono"><?= (int) $row['stok'] ?></span> <span class="text-muted small">tersedia</span><?php else: ?><span class="stamp stamp-danger">Habis</span><?php endif; ?></td>
                    <td>
                        <?php if (can($pdo, 'buku.edit')): ?>
                            <a href="<?= url('/buku/edit.php') ?>?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                        <?php endif; ?>
                        <?php if (can($pdo, 'buku.delete')): ?>
                            <a href="<?= url('/buku/hapus.php') ?>?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Hapus buku &quot;<?= e($row['judul']) ?>&quot;?')"><i class="bi bi-trash"></i> Hapus</a>
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
