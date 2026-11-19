<?php
/** Edit Anggota. */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'anggota.edit');

$title = 'Edit Anggota';
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM anggota WHERE id = ?');
$stmt->execute([$id]);
$anggota = $stmt->fetch();
if (!$anggota) {
    setFlash('danger', 'Data anggota tidak ditemukan.');
    redirect('/anggota/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat  = trim($_POST['alamat'] ?? '');

    if ($nama === '') {
        $error = 'Nama wajib diisi.';
    } else {
        $stmt = $pdo->prepare('UPDATE anggota SET nama = ?, telepon = ?, alamat = ? WHERE id = ?');
        $stmt->execute([$nama, $telepon ?: null, $alamat ?: null, $id]);
        setFlash('success', 'Data anggota "' . $nama . '" berhasil diubah.');
        redirect('/anggota/index.php');
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="card"><div class="card-body">
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= url('/anggota/edit.php') ?>?id=<?= $id ?>">
        <div class="mb-3">
            <label class="form-label">Nomor Anggota</label>
            <input type="text" class="form-control" value="<?= e($anggota['nomor_anggota']) ?>" disabled>
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Lengkap *</label>
            <input type="text" name="nama" class="form-control" required value="<?= e($_POST['nama'] ?? $anggota['nama']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">No. Telepon / HP</label>
            <input type="text" name="telepon" class="form-control" value="<?= e($_POST['telepon'] ?? $anggota['telepon']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-control" rows="3"><?= e($_POST['alamat'] ?? $anggota['alamat']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
        <a href="<?= url('/anggota/index.php') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
