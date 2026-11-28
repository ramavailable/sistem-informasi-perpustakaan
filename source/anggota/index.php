<?php
/** Data Anggota — daftar + pencarian. */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'anggota.view');

$title = 'Data Anggota';
$cari = trim($_GET['cari'] ?? '');

if ($cari !== '') {
    $stmt = $pdo->prepare(
        'SELECT * FROM anggota WHERE nama LIKE ? OR nomor_anggota LIKE ? OR telepon LIKE ? ORDER BY nama'
    );
    $stmt->execute(["%$cari%", "%$cari%", "%$cari%"]);
} else {
    $stmt = $pdo->query('SELECT * FROM anggota ORDER BY nama');
}
$anggota = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <form class="d-flex" method="get" action="<?= url('/anggota/index.php') ?>" style="max-width: 420px;">
        <input type="search" name="cari" class="form-control me-2" placeholder="Cari nama / nomor anggota / telepon" value="<?= e($cari) ?>">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Cari</button>
    </form>
    <?php if (can($pdo, 'anggota.create')): ?>
        <a href="<?= url('/anggota/tambah.php') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Anggota</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead><tr>
                <th>#</th><th>Nomor Anggota</th><th>Nama</th><th>Telepon</th><th>Alamat</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            <?php if (empty($anggota)): ?>
                <tr><td colspan="6"><div class="empty-state">
                    <p>Belum ada anggota yang terdaftar.</p>
                    <?php if (can($pdo, 'anggota.create')): ?>
                        <p><a href="<?= url('/anggota/tambah.php') ?>" class="btn btn-primary btn-sm">Tambah Anggota</a></p>
                    <?php endif; ?>
                </div></td></tr>
            <?php else: ?>
                <?php foreach ($anggota as $i => $row): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><span class="mono"><?= e($row['nomor_anggota']) ?></span></td>
                    <td><?= e($row['nama']) ?></td>
                    <td><?= e($row['telepon']) ?></td>
                    <td><?= e($row['alamat']) ?></td>
                    <td>
                        <?php if (can($pdo, 'anggota.edit')): ?>
                            <a href="<?= url('/anggota/edit.php') ?>?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                        <?php endif; ?>
                        <?php if (can($pdo, 'anggota.delete')): ?>
                            <a href="<?= url('/anggota/hapus.php') ?>?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Hapus anggota &quot;<?= e($row['nama']) ?>&quot;?')"><i class="bi bi-trash"></i> Hapus</a>
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
