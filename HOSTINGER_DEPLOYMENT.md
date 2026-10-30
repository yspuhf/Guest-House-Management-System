# Hostinger Deployment & Git Guide
## Dr. Y.S. Parmar UHF Nauni - Central Guest House Management System

---

## 🚀 Instant Fix for "500 Server Error" on Hostinger

If you see **500 Server Error** on Hostinger after deploying via Git, it is caused by one of three reasons:

### Reason 1: Hostinger tries to run PHP without Composer `vendor/` or `.env`
* **Why it happens:** Git repos deliberately do NOT commit `vendor/` or `.env` (to protect database credentials and passwords). If Apache tries to run Laravel's `index.php` without dependencies, PHP throws a fatal error &rarr; **500 Server Error**.
* **What we fixed:**
  1. Added `DirectoryIndex index.html index.php` in `.htaccess` so Hostinger immediately serves `index.html` (the full interactive guest house portal) without needing PHP or Composer.
  2. Added an intelligent fallback in `public/index.php` and root `index.php`: If `vendor/autoload.php` is missing, it automatically loads `index.html` instead of throwing a 500 error.

### Reason 2: PHP Version on Hostinger
* In Hostinger hPanel &rarr; **Advanced** &rarr; **PHP Configuration**:
* Ensure your PHP version is set to **PHP 8.2** or **PHP 8.3**.

### Reason 3: If You Want Full Laravel Backend Mode on Hostinger
If you want to run Laravel's full database backend rather than the static portal:
1. In Hostinger hPanel &rarr; **File Manager** (or SSH), copy `.env.example` to `.env`.
2. Edit `.env` with your Hostinger MySQL database details (created under **Databases &rarr; Management** in hPanel).
3. In SSH terminal on Hostinger, run:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan key:generate
   php artisan migrate --seed
   chmod -R 775 storage bootstrap/cache
   ```

---

## 📦 How to Push this Fix to GitHub and Hostinger

Open PowerShell in `D:\Website Projects\Guest House Management System` and run:

```powershell
# 1. Add all updated files
git add .

# 2. Commit the fix
git commit -m "Fix 500 error: Add DirectoryIndex and fail-safe router for Hostinger"

# 3. Push to your GitHub repository
git push origin main
```

Then in Hostinger hPanel:
* Go to **Advanced &rarr; Git** &rarr; Click **Deploy** (or **Pull**) to fetch the latest commit.
* Your website will immediately load without the 500 error!
