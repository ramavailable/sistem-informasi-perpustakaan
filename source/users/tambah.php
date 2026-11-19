<?php
/** Tambah User (khusus Admin). */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'users.manage');

$title = 'Tambah User';
$roles = $pdo->query('SELECT * FROM roles ORDER BY id')->fetchAll();
$anggota = $pdo->query('SELECT * FROM anggota ORDER BY nama')->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $role_id    = (int) ($_POST['role_id'] ?? 0);
    $anggota_id = (int) ($_POST['anggota_id'] ?? 0);

    if ($name === '' || $username === '' || $password === '' || $role_id <= 0) {
        $error = 'Nama, username, password, dan role wajib diisi.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $cek = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        $cek->execute([$username]);
        if ((int) $cek->fetchColumn() > 0) {
            $error = 'Username "' . $username . '" sudah dipakai.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, username, password, role_id, anggota_id) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $name, $username, password_hash($password, PASSWORD_DEFAULT),
                $role_id, $anggota_id ?: null,
            ]);
            setFlash('success', 'User "' . $username . '" berhasil ditambahkan.');
            redirect('/users/index.php');
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="card"><div class="card-body">
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= url('/users/tambah.php') ?>">
        <div class="mb-3">
            <label class="form-label">Nama Lengkap *</label>
            <input type="text" name="name" class="form-control" required value="<?= e($_POST['name'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Username *</label>
            <input type="text" name="username" class="form-control" required value="<?= e($_POST['username'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control" required>
            <div class="form-text">Minimal 6 karakter. Password disimpan terenkripsi (password_hash).</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Role *</label>
            <select name="role_id" class="form-select" required>
                <option value="">— Pilih Role —</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= (int) $r['id'] ?>"><?= e($r['name']) ?> — <?= e($r['description']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Terhubung ke Data Anggota (opsional)</label>
            <select name="anggota_id" class="form-select">
                <option value="">— Tidak terhubung —</option>
                <?php foreach ($anggota as $a): ?>
                    <option value="<?= (int) $a['id'] ?>"><?= e($a['nama']) ?> (<?= e($a['nomor_anggota']) ?>)</option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Isi ini jika user adalah Anggota, agar ia hanya melihat peminjamannya sendiri.</div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
        <a href="<?= url('/users/index.php') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
