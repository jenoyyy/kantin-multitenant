# Pemetaan Modul — Kantin Multi-Tenant

## Admin
- FR-ADM-01 Kelola tenant & skema komisi
- FR-ADM-02 Kelola meja & QR Code
- FR-ADM-03 Verifikasi pencairan dana

## Catalog
- FR-CUS-02 Telusuri katalog menu
- FR-TEN-02 Kelola menu & stok
- FR-TEN-03 Tandai menu habis

## Ordering
- FR-CUS-01 Identifikasi meja via QR
- FR-CUS-03 Keranjang belanja
- FR-CUS-04 Kustomisasi item
- FR-CUS-06 Checkout & buat pesanan
- FR-CUS-07 Pre-order terjadwal
- FR-CUS-08 Lacak status pesanan

## Payments
- FR-PAY-01 s.d. 04 QRIS, verifikasi, split payment, notifikasi
- FR-TEN-07 Rekonsiliasi bagi hasil
- FR-TEN-08 Ajukan penarikan dana

## Kitchen
- FR-CUS-05 Estimasi waktu tunggu
- FR-TEN-04 Kitchen Display System (KDS)

## Reporting
- FR-TEN-05 Laporan penjualan
- FR-TEN-06 Ekspor laporan

## Cross-cutting (bukan modul bisnis, dipakai semua)
- FR-TEN-01 Autentikasi & otorisasi

## Aturan Dependensi Antarmodul

- Setiap modul (Admin, Catalog, Ordering, Payments, Kitchen, Reporting) tidak boleh mengakses
  model/tabel milik modul lain secara langsung.
- Komunikasi antarmodul harus lewat Service atau Action yang disediakan modul tujuan (kontrak
  eksplisit), bukan lewat query database langsung ke tabel modul lain.
- Contoh: modul Ordering tidak mengakses tabel `ledger_entries` (milik Payments) secara langsung;
  Ordering memanggil service Payments jika perlu.
- Autentikasi (login, role, status akun) bersifat cross-cutting — dipakai semua modul, tidak
  dimiliki satu modul bisnis manapun.

## Konvensi Penamaan

- **Route name**: prefix sesuai konteks — `customer.*`, `tenant.*`, `admin.*` (contoh:
  `customer.home`, `tenant.dashboard`, `admin.dashboard`).
- **Namespace modul**: `App\Modules\{NamaModul}\{Actions|Data|Services}` (contoh:
  `App\Modules\Catalog\Services`).
- **Service Provider**: `App\Providers\{NamaModul}ServiceProvider` (contoh:
  `AdminServiceProvider`), didaftarkan di `bootstrap/providers.php`.
- **Layout Blade**: `resources/views/layouts/{customer|tenant|admin}.blade.php`.
- **View per konteks**: `resources/views/{customer|tenant|admin}/{nama-halaman}.blade.php`.