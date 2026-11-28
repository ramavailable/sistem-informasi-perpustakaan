<?php
/** Tambah Peminjaman. Stok buku berkurang 1 saat peminjaman dibuat. */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'peminjaman.create');

$title = 'Tambah Peminjaman';
$anggota = $pdo->query('SELECT * FROM anggota ORDER BY nama')->fetchAll();
$buku = $pdo->query('SELECT * FROM buku WHERE stok > 0 ORDER BY judul')->fetchAll();
$error = '';

$defaultPinjam = date('Y-m-d');
$defaultTempo = date('Y-m-d', strtotime('+' . LAMA_PINJAM_HARI . ' days'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anggota_id       = (int) ($_POST['anggota_id'] ?? 0);
    $buku_id          = (int) ($_POST['buku_id'] ?? 0);
    $tanggal_pinjam   = $_POST['tanggal_pinjam'] ?? $defaultPinjam;
    $tanggal_tempo    = $_POST['tanggal_jatuh_tempo'] ?? $defaultTempo;

    if ($anggota_id <= 0 || $buku_id <= 0) {
        $error = 'Anggota dan buku wajib dipilih.';
    } elseif ($tanggal_tempo < $tanggal_pinjam) {
        $error = 'Tanggal jatuh tempo tidak boleh sebelum tanggal pinjam.';
    } else {
        // Cek stok buku (kunci baris agar tidak race condition)
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT stok, judul FROM buku WHERE id = ? FOR UPDATE');
            $stmt->execute([$buku_id]);
            $b = $stmt->fetch();
            if (!$b) {
                throw new Exception('Buku tidak ditemukan.');
            }
            if ((int) $b['stok'] <= 0) {
                throw new Exception('Stok buku "' . $b['judul'] . '" habis.');
            }
            $stmt = $pdo->prepare(
                'INSERT INTO peminjaman (anggota_id, buku_id, tanggal_pinjam, tanggal_jatuh_tempo) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$anggota_id, $buku_id, $tanggal_pinjam, $tanggal_tempo]);
            $pdo->prepare('UPDATE buku SET stok = stok - 1 WHERE id = ?')->execute([$buku_id]);
            $pdo->commit();
            setFlash('success', 'Peminjaman berhasil dicatat. Stok buku berkurang 1.');
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
    <?php if (empty($buku)): ?>
        <div class="alert alert-warning">Semua buku sedang habis stok. Tambah stok buku terlebih dahulu di menu Data Buku.</div>
    <?php endif; ?>
    <form method="post" action="<?= url('/peminjaman/tambah.php') ?>">
        <div class="mb-3">
            <label class="form-label">Anggota *</label>
            <select name="anggota_id" class="form-select" required>
                <option value="">Pilih Anggota</option>
                <?php foreach ($anggota as $a): ?>
                    <option value="<?= (int) $a['id'] ?>"><?= e($a['nama']) ?> (<?= e($a['nomor_anggota']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Buku *</label>
            <select name="buku_id" class="form-select" required>
                <option value="">Pilih Buku</option>
                <?php foreach ($buku as $b): ?>
                    <option value="<?= (int) $b['id'] ?>"><?= e($b['judul']) ?>, <?= e($b['penulis']) ?> (stok: <?= (int) $b['stok'] ?>)</option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Hanya buku yang stoknya tersedia yang ditampilkan.</div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Pinjam *</label>
                <input type="date" name="tanggal_pinjam" class="form-control" required value="<?= e($_POST['tanggal_pinjam'] ?? $defaultPinjam) ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Jatuh Tempo *</label>
                <input type="date" name="tanggal_jatuh_tempo" class="form-control" required value="<?= e($_POST['tanggal_jatuh_tempo'] ?? $defaultTempo) ?>">
                <div class="form-text">Default <?= LAMA_PINJAM_HARI ?> hari setelah tanggal pinjam.</div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
        <a href="<?= url('/peminjaman/index.php') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
