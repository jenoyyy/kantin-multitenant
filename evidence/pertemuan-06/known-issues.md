# Known Issues & SOP — Pertemuan 6 (Meja, QR Token Opaque, Sesi Anonim)

## Risiko: foto QR tersebar
QR adalah kredensial publik yang mudah disalin. Foto QR yang tersebar bisa dipakai dari luar kantin.
**SOP:** pengelola membuka Admin → Meja & QR → klik **Rotate QR** pada meja terkait, lalu cetak
ulang QR baru. Token lama langsung tidak berlaku (404 generik).

## Masa berlaku
- Token QR meja tidak kedaluwarsa otomatis; dicabut lewat rotasi atau menonaktifkan meja.
- Sesi pelanggan anonim kedaluwarsa dalam 4 jam (cookie `customer_session`, HttpOnly, SameSite=Lax,
  Secure di production).

## Keamanan token
- Token mentah (32 byte acak, base64url) hanya tampil sekali saat diterbitkan.
- Database hanya menyimpan hash SHA-256 biner; audit log tidak pernah memuat token maupun hash.
- Endpoint `/q/{token}` dibatasi 30 pindai per menit per IP (limiter `qr-scan`).

## Catatan implementasi
- Tabel lama `table_tokens` dan `table_sessions` (Modul 3) tidak dipakai lagi; digantikan
  `table_qr_tokens` dan `customer_sessions`. Belum dihapus agar riwayat migrasi tetap utuh.
- Gambar QR siap cetak (SVG, bacon/bacon-qr-code), penonaktifan meja, halaman sambutan, dan jam operasional (tabel `operating_hours`) sudah ada sejak Tahap 8. Tenant tanpa jam operasional dianggap buka.