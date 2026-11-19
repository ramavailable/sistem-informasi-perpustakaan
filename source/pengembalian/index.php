<?php
/**
 * Pengembalian — daftar peminjaman aktif + proses pengembalian.
 * Denda = hari terlambat x DENDA_PER_HARI (Rp1.000/hari), dihitung otomatis.
 * Stok buku bertambah 1 saat buku dikembalikan.
 */
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
requirePermission($pdo, 'pengembalian.process');

$title = 'Pengembalian Buku';
$pesan = '';

// Proses pengembalian (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kembalikan_id'])) {
    $pid = (int) $_POST['kembalikan_id'];
    $tglKembali = $_POST['tanggal_kembali'] ?? date('Y-m-d');

    $stmt = $pdo->prepare('SELECT * FROM peminjaman WHERE id = ? AND tanggal_kembali IS NULL');
    $stmt->execute([$pid]);
    $pinjam = $stmt->fetch();

    if (!$pinjam) {
        $pesan = 'Data peminjaman tidak ditemukan atau sudah dikembalikan.';
    } else {
        $denda = hitungDenda($pinjam['tanggal_jatuh_tempo'], $tglKembali);
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                "UPDATE peminjaman SET tanggal_kembali = ?, status = 'dikembalikan', denda = ? WHERE id = ?"
            );
            $stmt->execute([$tglKembali, $denda, $pid]);
            $pdo->prepare('UPDATE buku SET stok = stok + 1 WHERE id = ?')->execute([$pinjam['buku_id']]);
            $pdo->commit();
            setFlash('success', 'Buku berhasil dikembalikan. Denda: ' . rupiah($denda) . '.');
            redirect('/pengembalian/index.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $pesan = 'Gagal memproses pengembalian.';
        }
    }
}

// Peminjaman yang masih aktif (belum dikembalikan)
$aktif = $pdo->query(
    'SELECT p.*, a.nama AS nama_anggota, a.nomor_anggota, b.judul AS judul_buku
     FROM peminjaman p
     JOIN anggota a ON a.id = p.anggota_id
     JOIN buku b ON b.id = p.buku_id
     WHERE p.tanggal_kembali IS NULL
     ORDER BY p.tanggal_jatuh_tempo ASC'
)->fetchAll();

// Riwayat pengembalian terbaru
$riwayat = $pdo->query(
    'SELECT p.*, a.nama AS nama_anggota, b.judul AS judul_buku
     FROM peminjaman p
     JOIN anggota a ON a.id = p.anggota_id
     JOIN buku b ON b.id = p.buku_id
     WHERE p.tanggal_kembali IS NOT NULL
     ORDER BY p.tanggal_kembali DESC LIMIT 10'
)->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<?php if ($pesan): ?><div class="alert alert-danger"><?= e($pesan) ?></div><?php endif; ?>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-arrow-return-left"></i> Peminjaman Aktif — Siap Dikembalikan</div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead><tr>
                <th>Anggota</th><th>Buku</th><th>Tgl Pinjam</th><th>Jatuh Tempo</th>
                <th>Status</th><th>Denda Jika Kembali Hari Ini</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            <?php if (empty($aktif)): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada peminjaman aktif.</td></tr>
            <?php else: ?>
                <?php foreach ($aktif as $row): $st = statusPeminjaman($row); $dendaHariIni = hitungDenda($row['tanggal_jatuh_tempo'], date('Y-m-d')); ?>
                <tr>
                    <td><?= e($row['nama_anggota']) ?><br><small class="text-muted"><?= e($row['nomor_anggota']) ?></small></td>
                    <td><?= e($row['judul_buku']) ?></td>
                    <td><?= tglIndo($row['tanggal_pinjam']) ?></td>
                    <td><?= tglIndo($row['tanggal_jatuh_tempo']) ?></td>
                    <td><span class="badge bg-<?= $st['class'] ?>"><?= $st['label'] ?></span></td>
                    <td><?= rupiah($dendaHariIni) ?></td>
                    <td>
                        <form method="post" action="<?= url('/pengembalian/index.php') ?>" class="d-flex gap-1">
                            <input type="hidden" name="kembalikan_id" value="<?= (int) $row['id'] ?>">
                            <input type="date" name="tanggal_kembali" class="form-control form-control-sm" style="width: 150px;" value="<?= date('Y-m-d') ?>" required>
                            <button type="submit" class="btn btn-sm btn-success"
                                    onclick="return confirm('Proses pengembalian buku &quot;<?= e($row['judul_buku']) ?>&quot;? Denda: <?= rupiah($dendaHariIni) ?>')">
                                <i class="bi bi-check-lg"></i> Kembalikan
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted small">
        Denda keterlambatan: <?= rupiah(DENDA_PER_HARI) ?> per hari, dihitung dari tanggal jatuh tempo sampai tanggal kembali.
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-clock-history"></i> Riwayat Pengembalian Terbaru</div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>Anggota</th><th>Buku</th><th>Tgl Kembali</th><th>Denda</th></tr></thead>
            <tbody>
            <?php if (empty($riwayat)): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">Belum ada pengembalian.</td></tr>
            <?php else: ?>
                <?php foreach ($riwayat as $row): ?>
                <tr>
                    <td><?= e($row['nama_anggota']) ?></td>
                    <td><?= e($row['judul_buku']) ?></td>
                    <td><?= tglIndo($row['tanggal_kembali']) ?></td>
                    <td><?= rupiah($row['denda']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
