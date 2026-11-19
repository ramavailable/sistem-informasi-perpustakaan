-- ============================================================
-- SISTEM INFORMASI PERPUSTAKAAN — Database (MySQL/MariaDB)
-- UAS — Universitas Pamulang, S1 Teknik Informatika
--
-- Cara impor:
--   mysql -u root -p < database.sql
--   atau lewat phpMyAdmin: pilih tab Import -> pilih file ini.
--
-- Setelah impor, atur koneksi di source/config/database.php
-- (default: host 127.0.0.1, database perpustakaan, user root, tanpa password).
-- ============================================================

CREATE DATABASE IF NOT EXISTS perpustakaan
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE perpustakaan;

-- ------------------------------------------------------------
-- Tabel roles — peran pengguna (Admin, Pustakawan, Anggota)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel permissions — daftar hak akses (permission)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel role_permissions — RBAC: permission apa saja yang
-- dimiliki setiap role (relasi many-to-many)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel kategori — kategori buku
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel buku
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    penulis VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100) NULL,
    tahun INT NULL,
    kategori_id INT NULL,
    stok INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel anggota
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS anggota (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_anggota VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    telepon VARCHAR(20) NULL,
    alamat TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel peminjaman
-- status: 'dipinjam' (masih dipinjam) / 'dikembalikan'
-- Tampilan "Terlambat" dihitung dinamis: masih dipinjam dan
-- hari ini sudah melewati tanggal_jatuh_tempo.
-- denda: rupiah, Rp1.000 per hari keterlambatan.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anggota_id INT NOT NULL,
    buku_id INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_jatuh_tempo DATE NOT NULL,
    tanggal_kembali DATE NULL,
    status ENUM('dipinjam','dikembalikan','terlambat') NOT NULL DEFAULT 'dipinjam',
    denda INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anggota_id) REFERENCES anggota (id),
    FOREIGN KEY (buku_id) REFERENCES buku (id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel users — akun login. Password disimpan sebagai hash
-- (password_hash / password_verify). Kolom anggota_id
-- menghubungkan akun role Anggota ke data anggotanya, supaya
-- ia hanya melihat peminjamannya sendiri.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    anggota_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles (id),
    FOREIGN KEY (anggota_id) REFERENCES anggota (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- DATA AWAL (SEED)
-- ============================================================

INSERT INTO roles (id, name, description) VALUES
(1, 'Admin', 'Mengelola seluruh sistem, user, dan hak akses'),
(2, 'Pustakawan', 'Mengelola buku, anggota, peminjaman, dan pengembalian'),
(3, 'Anggota', 'Melihat katalog buku dan riwayat peminjaman sendiri');

INSERT INTO permissions (id, name, description) VALUES
(1,  'dashboard.view',        'Melihat dashboard'),
(2,  'buku.view',             'Melihat data buku'),
(3,  'buku.create',           'Menambah data buku'),
(4,  'buku.edit',             'Mengubah data buku'),
(5,  'buku.delete',           'Menghapus data buku'),
(6,  'anggota.view',          'Melihat data anggota'),
(7,  'anggota.create',        'Menambah data anggota'),
(8,  'anggota.edit',          'Mengubah data anggota'),
(9,  'anggota.delete',        'Menghapus data anggota'),
(10, 'peminjaman.view',       'Melihat data peminjaman'),
(11, 'peminjaman.create',     'Mencatat peminjaman baru'),
(12, 'peminjaman.edit',       'Mengubah data peminjaman'),
(13, 'peminjaman.delete',     'Menghapus data peminjaman'),
(14, 'pengembalian.process',  'Memproses pengembalian buku dan denda'),
(15, 'users.manage',          'Mengelola akun user'),
(16, 'roles.manage',          'Mengelola role dan hak akses (RBAC)');

-- Admin: semua permission (16)
INSERT INTO role_permissions (role_id, permission_id) VALUES
(1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),(1,11),(1,12),(1,13),(1,14),(1,15),(1,16);

-- Pustakawan: dashboard, buku, anggota, peminjaman, pengembalian (14)
INSERT INTO role_permissions (role_id, permission_id) VALUES
(2,1),(2,2),(2,3),(2,4),(2,5),(2,6),(2,7),(2,8),(2,9),(2,10),(2,11),(2,12),(2,13),(2,14);

-- Anggota: hanya lihat dashboard, katalog buku, dan peminjaman sendiri (3)
INSERT INTO role_permissions (role_id, permission_id) VALUES
(3,1),(3,2),(3,10);

INSERT INTO kategori (id, nama) VALUES
(1, 'Fiksi'),
(2, 'Non-Fiksi'),
(3, 'Teknologi'),
(4, 'Sains'),
(5, 'Sejarah'),
(6, 'Pendidikan'),
(7, 'Agama'),
(8, 'Komik');

INSERT INTO buku (id, judul, penulis, penerbit, tahun, kategori_id, stok) VALUES
(1,  'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 1, 4),
(2,  'Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, 1, 2),
(3,  'Clean Code', 'Robert C. Martin', 'Prentice Hall', 2008, 3, 4),
(4,  'Pemrograman Web dengan PHP dan MySQL', 'Budi Raharjo', 'Informatika', 2019, 3, 5),
(5,  'Algoritma dan Pemrograman', 'Rinaldi Munir', 'Informatika', 2011, 6, 6),
(6,  'Basis Data', 'Fathansyah', 'Informatika', 2018, 3, 3),
(7,  'Fisika Dasar', 'Halliday, Resnick, Walker', 'Erlangga', 2010, 4, 4),
(8,  'Sejarah Indonesia Modern', 'M.C. Ricklefs', 'Serambi', 2008, 5, 2),
(9,  'Petualangan Tintin: Rahasia Unicorn', 'Hergé', 'Gramedia', 2011, 8, 5),
(10, 'Manajemen Keuangan', 'Brigham & Houston', 'Salemba Empat', 2018, 2, 3);

INSERT INTO anggota (id, nomor_anggota, nama, telepon, alamat) VALUES
(1, 'AG-001', 'Budi Santoso', '081234567890', 'Jl. Merdeka No. 10, Bogor'),
(2, 'AG-002', 'Siti Aminah', '081298765432', 'Jl. Sudirman No. 25, Bogor'),
(3, 'AG-003', 'Andi Wijaya', '085678901234', 'Jl. Pajajaran No. 5, Bogor'),
(4, 'AG-004', 'Dewi Lestari', '082112345678', 'Jl. Raya Parung No. 88, Bogor'),
(5, 'AG-005', 'Rizky Pratama', '081377788899', 'Jl. Cibinong Raya No. 12, Bogor');

-- Contoh peminjaman (tanggal relatif terhadap hari impor, agar demo selalu segar):
-- 1) aktif, belum jatuh tempo | 2) aktif, sudah terlambat | 3) sudah dikembalikan + denda Rp2.000
INSERT INTO peminjaman (id, anggota_id, buku_id, tanggal_pinjam, tanggal_jatuh_tempo, tanggal_kembali, status, denda) VALUES
(1, 1, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY),  DATE_ADD(CURDATE(), INTERVAL 5 DAY),  NULL, 'dipinjam', 0),
(2, 4, 2, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 3 DAY),  NULL, 'dipinjam', 0),
(3, 2, 4, DATE_SUB(CURDATE(), INTERVAL 12 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY),  DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'dikembalikan', 2000);

