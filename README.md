# 📱 I-Chat API

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Status](https://img.shields.io/badge/Status-Production%20Ready-green.svg)](#)

> **I-Chat API** adalah aplikasi chat backend yang dibangun dengan Laravel 11, mendukung chat real-time, manajemen pertemanan, sistem blokir, dan panel admin yang lengkap.

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

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com)**
-   **[Tighten Co.](https://tighten.co)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Redberry](https://redberry.international/laravel-development)**
-   **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
