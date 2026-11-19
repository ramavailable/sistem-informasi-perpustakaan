<?php
/** Atur Permission sebuah Role (khusus Admin). */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'roles.manage');

$title = 'Atur Permission Role';
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM roles WHERE id = ?');
$stmt->execute([$id]);
$role = $stmt->fetch();
if (!$role) {
    setFlash('danger', 'Role tidak ditemukan.');
    redirect('/roles/index.php');
}

$permissions = $pdo->query('SELECT * FROM permissions ORDER BY id')->fetchAll();
$stmt = $pdo->prepare('SELECT permission_id FROM role_permissions WHERE role_id = ?');
$stmt->execute([$id]);
$aktif = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Kelompokkan permission berdasarkan modul (awalan sebelum titik)
$kelompok = [];
foreach ($permissions as $p) {
    $modul = explode('.', $p['name'])[0];
    $kelompok[$modul][] = $p;
}
$labelModul = [
    'dashboard'    => 'Dashboard',
    'buku'         => 'Data Buku',
    'anggota'      => 'Data Anggota',
    'peminjaman'   => 'Peminjaman',
    'pengembalian' => 'Pengembalian',
    'users'        => 'Manajemen User',
    'roles'        => 'Role & Hak Akses',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dipilih = array_map('intval', $_POST['permissions'] ?? []);
    $pdo->beginTransaction();
    try {
        $pdo->prepare('DELETE FROM role_permissions WHERE role_id = ?')->execute([$id]);
        $stmt = $pdo->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)');
        foreach ($dipilih as $pid) {
            $stmt->execute([$id, $pid]);
        }
        $pdo->commit();
        setFlash('success', 'Permission role "' . $role['name'] . '" berhasil disimpan.');
        redirect('/roles/index.php');
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('danger', 'Gagal menyimpan permission.');
        redirect('/roles/edit.php?id=' . $id);
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="card"><div class="card-body">
    <h5>Role: <span class="badge bg-primary"><?= e($role['name']) ?></span></h5>
    <p class="text-muted"><?= e($role['description']) ?></p>
    <form method="post" action="<?= url('/roles/edit.php') ?>?id=<?= $id ?>">
        <?php foreach ($kelompok as $modul => $daftar): ?>
            <h6 class="mt-4"><?= e($labelModul[$modul] ?? ucfirst($modul)) ?></h6>
            <div class="row">
                <?php foreach ($daftar as $p): ?>
                    <div class="col-md-4">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="permissions[]"
                                   value="<?= (int) $p['id'] ?>" id="perm-<?= (int) $p['id'] ?>"
                                   <?= in_array($p['id'], $aktif, false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="perm-<?= (int) $p['id'] ?>">
                                <code><?= e($p['name']) ?></code><br>
                                <small class="text-muted"><?= e($p['description']) ?></small>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Permission</button>
            <a href="<?= url('/roles/index.php') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
