# Hostinger Git Deployment Guide
## Dr. Y.S. Parmar UHF Nauni - Central Guest House Management System

This guide outlines how to push this repository to GitHub/GitLab and automatically deploy it to **Hostinger** web hosting.

---

## Architecture Overview

The repository is pre-configured to work seamlessly on Hostinger:
* **Static / Public Portal:** `index.html` (Public reservation portal with Wing selection & Notification 1615-40 rates).
* **Estate Admin Console:** `preview.html` (Full management prototype & Estate Tariff Console).
* **Pricing & Rules Engine:** `estate_tariffs.js` (Notification 1615-40 dynamic calculation & persistence).
* **Assets:** `university_logo.jpg` official crest.
* **Dual-Target Readiness:** Files are present both in the root directory and in `public/` so Hostinger can serve the site regardless of whether your domain points to `public_html/` or `public_html/public/`.

---

## Step 1: Create a Repository on GitHub or GitLab

1. Log in to [GitHub](https://github.com) (or GitLab).
2. Click **New Repository** (`+` icon at the top right).
3. Name your repository (e.g., `guest-house-management-system` or `yspu-guest-house`).
4. Set it as **Public** or **Private**.
   > *Note:* If private, you will add a Hostinger deployment key (explained below).
5. **Do NOT** initialize with a README, .gitignore, or license (we already created them locally).
6. Click **Create repository**.
7. Copy your repository HTTPS URL (e.g., `https://github.com/YOUR_USERNAME/guest-house-management-system.git`).

---

## Step 2: Push Local Code to GitHub

Open PowerShell or terminal in `D:\Website Projects\Guest House Management System` and run:

```bash
# 1. Add your GitHub repository as remote origin (replace with your URL)
git remote add origin https://github.com/YOUR_USERNAME/guest-house-management-system.git

# 2. Verify remote URL
git remote -v

# 3. Push code to main branch
git push -u origin main
```

*(When prompted by GitHub, sign in via browser or paste your Personal Access Token).*

---

## Step 3: Deploy on Hostinger via hPanel

Hostinger has native Git integration built right into **hPanel**:

1. Log in to your **Hostinger Control Panel (hPanel)**: [https://hpanel.hostinger.com](https://hpanel.hostinger.com)
2. Select your hosting plan / domain.
3. In the left-hand search bar, type **Git** (or go to **Advanced &rarr; Git**).
4. Fill in the deployment form:
   * **Repository:** Paste your GitHub repository URL (e.g., `https://github.com/YOUR_USERNAME/guest-house-management-system.git`).
   * **Branch:** `main`
   * **Install Directory:** Leave blank or enter `public_html` (Hostinger will clone into `public_html`).
5. **If your GitHub repository is Private:**
   * Hostinger will generate an **SSH Deployment Key**.
   * Copy the public key shown by Hostinger.
   * Go to your **GitHub Repository &rarr; Settings &rarr; Deploy keys &rarr; Add deploy key**.
   * Paste the key and check *Allow write access* (optional), then save.
6. Click the **Create** or **Deploy** button in Hostinger.
7. Hostinger will clone your repository into `public_html`.

---

## Step 4: Enable Automated Deployments (Auto-Deploy Webhook)

With Hostinger Webhooks, every time you run `git push origin main`, Hostinger will automatically pull and update your website in real-time!

1. In Hostinger hPanel under **Advanced &rarr; Git &rarr; Manage Git Repositories**, locate your deployed repository.
2. Click on the **Webhook** icon or copy the **Auto-Deployment Webhook URL** provided by Hostinger:
   `https://hpanel.hostinger.com/api/git/deploy?id=...`
3. Go to your **GitHub Repository &rarr; Settings &rarr; Webhooks &rarr; Add webhook**.
4. In **Payload URL**, paste the Hostinger Webhook URL.
5. Set **Content type** to `application/json`.
6. Select **Just the push event**.
7. Click **Add webhook**.

*From now on, whenever you run `git push`, your live Hostinger website updates automatically!*

---

## Step 5: Activate Free SSL & Verify Live Site

1. In Hostinger hPanel, go to **Security &rarr; SSL**.
2. Click **Install SSL** on your domain (Free Let's Encrypt SSL).
3. Visit your live domain in the browser:
   * `https://yourdomain.com` &rarr; Loads the Public Guest House Portal.
   * `https://yourdomain.com/preview.html#tariffs` &rarr; Loads the Estate Admin Tariff Console.
