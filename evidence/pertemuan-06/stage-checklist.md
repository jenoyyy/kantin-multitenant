# Stage Checklist — Pertemuan 6

| Tahap | Isi | Status | Commit |
| --- | --- | --- | --- |
| 1 | Skema `table_qr_tokens` dan `customer_sessions`, penyesuaian `dining_tables`, model | Selesai | 576c54d |
| 2 | Policy dan CRUD meja (scoped kantin) | Selesai | 3e62ebc |
| 3 | `OpaqueToken` (token 256 bit, hash SHA-256 biner) + unit test | Selesai | 3e62ebc |
| 4 | `QrTokenService`: terbitkan dan rotate (satu token aktif per meja) | Selesai | 3e62ebc |
| 5 | Pindai `/q/{token}` menjadi sesi anonim + cookie aman | Selesai | dbdf28e |
| 6 | Test keamanan dan sesi (5 test) | Selesai | dbdf28e |
| 7 | Dokumentasi dan commit | Selesai | dbdf28e |
| 8 | QR siap cetak (SVG), nonaktifkan meja, jam operasional, halaman sambutan, pesan QR tidak valid | Selesai | 52c33bd, 9ecdf1e |

Tag: `pertemuan-06`

## Penyesuaian terhadap modul
- Tabel QR/sesi baru dibuat dengan nama `table_qr_tokens` dan `customer_sessions`; tabel lama
  `table_tokens` dan `table_sessions` dibiarkan.
- Kantin diidentifikasi lewat `code` pada URL (tabel `canteens` tidak punya `slug`).
- Jam operasional memakai tabel `operating_hours` (model `OperatingHour`).
- `AuditLogger::record()` di proyek ini menerima `(aksi, model, perubahan)`.