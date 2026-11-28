# DESIGN.md — Sistem Informasi Perpustakaan (UAS)

Arah visual aplikasi. Dibaca sebelum mengubah tampilan apa pun.
Filter antislop berlaku di atas arah ini, bukan sebaliknya.

## Design Read

> Reading this as: admin dashboard for a campus library (UNPAM), for
> librarians and members, in a warm paper-and-ink catalogue style,
> dial ENERGY 2 / RHYTHM 2 / MOTION 1.

## Identitas

Aplikasi ini alat kerja perpustakaan, bukan startup. Bahasa visualnya:
**kartu katalog dan buku besar (ledger)**. Motif identitasnya tiga:
kartu katalog bergaris halus, angka serif, dan label status seperti
cap stempel. Dashboardnya bukan template admin, melainkan **meja
pustakawan**: tiga tumpukan kerja (kembali hari ini, terlambat, baru
dipinjam). Tabelnya **rapat seperti buku besar**, bukan lega seperti
landing page. Kalau logo dan nama produk diganti, tampilannya tetap
harus terasa seperti perpustakaan, bukan seperti aplikasi generik.

## Palet (maksimal 2 warna inti + 1 aksen)

| Peran | Warna | Alasan satu baris |
|---|---|---|
| Paper (latar) | `#faf7f0` | Kertas adalah bahan perpustakaan; putih hangat mengalahkan abu-abu datar. |
| Ink (teks) | `#201a17` | Hitam hangat lebih lembut dari hitam murni di atas kertas, kontras tetap di atas 4.5:1. |
| Hijau perpustakaan (aksi utama, nav aktif, tautan) | `#1e4d3b` | Warna jilid buku dan cat ruang baca; satu hijau menggantikan empat warna kartu statistik lama. |
| Amber (aksen, hanya untuk terlambat/denda) | `#b45309` | Amber berarti "perhatian" di buku besar; muncul tepat di tempat ada uang atau waktu yang jatuh tempo. |

Warna status memakai rona yang sama: hijau untuk tersedia/dikembalikan,
merah bata `#a93226` untuk stok habis/hapus (bahaya harus tetap terbaca
sebagai bahaya), amber untuk terlambat. Semuanya diredam supaya halaman
tetap terasa satu palet. Abu-abu hangat untuk garis dan teks sekunder
tidak dihitung sebagai warna inti.

## Tipografi

- **Serif display** (Georgia, 'Times New Roman', serif) untuk merek,
  judul halaman, dan angka besar. Alasan: buku dan katalog itu serif;
  satu pilihan ini memberi karakter pada seluruh aplikasi.
- **Sans sistem** untuk isi dan tabel. Alasan: data harus tetap terbaca
  pada ukuran kecil.
- **Monospace sistem** untuk nomor anggota (AG-001) dan kode. Alasan:
  nomor katalog terbaca sebagai data, bukan prosa.
- **Tanpa webfont.** Alasan: aplikasi jalan di XAMPP localhost dan
  harus tampil benar tanpa internet.

## Tata letak

- **Sidebar kertas**, garis halus di kanan, navigasi dikelompokkan
  dengan label kecil (Katalog, Sirkulasi, Pengaturan). Alasan: shell
  terlihat di setiap layar; pengelompokan memberi tahu pustakawan di
  mana letak tiap hal.
- **Menu aktif** ditandai blok hijau + tebal, bukan warna saja. Alasan:
  status harus bertahan untuk pengguna buta warna dan saat sidebar
  mengecil jadi ikon.
- **Dashboard menjawab "apa yang harus dikerjakan hari ini".**
  Tiga kolom kerja: kembali hari ini, terlambat, baru dipinjam.
  Angka total hanya satu baris tenang di bawah, bukan empat kartu
  sama besar. Alasan: keputusan di layar ini adalah apa yang harus
  dikerjakan, bukan seberapa besar koleksinya.
- **Status dicap, bukan ditempel pil.** Label status (Dipinjam,
  Terlambat, Dikembalikan, Habis) memakai huruf kapital, garis tebal,
  dan sedikit miring seperti cap stempel. Alasan: stempel adalah
  bahasa perpustakaan; pil warna adalah bahasa template.
- **Tabel rapat seperti buku besar.** Isi tabel 0.92rem, jarak baris
  0.5rem, garis halus, tanpa belang zebra. Alasan: ledger asli itu
  padat; halaman yang terlalu lega dan bersih adalah ciri khas AI.
- **Tabel datar**, garis halus, tanpa belang zebra. Alasan: belang
  adalah dekorasi; garis adalah buku besar.
- **Radius**: kartu 10px, tombol/input 8px, label status pil. Alasan:
  radius menandai hierarki, kecil untuk data, lembut untuk wadah,
  pil hanya untuk stempel status.
- **Bayangan**: datar di mana-mana kecuali kartu login dan modal.
  Alasan: hanya benda yang melayang di atas halaman yang punya
  bayangan.
- **Gerakan (MOTION 1)**: hover saja, tanpa animasi berjalan.
  Alasan: alat kerja tidak boleh bergerak sendiri.

## Copy

Bahasa Indonesia. CTA spesifik (Tambah Buku, Simpan, Kembalikan).
Tanpa em dash, tanpa kata promosi (revolusioner, canggih, tanpa batas),
tanpa emoji di antarmuka. Angka yang tampil adalah hitungan nyata
dari database; tidak ada delta persen tanpa periode pembanding yang
nyata. State kosong menyebut penyebab dan aksi berikutnya, bukan
sekadar "Tidak ada data".

## Aksesibilitas

Kontras minimal 4.5:1, cincin fokus terlihat, target sentuh 44px,
bisa dipakai penuh dengan keyboard, setiap field punya label terlihat,
gerak berlebih mati saat `prefers-reduced-motion`.
