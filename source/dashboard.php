<?php
/** Meja Pustakawan: yang kembali hari ini, yang terlambat, yang baru dipinjam. */
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
requirePermission($pdo, 'dashboard.view');

$user = currentUser();
$punyaAnggota = isRole('Anggota') && !empty($user['anggota_id']);
$paramsAnggota = $punyaAnggota ? [(int) $user['anggota_id']] : [];

$title = $punyaAnggota ? 'Pinjaman Saya' : 'Meja Pustakawan';
$subtitle = tglIndo(date('Y-m-d'));

// Angka ringkas (Admin & Pustakawan melihat semua data).
$totalBuku    = (int) $pdo->query('SELECT COUNT(*) FROM buku')->fetchColumn();
$totalAnggota = (int) $pdo->query('SELECT COUNT(*) FROM anggota')->fetchColumn();
$pinjamAktif  = (int) $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE tanggal_kembali IS NULL")->fetchColumn();
$terlambat    = (int) $pdo->query(
    "SELECT COUNT(*) FROM peminjaman WHERE tanggal_kembali IS NULL AND tanggal_jatuh_tempo < CURDATE()"
)->fetchColumn();
$totalDenda   = (int) $pdo->query('SELECT COALESCE(SUM(denda),0) FROM peminjaman')->fetchColumn();

// Statistik personal untuk Anggota.
if ($punyaAnggota) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE anggota_id = ? AND tanggal_kembali IS NULL");
    $stmt->execute([(int) $user['anggota_id']]);
    $pinjamAktif = (int) $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE anggota_id = ? AND tanggal_kembali IS NULL AND tanggal_jatuh_tempo < CURDATE()");
    $stmt->execute([(int) $user['anggota_id']]);
    $terlambat = (int) $stmt->fetchColumn();
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(denda),0) FROM peminjaman WHERE anggota_id = ?');
    $stmt->execute([(int) $user['anggota_id']]);
    $totalDenda = (int) $stmt->fetchColumn();
}

$baseSelect = 'SELECT p.*, a.nama AS nama_anggota, b.judul AS judul_buku
     FROM peminjaman p
     JOIN anggota a ON a.id = p.anggota_id
     JOIN buku b ON b.id = p.buku_id';

// Jatuh tempo hari ini, belum dikembalikan.
$stmt = $pdo->prepare($baseSelect . ' WHERE p.tanggal_kembali IS NULL AND p.tanggal_jatuh_tempo = CURDATE()'
    . ($punyaAnggota ? ' AND p.anggota_id = ?' : '') . ' ORDER BY a.nama LIMIT 5');
$stmt->execute($paramsAnggota);
$hariIni = $stmt->fetchAll();
$hariIniTotal = (int) $pdo->query(
    'SELECT COUNT(*) FROM peminjaman WHERE tanggal_kembali IS NULL AND tanggal_jatuh_tempo = CURDATE()'
    . ($punyaAnggota ? ' AND anggota_id = ' . (int) $user['anggota_id'] : '')
)->fetchColumn();

// Terlambat, belum dikembalikan (paling lama dulu).
$stmt = $pdo->prepare($baseSelect . ' WHERE p.tanggal_kembali IS NULL AND p.tanggal_jatuh_tempo < CURDATE()'
    . ($punyaAnggota ? ' AND p.anggota_id = ?' : '') . ' ORDER BY p.tanggal_jatuh_tempo ASC LIMIT 5');
$stmt->execute($paramsAnggota);
$terlambatList = $stmt->fetchAll();

// Baru dipinjam (masih aktif).
$stmt = $pdo->prepare($baseSelect . ' WHERE p.tanggal_kembali IS NULL'
    . ($punyaAnggota ? ' AND p.anggota_id = ?' : '') . ' ORDER BY p.tanggal_pinjam DESC, p.created_at DESC LIMIT 5');