-- ------------------------------------------------------------
-- AKUN DEMO (password asli: admin123 / pustaka123 / anggota123)
-- Hash di bawah ini adalah hash SHA-512 crypt ($6$) yang
-- dikenali dan diverifikasi oleh password_verify() di PHP.
-- User baru yang dibuat lewat menu Manajemen User memakai
-- password_hash() (bcrypt $2y$) seperti biasa.
-- ------------------------------------------------------------
INSERT INTO users (id, name, username, password, role_id, anggota_id) VALUES
(1, 'Administrator', 'admin', '$6$7NbvY7CApmHhbT/s$XhsnHsRQHIhvpbcpNtHEghM2i5GfQQTsPi303M3HU2Z4Xb8Wp5LR77VW62XT0D5Nr8cvA.iTyA/ZscbGejsry1', 1, NULL),
(2, 'Sari Pustakawan', 'pustakawan', '$6$VMoAGwY9Ut8In.vk$CqNVS0g/a/9nQz2jGZt.1ACH0FDmYSbyfoZ5PMzlkwkxy0.E4PsoTxbjlQPKsrrbGfkOKN3VUdrONWOWODd040', 2, NULL),
(3, 'Budi Santoso', 'anggota', '$6$MSyJpudoIjFowM/k$750kvYVURI1.wyi42b8Xw2UGHsV59R.EA9t0ikrYwUSJM.F5WKm4kyRj7Le2VytiizvSTv7TJFnMOBfdwT0hE/', 3, 1);
