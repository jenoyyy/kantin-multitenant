# Quality Gate — Pertemuan 6

Tanggal: 25 Oktober 2026

| Pemeriksaan | Perintah | Hasil |
| --- | --- | --- |
| Gaya kode | `vendor\bin\pint --test` | PASS, 162 berkas |
| Seluruh test | `php artisan test --compact` | 113 lulus, 338 assertion |
| Test khusus QR | `php artisan test --compact tests\Feature\QrTableSessionTest.php` | lulus (lihat `test-results\phpunit-qr.txt`) |
| Route meja | `php artisan route:list --path=admin/tables` | 5 route (index, store, rotate, qr, status) |
| Route pelanggan | `php artisan route:list --path=kantin` | `customer.home` dan `customer.welcome` |

Catatan: test berjalan di SQLite memori, sehingga migrasi memakai perintah Laravel yang kompatibel
dengan MySQL dan SQLite (bukan `ALTER TABLE ... MODIFY` khusus MySQL).