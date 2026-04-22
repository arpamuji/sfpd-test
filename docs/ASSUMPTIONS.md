# Assumptions & Design Decisions

**Project:** Warehouse Construction Approval System  
**Date:** 2026-04-21

---

## Assumptions

1. **Approval workflow statis** - 6 level berurutan tetap (Requestor → SPV → Kepala → Manager → Direktur Ops → Direktur Keuangan). Tidak ada konfigurasi dinamis.

2. **Satu user = satu role** - Tidak ada multi-role assignment.

3. **Rejection = submission stays rejected** - Submission yang ditolak tetap berstatus `rejected` (immutable record). Requestor harus membuat pengajuan **BARU** jika ingin mengajukan ulang. Ini memastikan audit trail yang jelas - setiap submission memiliki lifecycle yang terpisah.

4. **Dokumen: Wajib 3 PDF + opsional foto** - Upload minimal 3 file PDF sebagai dokumen wajib. Foto/gambar boleh diupload tambahan, tapi 3 PDF adalah mandatory requirement.
    - PDF formats: `.pdf`
    - Image formats: `.jpg`, `.jpeg`, `.png`
    - Max size: 5MB per file

5. **Dashboard visibility role-based** - Requestor hanya lihat submission sendiri. Approver lihat submission pending di level mereka.

6. **Approval notes** - Wajib untuk reject, opsional untuk approve.

7. **2FA secrets terenkripsi** - Menggunakan Laravel `encrypted` cast untuk automasi enkripsi/dekripsi di database.

8. **2FA secrets hardcoded untuk testing** - Seeder membuat user dengan 2FA secret yang sudah ditentukan. Secret ditampilkan di README untuk recruiter.

9. **Tidak ada public registration** - User dibuat via seeder saja.

10. **Workflow tidak berubah** - Tidak perlu admin panel untuk konfigurasi approval chain.

---

## Technical Decisions

| Decision        | Choice                     | Rationale                       |
| --------------- | -------------------------- | ------------------------------- |
| ID Type         | UUID                       | IDOR protection, unguessable    |
| Architecture    | Service-Repository Pattern | Clean separation, testable      |
| Frontend        | Inertia.js + React         | Modern SPA tanpa API complexity |
| Package Manager | Bun                        | Faster installs                 |
| Map Library     | Leaflet.js + OpenStreetMap | Free, no API key                |
| 2FA Library     | pragmarx/google2fa-laravel | Laravel integration, QR support |

---

## Testing Strategy

- **Unit Tests:** Services dan Repositories (PHPUnit)
- **Feature Tests:** Auth flow, approval workflow, role access (PHPUnit)

---
