# 📊 White-Label Multi-Tenant Analytics Dashboard (PHP / MySQL)

A **white-label, agency-ready analytics dashboard** built with **PHP, MySQL, jQuery, JavaScript, and Bootstrap**, designed to aggregate data from multiple services (Stripe, Google Analytics, social platforms) into a single branded dashboard.

This system supports:

* Multiple agencies (true multi-tenant)
* White-label branding per agency
* Multiple custom domains per agency
* Agency users and optional client-only logins
* Secure integrations and encrypted credentials
* Public, revocable dashboard share links
* Mobile-friendly UI

This project is suitable as:

* A SaaS foundation
* A white-label analytics product
* An internal agency reporting tool

---

## 🚀 Features

### 🏢 Multi-Tenant Agency Architecture

* Unlimited agencies
* Each agency is fully isolated at the database and permission level
* Per-agency branding and domain mapping

### 🎨 White-Label Branding

* Agency logo URL
* Primary brand color
* Branded login page
* Branded dashboard UI
* Domain-based agency resolution

### 🌐 Multiple Domains per Agency

* Add unlimited domains per agency
* Set a primary domain
* Login automatically resolves agency via `HTTP_HOST`
* Supports:

  * `agency.yourapp.com`
  * `dashboard.clientdomain.com`
  * `login.php?agency=agency-slug`

### 👥 User & Permission System

* Agency roles:

  * `agency_owner`
  * `agency_admin`
  * `agency_manager`
  * `agency_viewer`
* Subuser access per client
* Optional **client logins**:

  * View-only
  * Restricted to a single client
  * No admin access

### 🔌 Integrations (Extensible)

* Stripe (API key based for MVP)
* Google Analytics 4 (JSON blob for MVP)
* Credentials encrypted at rest (AES-256-GCM)
* Integration status tracking
* Cron-based sync architecture

### 📈 Analytics Dashboard

* KPI summary cards
* Time-series trend charts
* Date range selection
* Client switching
* Mobile-friendly layout

### 🔗 Dashboard Share Links

* Create public, read-only dashboard links
* Optional expiration (7 / 30 / 90 days)
* Revocable at any time
* Token stored as SHA-256 hash (non-recoverable)
* Secure by design
* No authentication required to view shared dashboard

### 🔐 Security

* Password hashing with bcrypt
* CSRF protection on all POST endpoints
* Encrypted credentials storage
* Role-based access control (RBAC)
* Audit logging for sensitive actions
* Share tokens never stored in plaintext

---

## 🧱 Tech Stack

* **Backend:** PHP 8+
* **Database:** MySQL 8 / MariaDB
* **Frontend:** Bootstrap 5, jQuery, Chart.js
* **Security:** AES-256-GCM encryption, CSRF tokens
* **Architecture:** Monolith (clean separation of concerns)

---

## 📁 Project Structure

```
/app        Core application logic (auth, RBAC, crypto)
/public     Public routes, pages, AJAX endpoints
/partials   Shared layout components
/cron       Scheduled background sync jobs
```

---

## ⚙️ Installation & Setup

### 1️⃣ Requirements

* PHP 8.0+
* MySQL 8.0+ or MariaDB
* Apache or Nginx
* CLI access (for cron)
* HTTPS recommended (required for production)

---

### 2️⃣ Database Setup

1. Create a database:

```sql
CREATE DATABASE agency_analytics;
```

2. Import the provided SQL schema:

```
database.sql
```

This will:

* Create all tables
* Seed a demo agency
* Create an owner user
* Create a sample client

---

### 3️⃣ Configuration

Edit:

```
/app/config.php
```

Set:

```php
'db' => [
  'host' => '127.0.0.1',
  'name' => 'agency_analytics',
  'user' => 'db_user',
  'pass' => 'db_password',
],

'app_enc_key' => '64+ HEX CHAR RANDOM KEY',
'cron_key' => 'SECURE_RANDOM_STRING'
```

Generate encryption key:

```bash
openssl rand -hex 32
```

---

### 4️⃣ Login (Demo)

```
/public/login.php?agency=demo-agency
```

Demo credentials:

```
Email: owner@demo.com
Password: Admin123!
```

---

## ⏱️ Running the Cron Sync

The cron job simulates pulling analytics data.
Replace this later with real Stripe / GA API logic.

### Run manually:

```bash
php cron/run_sync.php YOUR_CRON_KEY
```

### Or via web:

```
/cron/run_sync.php?key=YOUR_CRON_KEY
```

Schedule with cron:

```bash
0 * * * * php /path/to/cron/run_sync.php YOUR_CRON_KEY
```

---

## 🔗 Share Links (How It Works)

* Generated per client
* Random token shown **only once**
* Stored as SHA-256 hash
* Revocable instantly
* Optional expiration

Example:

```
https://yourdomain.com/public/share.php?t=TOKEN
```

Security note:

> Tokens cannot be retrieved again. If lost, create a new link and revoke the old one.

---

## 🌐 Custom Domains

1. Add domain in **Agency Settings**
2. Point DNS to your server
3. Configure web server to route to this app
4. Visit:

```
https://customdomain.com/public/login.php
```

Agency is resolved automatically via `HTTP_HOST`.

---

## 🧩 Extending the Platform

### Add New Integrations

* Create provider enum
* Add credential storage
* Extend cron sync logic
* Add metrics to dashboard

### Monetization Ideas

* Per-client billing
* Per-integration pricing
* Share-link usage tracking
* API access tiers
* White-label licensing

### Recommended Next Features

* OAuth for GA4 and Stripe
* Email reports
* PDF export
* Client-level branding
* Usage limits & billing
* API access for agencies

---

## ⚠️ Production Notes

* Enforce HTTPS
* Move config outside web root if possible
* Rotate encryption keys carefully
* Limit share link expiration for compliance
* Add rate limiting if exposed publicly

---

## 📄 License

This project is provided as-is.
Choose an appropriate license based on your intended use:

* MIT (open source)
* Proprietary (commercial SaaS)
* Dual license (recommended)

---

## 🤝 Contribution & Support

This codebase is intentionally:

* Clean
* Auditable
* Extensible

It is suitable as a **commercial SaaS foundation** or **agency internal platform**.

If you want:

* OAuth integrations
* Billing
* Multi-region scaling
* API layer
* React/Vue frontend

Those can be layered cleanly on top of this architecture.

---

**Built for agencies. Designed for scale.**
