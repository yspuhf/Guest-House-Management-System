# University Guest House Management System (UGHMS)
## Deployment & Security Hardening Checklist

---

### 1. Environment & Secrets Management
- [ ] **Disable Debug Mode in Production:**  
  Set `APP_DEBUG=false` in `.env`. Never expose stack traces on university networks.
- [ ] **Generate Production Application Key:**  
  `php artisan key:generate` to generate a 256-bit AES cryptographic encryption key.
- [ ] **Enforce HTTPS (TLS 1.3):**  
  Obtain institutional SSL/TLS certificate (e.g., Let's Encrypt or NKN certificate) and redirect all HTTP traffic to HTTPS via Nginx/Apache.
- [ ] **Secure Database Credentials:**  
  Use dedicated database credentials with least privileges (`SELECT, INSERT, UPDATE, DELETE`) rather than database root.

---

### 2. Application & Data Security
- [ ] **Cross-Site Request Forgery (CSRF):**  
  All forms must include `@csrf` token verification (`ValidateCsrfToken` middleware).
- [ ] **Cross-Site Scripting (XSS):**  
  Blade `{{ $variable }}` escaping is used globally for output. Raw HTML (`{!! !!}`) is strictly avoided for all user input.
- [ ] **SQL Injection Prevention:**  
  Eloquent ORM and parameterized PDO bindings are used for all queries. No raw string concatenations in SQL clauses.
- [ ] **Insecure Direct Object References (IDOR):**  
  All record modifications enforce authorization gates (`BookingPolicy`, `InvoicePolicy`). Direct URL manipulation is denied with 403 Forbidden.
- [ ] **Private Storage for Identity Documents:**  
  Identification proofs and duty sanction orders are stored in private disk `storage/app/private/documents/`. All file retrievals require authentication and policy verification via `DocumentController`.
- [ ] **MIME Type & File Size Validation:**  
  File uploads strictly validate file type (`pdf, jpg, jpeg, png`) and limit payload size to 5MB (`StoreBookingRequest`).

---

### 3. Session & Access Control
- [ ] **Cookie Security Flags:**  
  In `config/session.php`, enforce `http_only = true` and `secure = true` (over HTTPS) to mitigate session hijacking.
- [ ] **Brute-Force Rate Limiting:**  
  Login endpoints are throttled to 5 requests per minute per IP using Laravel rate limiters.
- [ ] **Immutable Audit Trail:**  
  Ordinary users and administrators cannot edit or delete records in `audit_logs`.

---

### 4. Backup & Disaster Recovery Runbook
- [ ] **Automated Daily Database Dump:**  
  Setup a scheduled cron job on the university server:
  ```bash
  mysqldump -u ughms_user -p university_guest_house | gzip > /backups/db/ughms_$(date +\%F).sql.gz
  ```
- [ ] **Weekly Document Storage Backup:**  
  Rsync private documents to off-site backup storage:
  ```bash
  rsync -avz /var/www/guest-house/storage/app/private /backups/documents/
  ```
- [ ] **Disaster Recovery Simulation:**  
  Verify restore procedure quarterly on a staging environment using `database/schema.sql` and the latest database dump.

