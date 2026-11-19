<?php
/** Tambah Anggota. Nomor anggota dibuat otomatis (AG-001, AG-002, ...). */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'anggota.create');

$title = 'Tambah Anggota';
$error = '';

// Nomor anggota berikutnya
$last = $pdo->query("SELECT nomor_anggota FROM anggota ORDER BY id DESC LIMIT 1")->fetchColumn();
$nextNum = $last ? (int) substr($last, 3) + 1 : 1;
$nomorBaru = 'AG-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat  = trim($_POST['alamat'] ?? '');

    if ($nama === '') {
        $error = 'Nama wajib diisi.';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO anggota (nomor_anggota, nama, telepon, alamat) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$nomorBaru, $nama, $telepon ?: null, $alamat ?: null]);
        setFlash('success', 'Anggota "' . $nama . '" (' . $nomorBaru . ') berhasil ditambahkan.');
        redirect('/anggota/index.php');
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="card"><div class="card-body">
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= url('/anggota/tambah.php') ?>">
        <div class="mb-3">
            <label class="form-label">Nomor Anggota</label>
            <input type="text" class="form-control" value="<?= e($nomorBaru) ?>" disabled>
            <div class="form-text">Nomor anggota dibuat otomatis oleh sistem.</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Lengkap *</label>
            <input type="text" name="nama" class="form-control" required value="<?= e($_POST['nama'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">No. Telepon / HP</label>
            <input type="text" name="telepon" class="form-control" value="<?= e($_POST['telepon'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-control" rows="3"><?= e($_POST['alamat'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
        <a href="<?= url('/anggota/index.php') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
