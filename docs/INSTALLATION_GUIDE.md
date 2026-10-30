# University Guest House Management System (UGHMS)
## Installation & Deployment Guide for Windows (XAMPP) and Linux

**Institution:** Dr. Y.S. Parmar University of Horticulture & Forestry, Nauni, Solan (H.P.)  
**Application Stack:** PHP 8.1 / 8.2 / 8.3, Laravel Framework, MySQL / MariaDB (InnoDB), Bootstrap 5, Blade.

---

### Table of Contents
1. [Prerequisites](#1-prerequisites)
2. [Windows Installation with XAMPP](#2-windows-installation-with-xampp)
3. [Linux (Ubuntu / Debian / CentOS) Installation](#3-linux-installation)
4. [Database Setup Methods](#4-database-setup-methods)
   - [Method A: Direct phpMyAdmin / MySQL Import (No CLI required)](#method-a-direct-phpmyadmin--mysql-cli-import)
   - [Method B: Laravel Artisan Migrations & Seeders](#method-b-laravel-artisan-migrations--seeders)
5. [Default User Credentials & Roles](#5-default-user-credentials--roles)
6. [File Storage & Permissions Setup](#6-file-storage--permissions-setup)
7. [Verification & Sanity Checklist](#7-verification--sanity-checklist)

---

### 1. Prerequisites

Before installing the application, ensure the host machine meets the following requirements:
- **PHP:** Version 8.1, 8.2, or 8.3 with extensions:
  - `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`
- **Database Server:** MySQL 8.0+ or MariaDB 10.4+ with InnoDB engine support.
- **Web Server:** Apache 2.4+ (with `mod_rewrite` enabled) or Nginx 1.20+.
- **Package Manager (Optional for production):** Composer 2.x.

---

### 2. Windows Installation with XAMPP

#### Step 1: Install XAMPP
1. Download and install XAMPP for Windows (PHP 8.1 or higher) from [Apache Friends](https://www.apachefriends.org).
2. Start the **Apache** and **MySQL** services from the XAMPP Control Panel.

#### Step 2: Place Application Files
1. Place the project directory into:
   ```
   C:\xampp\htdocs\guest-house
   ```
   (or keep in your existing project path `d:\Website Projects\Guest House Management System` and configure an Apache VirtualHost or alias).

#### Step 3: Configure Apache VirtualHost
Open `C:\xampp\apache\conf\extra\httpd-vhosts.conf` and append:
```apache
<VirtualHost *:80>
    ServerName guesthouse.local
    DocumentRoot "d:/Website Projects/Guest House Management System/public"
    <Directory "d:/Website Projects/Guest House Management System/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
Add `127.0.0.1 guesthouse.local` to `C:\Windows\System32\drivers\etc\hosts`.  
Restart Apache.

Alternatively, you can run the built-in development server from PowerShell / CMD:
```powershell
cd "d:\Website Projects\Guest House Management System"
php artisan serve
```
Then visit `http://127.0.0.1:8000`.

---

### 3. Linux Installation

#### Step 1: Install System Dependencies (Ubuntu / Debian)
```bash
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
    php8.2-curl php8.2-bcmath php8.2-zip nginx mysql-server composer
```

#### Step 2: Clone and Configure Environment
```bash
cd /var/www
sudo git clone <repo_url> guest-house
cd guest-house
cp .env.example .env
```

#### Step 3: Nginx Server Block
Create `/etc/nginx/sites-available/guesthouse`:
```nginx
server {
    listen 80;
    server_name guesthouse.yspuniversity.ac.in;
    root /var/www/guest-house/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
Enable the site and reload Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/guesthouse /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

---

### 4. Database Setup Methods

Choose either **Method A** or **Method B**.

#### Method A: Direct phpMyAdmin / MySQL CLI Import (Fastest & Standalone)
No composer or artisan commands required!

1. Open phpMyAdmin (`http://localhost/phpmyadmin`) or connect via MySQL console:
   ```bash
   mysql -u root -p
   ```
2. Create the database:
   ```sql
   CREATE DATABASE university_guest_house CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE university_guest_house;
   ```
3. Import the normalized schema:
   ```bash
   mysql -u root -p university_guest_house < database/schema.sql
   ```
   *(Or in phpMyAdmin, select `university_guest_house` -> Click **Import** -> Choose `database/schema.sql` -> Click **Go**)*
4. Import the seed and demo dataset:
   ```bash
   mysql -u root -p university_guest_house < database/demo_data.sql
   ```
   *(Or in phpMyAdmin, Click **Import** -> Choose `database/demo_data.sql` -> Click **Go**)*

#### Method B: Laravel Artisan Migrations & Seeders
If Composer and PHP CLI are available:
```bash
cp .env.example .env
# Edit .env and configure DB_DATABASE, DB_USERNAME, DB_PASSWORD
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate:fresh --seed
```

---

### 5. Default User Credentials & Roles

All accounts are pre-configured with the default password:
**`password123`**

| Role | Name | Email | Primary Responsibilities |
| :--- | :--- | :--- | :--- |
| **System Administrator** | Dr. Rajesh Sharma | `admin@yspuniversity.ac.in` | Global accounts, security, settings & backups |
| **Guest House Administrator** | Sh. Suresh Verma | `ghadmin@yspuniversity.ac.in` | Masters: properties, room categories, tariffs & policies |
| **Guest House Incharge** | Sh. Anil Kumar | `incharge@yspuniversity.ac.in` | Room allocation, calendar, check-in, check-out |
| **Booking Verifier** | Dr. Meenakshi Thakur | `verifier@yspuniversity.ac.in` | Scrutiny of requisition documents and eligibility |
| **Approving Authority** | Dr. Sanjeev Chauhan (Dean) | `approver@yspuniversity.ac.in` | Final sanction / rejection of booking requests |
| **Faculty / Applicant** | Dr. Vikas Sharma | `employee@yspuniversity.ac.in` | Submitting requisitions, tracking status, vouchers |
| **Accounts / Finance** | Sh. Ramesh Chand | `finance@yspuniversity.ac.in` | Generating invoices, accepting payments, receipts |
| **Report Auditor** | Dr. Neena Kumari | `auditor@yspuniversity.ac.in` | View-only auditing of registers and immutable logs |

*Note: The application header includes a "Switch Demo Role" dropdown allowing instant switching between any persona for interactive testing and demonstration.*

---

### 6. File Storage & Permissions Setup

1. Create storage directories if missing:
   ```bash
   php artisan storage:link
   ```
2. Grant write permissions on Linux:
   ```bash
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   ```
3. Private storage is located in `storage/app/private/documents/`. Direct public HTTP access to this folder is blocked; all downloads route through `DocumentController` with role-based gate checks.

---

### 7. Verification & Sanity Checklist

- [x] Database migrations create all 27 normalized tables with foreign keys and compound indexes.
- [x] Initial seed data populates Dr. Y.S. Parmar UHF Nauni master properties, category tariffs, and sample bookings.
- [x] Login successfully authenticates user and displays role-specific dashboard.
- [x] "Switch Demo Role" dropdown switches between administrator, verifier, approver, incharge, and applicant seamlessly.
- [x] Public request tracker functions at `/track` without requiring credentials.
- [x] Printable vouchers render cleanly with university seals and instructions.
- [x] Invoices calculate decimal-safe tariffs with GST breakdown.
- [x] CSV export generates properly formatted spreadsheet registers.

