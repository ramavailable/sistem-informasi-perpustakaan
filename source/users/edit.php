<?php
/** Edit User (khusus Admin). Kosongkan password jika tidak ingin mengubahnya. */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'users.manage');

$title = 'Edit User';
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$userEdit = $stmt->fetch();
if (!$userEdit) {
    setFlash('danger', 'Data user tidak ditemukan.');
    redirect('/users/index.php');
}

$roles = $pdo->query('SELECT * FROM roles ORDER BY id')->fetchAll();
$anggota = $pdo->query('SELECT * FROM anggota ORDER BY nama')->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $role_id    = (int) ($_POST['role_id'] ?? 0);
    $anggota_id = (int) ($_POST['anggota_id'] ?? 0);

    if ($name === '' || $username === '' || $role_id <= 0) {
        $error = 'Nama, username, dan role wajib diisi.';
    } elseif ($password !== '' && strlen($password) < 6) {
        $error = 'Password baru minimal 6 karakter.';
    } else {
        $cek = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? AND id <> ?');
        $cek->execute([$username, $id]);
        if ((int) $cek->fetchColumn() > 0) {
            $error = 'Username "' . $username . '" sudah dipakai user lain.';
        } else {
            if ($password !== '') {
                $stmt = $pdo->prepare(
                    'UPDATE users SET name = ?, username = ?, password = ?, role_id = ?, anggota_id = ? WHERE id = ?'
                );
                $stmt->execute([
                    $name, $username, password_hash($password, PASSWORD_DEFAULT),
                    $role_id, $anggota_id ?: null, $id,
                ]);
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE users SET name = ?, username = ?, role_id = ?, anggota_id = ? WHERE id = ?'
                );
                $stmt->execute([$name, $username, $role_id, $anggota_id ?: null, $id]);
            }
            setFlash('success', 'User "' . $username . '" berhasil diubah.');
            redirect('/users/index.php');
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="card"><div class="card-body">
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= url('/users/edit.php') ?>?id=<?= $id ?>">
        <div class="mb-3">
            <label class="form-label">Nama Lengkap *</label>
            <input type="text" name="name" class="form-control" required value="<?= e($_POST['name'] ?? $userEdit['name']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Username *</label>
            <input type="text" name="username" class="form-control" required value="<?= e($_POST['username'] ?? $userEdit['username']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Password Baru</label>
            <input type="password" name="password" class="form-control">
            <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Role *</label>
            <select name="role_id" class="form-select" required>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= (int) $r['id'] ?>" <?= ((int) ($_POST['role_id'] ?? $userEdit['role_id']) === (int) $r['id']) ? 'selected' : '' ?>><?= e($r['name']) ?> (<?= e($r['description']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Terhubung ke Data Anggota (opsional)</label>
            <select name="anggota_id" class="form-select">
                <option value="">Tidak terhubung</option>
                <?php foreach ($anggota as $a): ?>
                    <option value="<?= (int) $a['id'] ?>" <?= ((int) ($_POST['anggota_id'] ?? $userEdit['anggota_id']) === (int) $a['id']) ? 'selected' : '' ?>><?= e($a['nama']) ?> (<?= e($a['nomor_anggota']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
        <a href="<?= url('/users/index.php') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
