<?php
/** Role & Hak Akses (RBAC) — daftar role dan jumlah permission-nya (khusus Admin). */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'roles.manage');

$title = 'Role & Hak Akses (RBAC)';
$roles = $pdo->query(
    'SELECT r.*, COUNT(rp.permission_id) AS jumlah_permission
     FROM roles r
     LEFT JOIN role_permissions rp ON rp.role_id = r.id
     GROUP BY r.id ORDER BY r.id'
)->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<p class="text-muted">
    Setiap role memiliki sekumpulan hak akses (permission). Permission dicek di sisi server pada
    setiap halaman melalui <code>requirePermission()</code>, jadi menu yang disembunyikan saja tidak cukup —
    akses langsung ke URL tanpa izin akan ditolak (403).
</p>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead><tr><th>#</th><th>Role</th><th>Deskripsi</th><th>Jumlah Permission</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($roles as $i => $row): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><span class="badge bg-primary"><?= e($row['name']) ?></span></td>
                    <td><?= e($row['description']) ?></td>
                    <td><?= (int) $row['jumlah_permission'] ?> permission</td>
                    <td>
                        <a href="<?= url('/roles/edit.php') ?>?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-warning">
                            <i class="bi bi-shield-lock"></i> Atur Permission
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
