<?php
/** Edit Buku. */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'buku.edit');

$title = 'Edit Buku';
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM buku WHERE id = ?');
$stmt->execute([$id]);
$buku = $stmt->fetch();
if (!$buku) {
    setFlash('danger', 'Data buku tidak ditemukan.');
    redirect('/buku/index.php');
}

$kategori = $pdo->query('SELECT * FROM kategori ORDER BY nama')->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul       = trim($_POST['judul'] ?? '');
    $penulis     = trim($_POST['penulis'] ?? '');
    $penerbit    = trim($_POST['penerbit'] ?? '');
    $tahun       = (int) ($_POST['tahun'] ?? 0);
    $kategori_id = (int) ($_POST['kategori_id'] ?? 0);
    $stok        = (int) ($_POST['stok'] ?? 0);

    if ($judul === '' || $penulis === '') {
        $error = 'Judul dan penulis wajib diisi.';
    } elseif ($stok < 0) {
        $error = 'Stok tidak boleh negatif.';
    } else {
        $stmt = $pdo->prepare(
            'UPDATE buku SET judul = ?, penulis = ?, penerbit = ?, tahun = ?, kategori_id = ?, stok = ? WHERE id = ?'
        );
        $stmt->execute([$judul, $penulis, $penerbit ?: null, $tahun ?: null, $kategori_id ?: null, $stok, $id]);
        setFlash('success', 'Buku "' . $judul . '" berhasil diubah.');
        redirect('/buku/index.php');
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="card"><div class="card-body">
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= url('/buku/edit.php') ?>?id=<?= $id ?>">
        <div class="mb-3">
            <label class="form-label">Judul *</label>
            <input type="text" name="judul" class="form-control" required value="<?= e($_POST['judul'] ?? $buku['judul']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Penulis *</label>
            <input type="text" name="penulis" class="form-control" required value="<?= e($_POST['penulis'] ?? $buku['penulis']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Penerbit</label>
            <input type="text" name="penerbit" class="form-control" value="<?= e($_POST['penerbit'] ?? $buku['penerbit']) ?>">
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Tahun Terbit</label>
                <input type="number" name="tahun" class="form-control" min="1900" max="<?= date('Y') ?>" value="<?= e($_POST['tahun'] ?? $buku['tahun']) ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Kategori</label>
                <select name="kategori_id" class="form-select">
                    <option value="">— Pilih Kategori —</option>
                    <?php foreach ($kategori as $k): ?>
                        <option value="<?= (int) $k['id'] ?>" <?= ((int) ($_POST['kategori_id'] ?? $buku['kategori_id']) === (int) $k['id']) ? 'selected' : '' ?>><?= e($k['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Stok *</label>
                <input type="number" name="stok" class="form-control" min="0" required value="<?= e($_POST['stok'] ?? $buku['stok']) ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
        <a href="<?= url('/buku/index.php') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
