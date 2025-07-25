# 📱 I-Chat API

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Status](https://img.shields.io/badge/Status-Production%20Ready-green.svg)](#)

> **I-Chat API** adalah aplikasi chat backend yang dibangun dengan Laravel 12, mendukung chat real-time, manajemen pertemanan, sistem blokir, dan panel admin yang lengkap.

## ✅ Status Testing

**SEMUA FITUR TELAH DITEST DAN BERFUNGSI NORMAL:**

-   🔐 Authentication (Admin & User login)
-   👥 User Management (Admin panel)
-   👫 Friendship System (Add/Remove friends)
-   💬 Chat System (Conversations & Messaging)
-   🚫 Block System (Block/Unblock users)
-   ⚡ Admin Controls (Full user management)
-   🌐 Google OAuth (Ready for integration)

## 🚀 Quick Start

### 1. Clone & Install

```bash
git clone <repository-url>
cd api-i-chat
composer install
```

### 2. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Setup

```bash
php artisan migrate
php artisan db:seed --class=AdminSeeder
php artisan db:seed --class=DummyUsersSeeder
```

### 4. Start Server

```bash
php artisan serve --host=127.0.0.1 --port=8001
```

## 🔑 Default Accounts

### Admin Account

-   **Email:** `admin@gmail.com`
-   **Password:** `admin123`
-   **Abilities:** Full access (`*`)

### Test Users

-   **John:** `john@example.com` / `password123`
-   **Jane:** `jane@example.com` / `password123`
-   **Bob:** `bob@example.com` / `password123`

## 📚 API Documentation

Lihat [API_DOCUMENTATION.md](API_DOCUMENTATION.md) untuk dokumentasi lengkap endpoint.

### Base URL

```
http://127.0.0.1:8001/api
```

### Quick Example

```bash
# Login as Admin
POST /api/auth/login
{
  "email": "admin@gmail.com",
  "password": "admin123"
}

# Get all users (Admin only)
GET /api/users
Authorization: Bearer {token}
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