$stmt->execute($paramsAnggota);
$baruDipinjam = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="desk">
    <section class="card desk-col">
        <div class="card-header">Kembali hari ini <span class="desk-count"><?= $hariIniTotal ?></span></div>
        <div class="card-body">
            <?php if (empty($hariIni)): ?>
                <p class="desk-empty">Tidak ada buku yang jatuh tempo hari ini.</p>
            <?php else: ?>
                <?php foreach ($hariIni as $row): ?>
                <div class="desk-row">
                    <div class="desk-row-main">
                        <strong><?= e($punyaAnggota ? $row['judul_buku'] : $row['nama_anggota']) ?></strong>
                        <span class="desk-book"><?= e($punyaAnggota ? 'Jatuh tempo hari ini' : $row['judul_buku']) ?></span>
                    </div>
                    <?php if (!$punyaAnggota): ?>
                        <a href="<?= url('/pengembalian/index.php') ?>" class="btn btn-sm btn-outline-secondary">Proses</a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if ($hariIniTotal > count($hariIni)): ?>
                    <a href="<?= url('/pengembalian/index.php') ?>" class="desk-more">Lihat semua (<?= $hariIniTotal ?>)</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="card desk-col<?= $terlambat > 0 ? ' desk-col-hot' : '' ?>">
        <div class="card-header">Terlambat <span class="desk-count"><?= $terlambat ?></span></div>
        <div class="card-body">
            <?php if (empty($terlambatList)): ?>
                <p class="desk-empty">Tidak ada yang terlambat. Semua kembali tepat waktu.</p>
            <?php else: ?>
                <?php foreach ($terlambatList as $row): $dendaRow = hitungDenda($row['tanggal_jatuh_tempo'], date('Y-m-d')); ?>
                <div class="desk-row">
                    <div class="desk-row-main">
                        <strong><?= e($punyaAnggota ? $row['judul_buku'] : $row['nama_anggota']) ?></strong>
                        <?php if (!$punyaAnggota): ?>
                            <span class="desk-book"><?= e($row['judul_buku']) ?></span>
                        <?php endif; ?>
                        <span class="desk-book">Jatuh tempo <?= tglIndo($row['tanggal_jatuh_tempo']) ?></span>
                    </div>
                    <span class="desk-meta"><span class="denda"><?= rupiah($dendaRow) ?></span></span>
                </div>
                <?php endforeach; ?>
                <a href="<?= url('/peminjaman/index.php') ?>?status=terlambat" class="desk-more">Lihat semua yang terlambat</a>
            <?php endif; ?>
        </div>
    </section>

    <section class="card desk-col">
        <div class="card-header"><?= $punyaAnggota ? 'Sedang dipinjam' : 'Baru dipinjam' ?> <span class="desk-count"><?= $pinjamAktif ?></span></div>
        <div class="card-body">
            <?php if (empty($baruDipinjam)): ?>
                <p class="desk-empty"><?= $punyaAnggota ? 'Anda tidak sedang meminjam buku.' : 'Belum ada peminjaman aktif.' ?></p>
                <?php if (!$punyaAnggota && can($pdo, 'peminjaman.create')): ?>
                    <p><a href="<?= url('/peminjaman/tambah.php') ?>" class="btn btn-primary btn-sm">Tambah Peminjaman</a></p>
                <?php endif; ?>
            <?php else: ?>
                <?php foreach ($baruDipinjam as $row): ?>
                <div class="desk-row">
                    <div class="desk-row-main">
                        <strong><?= e($punyaAnggota ? $row['judul_buku'] : $row['nama_anggota']) ?></strong>
                        <span class="desk-book"><?= e($punyaAnggota ? 'Jatuh tempo ' . tglIndo($row['tanggal_jatuh_tempo']) : $row['judul_buku']) ?></span>
                    </div>
                    <span class="desk-meta"><?= tglIndo($row['tanggal_pinjam']) ?></span>
                </div>
                <?php endforeach; ?>
                <a href="<?= url('/peminjaman/index.php') ?>" class="desk-more">Lihat semua</a>
            <?php endif; ?>
        </div>
    </section>
</div>

<p class="desk-totals text-muted">
    <?php if ($punyaAnggota): ?>
        Koleksi <?= $totalBuku ?> buku. Anda meminjam <?= $pinjamAktif ?> buku, <?= $terlambat ?> terlambat, total denda <?= rupiah($totalDenda) ?>.
    <?php else: ?>
        Koleksi <?= $totalBuku ?> buku, <?= $totalAnggota ?> anggota, <?= $pinjamAktif ?> sedang dipinjam, <?= $terlambat ?> terlambat.
    <?php endif; ?>
</p>

<?php require __DIR__ . '/includes/footer.php'; ?>
