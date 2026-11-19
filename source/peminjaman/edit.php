<?php
/** Edit Peminjaman (hanya yang masih dipinjam). */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'peminjaman.edit');

$title = 'Edit Peminjaman';
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM peminjaman WHERE id = ?');
$stmt->execute([$id]);
$pinjam = $stmt->fetch();
if (!$pinjam || !empty($pinjam['tanggal_kembali'])) {
    setFlash('danger', 'Data peminjaman tidak ditemukan atau sudah dikembalikan.');
    redirect('/peminjaman/index.php');
}

$anggota = $pdo->query('SELECT * FROM anggota ORDER BY nama')->fetchAll();
$buku = $pdo->query('SELECT * FROM buku ORDER BY judul')->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anggota_id     = (int) ($_POST['anggota_id'] ?? 0);
    $buku_id        = (int) ($_POST['buku_id'] ?? 0);
    $tanggal_pinjam = $_POST['tanggal_pinjam'] ?? '';
    $tanggal_tempo  = $_POST['tanggal_jatuh_tempo'] ?? '';

    if ($anggota_id <= 0 || $buku_id <= 0) {
        $error = 'Anggota dan buku wajib dipilih.';
    } elseif ($tanggal_tempo < $tanggal_pinjam) {
        $error = 'Tanggal jatuh tempo tidak boleh sebelum tanggal pinjam.';
    } else {
        $pdo->beginTransaction();
        try {
            // Jika buku diganti, kembalikan stok buku lama dan kurangi stok buku baru.
            if ($buku_id !== (int) $pinjam['buku_id']) {
                $stmt = $pdo->prepare('SELECT stok FROM buku WHERE id = ? FOR UPDATE');
                $stmt->execute([$buku_id]);
                $b = $stmt->fetch();
                if (!$b || (int) $b['stok'] <= 0) {
                    throw new Exception('Stok buku yang dipilih habis.');
                }
                $pdo->prepare('UPDATE buku SET stok = stok + 1 WHERE id = ?')->execute([$pinjam['buku_id']]);
                $pdo->prepare('UPDATE buku SET stok = stok - 1 WHERE id = ?')->execute([$buku_id]);
            }
            $stmt = $pdo->prepare(
                'UPDATE peminjaman SET anggota_id = ?, buku_id = ?, tanggal_pinjam = ?, tanggal_jatuh_tempo = ? WHERE id = ?'
            );
            $stmt->execute([$anggota_id, $buku_id, $tanggal_pinjam, $tanggal_tempo, $id]);
            $pdo->commit();
            setFlash('success', 'Data peminjaman berhasil diubah.');
            redirect('/peminjaman/index.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="card"><div class="card-body">
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= url('/peminjaman/edit.php') ?>?id=<?= $id ?>">
        <div class="mb-3">
            <label class="form-label">Anggota *</label>
            <select name="anggota_id" class="form-select" required>
                <?php foreach ($anggota as $a): ?>
                    <option value="<?= (int) $a['id'] ?>" <?= ((int) ($_POST['anggota_id'] ?? $pinjam['anggota_id']) === (int) $a['id']) ? 'selected' : '' ?>><?= e($a['nama']) ?> (<?= e($a['nomor_anggota']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Buku *</label>
            <select name="buku_id" class="form-select" required>
                <?php foreach ($buku as $b): ?>
                    <option value="<?= (int) $b['id'] ?>" <?= ((int) ($_POST['buku_id'] ?? $pinjam['buku_id']) === (int) $b['id']) ? 'selected' : '' ?>><?= e($b['judul']) ?> (stok: <?= (int) $b['stok'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Pinjam *</label>
                <input type="date" name="tanggal_pinjam" class="form-control" required value="<?= e($_POST['tanggal_pinjam'] ?? $pinjam['tanggal_pinjam']) ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Jatuh Tempo *</label>
                <input type="date" name="tanggal_jatuh_tempo" class="form-control" required value="<?= e($_POST['tanggal_jatuh_tempo'] ?? $pinjam['tanggal_jatuh_tempo']) ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
        <a href="<?= url('/peminjaman/index.php') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
