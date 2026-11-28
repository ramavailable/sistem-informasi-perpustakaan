<?php
/** Manajemen User — daftar user (khusus Admin). */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'users.manage');

$title = 'Manajemen User';
$users = $pdo->query(
    'SELECT u.*, r.name AS role_name, a.nama AS nama_anggota
     FROM users u
     JOIN roles r ON r.id = u.role_id
     LEFT JOIN anggota a ON a.id = u.anggota_id
     ORDER BY u.name'
)->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="mb-3 text-end">
    <a href="<?= url('/users/tambah.php') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah User</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead><tr><th>#</th><th>Nama</th><th>Username</th><th>Role</th><th>Terhubung ke Anggota</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (empty($users)): ?>
                <tr><td colspan="6"><div class="empty-state">
                    <p>Belum ada user selain Anda.</p>
                    <p><a href="<?= url('/users/tambah.php') ?>" class="btn btn-primary btn-sm">Tambah User</a></p>
                </div></td></tr>
            <?php else: ?>
                <?php foreach ($users as $i => $row): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($row['name']) ?></td>
                    <td><?= e($row['username']) ?></td>
                    <td><strong><?= e($row['role_name']) ?></strong></td>
                    <td><?= e($row['nama_anggota'] ?? '-') ?></td>
                    <td>
                        <a href="<?= url('/users/edit.php') ?>?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                        <?php if ((int) $row['id'] !== (int) currentUser()['id']): ?>
                            <a href="<?= url('/users/hapus.php') ?>?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Hapus user &quot;<?= e($row['username']) ?>&quot;?')"><i class="bi bi-trash"></i> Hapus</a>
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
