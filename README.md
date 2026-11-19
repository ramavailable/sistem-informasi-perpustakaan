# Sistem Informasi Perpustakaan

Aplikasi web untuk mengelola perpustakaan: data buku, anggota, peminjaman,
pengembalian beserta denda keterlambatan, dengan **3 role dan RBAC
(Role-Based Access Control)**.

Dibuat untuk **UAS — S1 Teknik Informatika, Universitas Pamulang**.
Bahasa antarmuka: **Indonesia**.

## Teknologi

- PHP Native (tanpa framework) + PDO MySQL
- MySQL / MariaDB
- Bootstrap 5 (CDN) + Bootstrap Icons
- Login memakai PHP Session, password memakai `password_hash` / `password_verify`

## Fitur

| Fitur | Keterangan |
|---|---|
| Dashboard | Total buku, anggota, peminjaman aktif, terlambat, dan total denda; daftar peminjaman terbaru |
| Data Buku (CRUD) | Judul, penulis, penerbit, tahun terbit, kategori, stok; pencarian |
| Data Anggota (CRUD) | Nama, nomor anggota otomatis (AG-001, AG-002, ...), telepon, alamat |
| Peminjaman (CRUD) | Anggota, buku (hanya yang stoknya tersedia), tanggal pinjam, jatuh tempo (default 7 hari); stok buku berkurang otomatis |
| Pengembalian | Proses pengembalian + hitung denda otomatis **Rp1.000/hari** keterlambatan; stok buku bertambah kembali |
| Manajemen User (Admin) | Tambah / edit / hapus akun, atur role, hubungkan akun Anggota ke data anggotanya |
| Role & Hak Akses (Admin) | Atur permission tiap role (RBAC) lewat tabel `roles`, `permissions`, `role_permissions` |

## Role & Hak Akses (RBAC)

Permission dicek **di sisi server pada setiap halaman** lewat fungsi
`requirePermission()` (bukan hanya menyembunyikan menu). Akses langsung ke URL
tanpa izin akan ditolak dengan halaman 403.

| Permission | Admin | Pustakawan | Anggota |
|---|---|---|---|
| dashboard.view | ✅ | ✅ | ✅ |
| buku.view | ✅ | ✅ | ✅ |
| buku.create / edit / delete | ✅ | ✅ | — |
| anggota.view | ✅ | ✅ | — |
| anggota.create / edit / delete | ✅ | ✅ | — |
| peminjaman.view | ✅ | ✅ | ✅ (milik sendiri) |
| peminjaman.create / edit / delete | ✅ | ✅ | — |
| pengembalian.process | ✅ | ✅ | — |
| users.manage | ✅ | — | — |
| roles.manage | ✅ | — | — |

## Akun Demo

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `admin123` |
| Pustakawan | `pustakawan` | `pustaka123` |
| Anggota | `anggota` | `anggota123` |

Akun Anggota terhubung ke data anggota **Budi Santoso (AG-001)**, jadi ia hanya
melihat peminjamannya sendiri di Dashboard dan menu Peminjaman.

## Struktur Folder

```
uas-perpustakaan/
├── database.sql            # Skema database + data awal (seed)
├── README.md               # File ini
└── source/                 # Upload isi folder ini ke hosting (public_html)
    ├── index.php           # Redirect ke login / dashboard
    ├── login.php
    ├── logout.php
    ├── dashboard.php
    ├── config/
    │   └── database.php    # Pengaturan koneksi database
    ├── includes/
    │   ├── auth.php        # Session, RBAC, helper (wajib dibaca)
    │   ├── header.php      # Layout + sidebar
    │   └── footer.php
    ├── buku/               # CRUD buku: index, tambah, edit, hapus
    ├── anggota/            # CRUD anggota: index, tambah, edit, hapus
    ├── peminjaman/         # CRUD peminjaman: index, tambah, edit, hapus
    ├── pengembalian/       # Proses pengembalian + denda
    ├── users/              # Manajemen user (Admin)
    └── roles/              # Manajemen role & permission (Admin)
```

## Cara Instalasi (Lokal — XAMPP)

1. Install [XAMPP](https://www.apachefriends.org/) dan jalankan **Apache** dan **MySQL**.
2. Salin folder `source/` ke `C:\xampp\htdocs\perpustakaan\`
   (atau `/opt/lampp/htdocs/perpustakaan/` di Linux).
3. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`), tab **Import**,
   pilih file `database.sql`, lalu klik **Go**.
   (Alternatif lewat terminal: `mysql -u root -p < database.sql`)
4. Jika username/password MySQL Anda berbeda dari default XAMPP
   (root tanpa password), sesuaikan di `source/config/database.php`.
5. Buka `http://localhost/perpustakaan/` lalu login dengan salah satu akun demo di atas.

## Cara Hosting (Shared Hosting)

1. Buat database MySQL baru di cPanel, lalu impor `database.sql`
   lewat **phpMyAdmin** di hosting.
2. Upload **isi** folder `source/` ke `public_html/` (atau ke subdomain).
3. Sesuaikan `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` di
   `source/config/database.php` dengan database hosting Anda.
4. Buka domain/subdomain Anda dan login dengan akun demo.
5. **Setelah berhasil login, segera ganti password ketiga akun demo**
   lewat menu Manajemen User (Admin).

## Aturan Denda

- Lama pinjam default: **7 hari** (diatur di `config/database.php`, konstanta `LAMA_PINJAM_HARI`).
- Denda keterlambatan: **Rp1.000 per hari** (konstanta `DENDA_PER_HARI`),
  dihitung dari tanggal jatuh tempo sampai tanggal buku dikembalikan.
- Status "Terlambat" tampil otomatis jika buku belum dikembalikan dan hari ini
  sudah melewati tanggal jatuh tempo.

## Catatan Keamanan

- Semua query memakai **prepared statement (PDO)** — aman dari SQL injection.
- Semua output memakai `htmlspecialchars()` — aman dari XSS.
- Password disimpan sebagai hash (`password_hash` / `password_verify`),
  bukan teks biasa.
- Permission dicek di server pada setiap halaman, bukan hanya di menu.
- Hash password pada data awal (`database.sql`) memakai format SHA-512 crypt
  (`$6$`) yang dikenali `password_verify()`; user baru yang dibuat lewat
  aplikasi memakai `password_hash()` (bcrypt `$2y$`) seperti biasa.
