# Data Dictionary — Kantin Multi-Tenant

## Kelompok Tabel

### 1. Identitas & Akses (platform-scoped)
- canteens
- tenants
- users
- tenant_user_roles
- balances
- bank_accounts

### 2. Meja & Session (tenant-owned / platform-scoped campuran)
- dining_tables (platform-scoped, milik canteen bukan tenant)
- table_tokens
- table_sessions
- operating_hours

### 3. Katalog & Komisi (tenant-owned)
- menu_categories
- menus
- modifier_groups
- order_item_modifiers

### 4. Order (campuran)
- orders (platform-scoped, TIDAK punya tenant_id)
- tenant_orders (tenant-owned)
- order_items (tenant-owned)

### 5. Payment & Ledger (platform-scoped / tenant-owned campuran)
- payments
- payment_attempts
- payment_events
- ledger
- withdrawals

### 6. Outbox & Audit (platform-scoped)
- outbox
- notification_deliveries
- audit_logs

## Catatan Penyimpangan dari ERD Baseline (Pertemuan 3)

Selama implementasi migration, ditemukan beberapa penyesuaian dari draft awal / ERD baseline Fase 0:

- Nama tabel di draft awal Tahap 1 (`tables`, `categories`, `modifiers`, `commission_schemes`, `ledger_entries`, `customer_sessions`) disesuaikan menjadi nama sebenarnya di migration: `dining_tables`, `menu_categories`, `modifier_groups`, `ledger`, `table_sessions`.
- **`dining_tables`**: terhubung ke `canteen_id` (bukan `tenant_id`), dengan kolom `label` (bukan `code`) dan `status` — karena 1 meja fisik bisa dipakai lintas tenant dalam satu kantin yang sama.
- **`commission_schemes`**: dikonfirmasi belum dibuat (`Schema::hasTable('commission_schemes')` mengembalikan `false`). Modul mensyaratkan skema komisi effective-dated (tarif dengan masa berlaku) pada Tahap 3, sehingga ini merupakan gap yang perlu ditindaklanjuti pada iterasi berikutnya — bukan penyesuaian penamaan.
- **`order_items`**: awalnya tidak memiliki kolom `tenant_id` langsung (hanya `tenant_order_id`), sehingga FK ke `menus` cuma memvalidasi `menu_id` exist tanpa memastikan tenant yang sama. Ditambahkan migrasi terpisah (`2026_09_05_add_tenant_id_to_order_items_table`) untuk menambahkan kolom `tenant_id` (di-backfill dari `tenant_orders`) dan FK komposit `(tenant_id, menu_id)` → `menus(tenant_id, id)` dengan `restrictOnDelete()`. Sudah diverifikasi manual: insert `order_item` dengan `tenant_id` dan `menu_id` dari tenant berbeda ditolak database dengan `ERROR 1452`.
## Mapping ERD → Model Eloquent

| Tabel               | Model            | Relasi Utama                          |
|---------------------|------------------|----------------------------------------|
| canteens             | Canteen          | hasMany Tenant, hasMany DiningTable    |
| tenants              | Tenant           | belongsTo Canteen                      |
| menu_categories      | MenuCategory     | belongsTo Tenant, hasMany Menu         |
| menus                | Menu             | belongsTo Tenant, belongsTo MenuCategory, hasMany OrderItem |
| dining_tables        | DiningTable      | belongsTo Canteen, hasMany TableSession |
| orders               | Order            | hasMany TenantOrder                    |
| tenant_orders        | TenantOrder      | belongsTo Order, belongsTo Tenant, hasMany OrderItem |
| order_items          | OrderItem        | belongsTo TenantOrder, belongsTo Menu  |