# Security Verification — Pertemuan 6

| Kontrol | Cara dibuktikan | Test |
| --- | --- | --- |
| Token tidak bisa ditebak | 32 byte acak (CSPRNG), minimal 128 bit | `OpaqueTokenTest` |
| Server hanya menyimpan hash | Pencarian lewat SHA-256 biner; token mentah tidak ada di database | `QrTokenServiceTest::test_issue_stores_only_the_hash_of_the_token` |
| Audit tidak membocorkan token | Audit hanya mencatat id meja | `QrTokenServiceTest::test_audit_log_records_issue_without_leaking_the_token` |
| Satu token aktif per meja | Rotate mencabut token lama dalam transaksi | `QrTokenServiceTest::test_rotate_revokes_old_token_and_keeps_single_active` |
| QR lama mati setelah rotate | Token lama menghasilkan 404 | `QrTableSessionTest::test_rotate_revokes_old_token_and_keeps_single_active` |
| 404 generik | Token acak, angka (`1`), dicabut, kantin nonaktif, meja nonaktif: semua 404 yang sama | `QrTableSessionTest::test_invalid_tokens_are_generic_404` |
| Cookie sesi aman | `HttpOnly`, `SameSite=Lax`; flag `Secure` aktif di HTTPS/production | `QrTableSessionTest::test_valid_scan_creates_bound_session_with_secure_cookie` |
| Sesi terikat kantin dan meja | Diambil dari token, bukan dari input klien | test yang sama |
| Meja nonaktif menolak pindai | Status `inactive` menghasilkan 404 sampai diaktifkan lagi | `TableQrUseCaseTest::test_deactivated_table_rejects_scan_until_reactivated` |
| Otorisasi per kantin | Pengelola kantin lain mendapat 403 | `TableQrUseCaseTest::test_index_summarises_tables_and_other_canteen_cannot_change_status` |
| Pembatasan laju | Limiter `qr-scan`: 30 pindai per menit per IP | konfigurasi di `routes\web.php` |
| Kantin tutup tidak membuat sesi | Di luar jam operasional, sesi tidak dibuat | `CustomerScanUseCaseTest::test_scan_outside_hours_shows_opening_hours_without_creating_session` |

Catatan: flag `Secure` pada cookie tidak aktif saat pengembangan lokal lewat HTTP, dan aktif otomatis
di production atau saat permintaan memakai HTTPS.