# Warehouse Construction Approval System

Sistem pengajuan pembangunan gudang distribusi dengan proses approval berjenjang (6 level), 2FA authentication, geolocation, dan upload dokumen.

## Tech Stack

- **Backend:** Laravel 12, PHP 8.3, PostgreSQL
- **Frontend:** React 19, Inertia.js, Tailwind CSS 4, TypeScript
- **Package Manager:** Bun 1.0+
- **2FA:** pragmarx/google2fa-laravel (TOTP)
- **Maps:** Leaflet.js + OpenStreetMap
- **Icons:** Tabler Icons

## Requirements

- PHP 8.3+
- Bun 1.0+
- Composer

## Setup & Installation

### 1. Install Dependencies

```bash
bun install
composer install
```

### 2. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Database menggunakan PostgreSQL. Pastikan PostgreSQL sudah terinstall dan berjalan.

### 3. Setup Database

```bash
# Create database and user
sudo -u postgres psql -c "CREATE DATABASE sfpd_test;"
sudo -u postgres psql -c "CREATE USER sfpd_user WITH PASSWORD 'sfpdtest123';"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE sfpd_test TO sfpd_user;"
sudo -u postgres psql -d sfpd_test -c "GRANT ALL ON SCHEMA public TO sfpd_user;"

# Update .env file
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=sfpd_test
# DB_USERNAME=sfpd_user
# DB_PASSWORD=sfpdtest123
```

Install PHP PostgreSQL extension:

```bash
sudo apt install php-pgsql
```

### 4. Run Migrations

```bash
php artisan migrate
php artisan db:seed
```

### 5. Jalankan Aplikasi

Terminal 1 (Laravel):

```bash
php artisan serve
```

Terminal 2 (Vite):

```bash
bun run dev
```

Akses: http://localhost:8000

### 6. Create Storage Symlink

```bash
php artisan storage:link
```

## Test User Accounts

Semua user password: **password123**

| Role                 | Email                      | 2FA Secret         | QR Code                                       |
| -------------------- | -------------------------- | ------------------ | --------------------------------------------- |
| Requestor            | requestor@test.com         | `JBSWY3DPEHPK3PXP` | [requester-test.png](docs/requester-test.png) |
| SPV Gudang           | spv@test.com               | `KRSXG5CTMVRXEZLU` | [spv-test.png](docs/spv-test.png)             |
| Kepala Gudang        | kepala@test.com            | `GEZDGNBVGY3TQOJQ` | [kepala-test.png](docs/kepala-test.png)       |
| Manager Operasional  | manager@test.com           | `MFRGGZDFMY2TQNZZ` | [manager-test.png](docs/manager-test.png)     |
| Direktur Operasional | direktur.ops@test.com      | `OVSG433SMVZWKZTH` | [dirops-test.png](docs/dirops-test.png)       |
| Direktur Keuangan    | direktur.keuangan@test.com | `KRSXG5CTMVRXEZTB` | [dirkeu-test.png](docs/dirkeu-test.png)       |

### Cara Login dengan 2FA

1. Buka QR code untuk role yang ingin dicoba (klik link di atas)
2. Scan dengan Google Authenticator / Authy
3. Atau masukkan manual 2FA Secret
4. Gunakan kode 6 digit dari aplikasi saat login

## Approval Workflow

```
Requestor → SPV Gudang → Kepala Gudang → Manager Operasional → Direktur Operasional → Direktur Keuangan
```

**Status:**

- `draft` - Pengajuan baru/belum dikirim
- `pending_spv` - Menunggu review SPV Gudang
- `pending_kepala` - Menunggu review Kepala Gudang
- `pending_manager_ops` - Menunggu review Manager Operasional
- `pending_direktur_ops` - Menunggu review Direktur Operasional
- `pending_direktur_keuangan` - Menunggu review Direktur Keuangan
- `approved` - Disetujui semua level
- `rejected` - Ditolak (final, tidak bisa diubah)

### Rejection Flow

Jika pengajuan **ditolak**:

- Status tetap `rejected` (tidak bisa diubah lagi)
- Requestor harus membuat pengajuan **BARU** jika ingin mengajukan ulang
- Setiap submission memiliki lifecycle terpisah untuk audit trail yang jelas

## Fitur

### Requestor

- Buat pengajuan pembangunan gudang baru
- Upload dokumen (3-10 file, PDF/JPG/PNG, max 5MB)
- Lihat status dan riwayat approval
- Track progress approval

### Approver (Semua Level)

- Lihat pengajuan pending di level mereka
- Review detail: warehouse, budget, lokasi, dokumen
- Lihat lokasi di map
- Approve atau reject pengajuan
- Tambahkan catatan approval

### Dashboard

- Stats: Total, Pending, Approved, Rejected
- Tabel "My Submissions" (requestor)
- Tabel "Pending Approvals" (approver)
- Status badge: Draft (abu), Pending (kuning), Approved (hijau), Rejected (merah)

## Architecture

### Service-Repository Pattern

```
Controllers → Services → Repositories → Models
```

| Component                 | Fungsi                                  |
| ------------------------- | --------------------------------------- |
| `SubmissionService`       | CRUD, upload file, submit for approval  |
| `ApprovalWorkflowService` | Approve/reject logic, pending approvals |
| `TwoFactorAuthService`    | TOTP generation & verification          |
| `Ensure2FAEnabled`        | Middleware proteksi route 2FA           |
| `RoleCheck`               | Middleware role-based access            |

### Database Schema

- **roles** - 6 level approval dengan `next_role_id` chain
- **users** - UUID, encrypted 2FA secrets
- **submissions** - UUID, data warehouse, status approval
- **submission_files** - Dokumen upload (3-10 file)
- **approval_logs** - Riwayat approval/rejection

## Build & Testing

### Run Tests

```bash
php artisan test
```

### Production Build

```bash
bun run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Catatan

- Pengajuan mulai dari status `draft`
- 2FA secrets dienkripsi dengan `encrypt()` Laravel
- Rate limiting login: 5 attempt per menit

## Validasi Form

**Pengajuan Baru:**

| Field           | Validasi                                  |
| --------------- | ----------------------------------------- |
| Nama gudang     | Required, max 255 karakter                |
| Alamat gudang   | Required, max 500 karakter                |
| Latitude        | Required, numeric: -90 sampai 90          |
| Longitude       | Required, numeric: -180 sampai 180        |
| Estimasi budget | Required, numeric, min 0                  |
| Deskripsi       | Optional, max 1000 karakter               |
| Dokumen         | 3-10 file, PDF/JPG/JPEG/PNG, max 5MB each |

**Approval:**

| Action  | Validasi                          |
| ------- | --------------------------------- |
| Approve | Notes optional                    |
| Reject  | Notes required (alasan penolakan) |

## Author

**A. Ramdhan Pamuji**  
Email: [hello@arpamuji.dev](mailto:hello@arpamuji.dev)

Dokumentasi tambahan:

- [Asumsi & Design Decisions](docs/ASSUMPTIONS.md)
- [Future Improvements](docs/IMPROVEMENTS.md)
- [Technical Specs](docs/specs/)

## Future Enhancements

Lihat [docs/IMPROVEMENTS.md](docs/IMPROVEMENTS.md):

- Dynamic workflow engine
- Email notifications
- Multi-role support
- Document versioning

## License

MIT License
