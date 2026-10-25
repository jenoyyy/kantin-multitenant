# Demo Script — Terbitkan QR lalu Pindai

Persiapan: `composer run dev`, login `admin@kantin.test`.

1. Buka **Meja & QR**. Daftar meja kantin tampil. Screenshot: `screenshots\01-daftar-meja.png`
2. Klik **Terbitkan & unduh QR** pada meja `M01`. Kartu QR tampil dengan tombol **Unduh QR (SVG)**
   dan **Cetak**. Screenshot: `screenshots\02-qr-meja.png`
3. Salin URL `/q/...` di bawah QR, buka di tab baru. Pelanggan diarahkan ke halaman
   **"Selamat datang, ..."** dan daftar tenant dengan label buka/tutup. Screenshot: `screenshots\03-sambutan-meja.png`
4. Kembali ke admin, klik **Regenerasi & unduh QR**, lalu buka URL yang lama. Muncul
   **"QR Code tidak dapat digunakan ... hubungi petugas kantin"** (404). Screenshot: `screenshots\04-qr-lama-ditolak.png`
5. Klik **Nonaktifkan** pada `M01`, buka URL QR yang baru. Juga ditolak. Aktifkan kembali lewat
   **Aktifkan kembali**.