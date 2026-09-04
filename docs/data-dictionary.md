# Data Dictionary — Kantin Multi-Tenant

## Kelompok Tabel

### 1. Identitas & Akses (platform-scoped)
- canteens
- tenants
- users
- (role, rekening bank — dilengkapi di Tahap 2)

### 2. Meja & Session (tenant-owned / platform-scoped campuran)
- tables
- customer_sessions

### 3. Katalog & Komisi (tenant-owned)
- categories
- menus
- modifiers
- commission_schemes

### 4. Order (campuran)
- orders (platform-scoped, TIDAK punya tenant_id)
- tenant_orders (tenant-owned)
- order_items (tenant-owned)

### 5. Payment & Ledger (platform-scoped / tenant-owned campuran)
- payments (platform-scoped)
- payment_events (platform-scoped)
- ledger_entries (tenant-owned)
- withdrawals (tenant-owned)

### 6. Outbox & Audit (platform-scoped)
- notification_deliveries
- audit_logs