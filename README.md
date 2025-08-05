# I-Chat API Documentation

API untuk aplikasi chat real-time dengan fitur manajemen user, pertemanan, dan pesan.

## 📋 Daftar Isi

-   [Instalasi](#instalasi)
-   [Konfigurasi](#konfigurasi)
-   [Authentication](#authentication)
-   [Endpoints](#endpoints)
    -   [Authentication](#authentication-endpoints)
    -   [User Management](#user-management)
    -   [Profile Management](#profile-management)
    -   [Friendship Management](#friendship-management)
    -   [Block Management](#block-management)
    -   [Conversation Management](#conversation-management)
    -   [Message Management](#message-management)
    -   [Admin Endpoints](#admin-endpoints)
-   [Error Handling](#error-handling)
-   [WebSocket Events](#websocket-events)

## 🚀 Instalasi

```bash
# Clone repository
git clone https://github.com/ifnuu01/api-i-chat.git
cd api-i-chat

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Start server
php artisan serve
```

## ⚙️ Konfigurasi

### Environment Variables

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ichat_db
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=reverb
REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret

GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
```

## 🔐 Authentication

API menggunakan Laravel Sanctum untuk autentikasi dengan Bearer Token.

### Headers

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Base URL

```
http://localhost:8000/api
```

## 📡 Endpoints

### Authentication Endpoints

#### 1. Login

Masuk ke sistem menggunakan email dan password.

**Endpoint:** `POST /auth/login`

**Request Body:**

```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response Success (200):**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "token": "1|laravel_sanctum_token",
        "name": "John Doe",
        "email": "user@example.com",
        "role": "users"
    }
}
```

**Response Error (401):**

```json
{
    "success": false,
    "message": "Invalid credentials"
}
```

#### 2. Register

Mendaftar akun baru.

**Endpoint:** `POST /auth/register`

**Request Body:**

```json
{
    "name": "John Doe",
    "email": "user@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response Success (201):**

```json
{
    "success": true,
    "message": "Registration successful",
    "data": {
        "id": 1,
        "token": "1|laravel_sanctum_token",
        "name": "John Doe",
        "email": "user@example.com",
        "role": "users"
    }
}
```

#### 3. Google Login

Login menggunakan Google OAuth.

**Endpoint:** `POST /auth/google`

**Request Body:**

```json
{
    "id_token": "google_id_token"
}
```

#### 4. Logout

Keluar dari sistem.

**Endpoint:** `POST /auth/logout`

**Headers:** `Authorization: Bearer {token}`

**Response Success (200):**

```json
{
    "success": true,
    "message": "Logout successful"
}
```

---

### User Management

#### 1. Get Current User Profile

**Endpoint:** `GET /user/profile`

**Headers:** `Authorization: Bearer {token}`

**Response Success (200):**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "avatar": null,
        "role": "users",
        "created_at": "2024-01-01T00:00:00Z"
    }
}
```

#### 2. Get All Users

**Endpoint:** `GET /users`

**Headers:** `Authorization: Bearer {token}`

#### 3. Search Users

**Endpoint:** `GET /users/search?query={search_term}`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**

-   `query` (required): Kata kunci pencarian

#### 4. Get User Detail

**Endpoint:** `GET /users/{id}`

**Headers:** `Authorization: Bearer {token}`

---

### Profile Management

#### 1. Update Avatar

**Endpoint:** `POST /profile/avatar`

**Headers:**

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**

```
avatar: [file] (image: jpeg,png,jpg,gif, max: 5MB)
```

#### 2. Update Profile

**Endpoint:** `PUT /profile/update`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "name": "John Doe Updated",
    "email": "newemail@example.com"
}
```

#### 3. Update Password

**Endpoint:** `PUT /profile/password`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "current_password": "oldpassword",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}
```

---

### Friendship Management

#### 1. Get Friends List

**Endpoint:** `GET /friends`

**Headers:** `Authorization: Bearer {token}`

#### 2. Send Friend Request

**Endpoint:** `POST /friends/add`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "friend_id": 2
}
```

#### 3. Get Friend Requests

**Endpoint:** `GET /friends/requests`

**Headers:** `Authorization: Bearer {token}`

#### 4. Accept Friend Request

**Endpoint:** `POST /friends/accept`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "friendship_id": 2
}
```

#### 5. Reject Friend Request

**Endpoint:** `POST /friends/reject`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "friendship_id": 2
}
```

#### 6. Remove Friend

**Endpoint:** `POST /friends/remove`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "friend_id": 2
}
```

#### 7. Cancel Friend Request

**Endpoint:** `POST /friends/cancel`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "friend_id": 2
}
```

#### 8. Search Friends

**Endpoint:** `GET /friends/search?query={search_term}`

**Headers:** `Authorization: Bearer {token}`

---

### Block Management

#### 1. Get Blocked Users

**Endpoint:** `GET /blocked`

**Headers:** `Authorization: Bearer {token}`

#### 2. Block User

**Endpoint:** `POST /blocked`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "user_id": 2
}
```

#### 3. Unblock User

**Endpoint:** `DELETE /blocked`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "user_id": 2
}
```

---

### Conversation Management

#### 1. Get Conversations

**Endpoint:** `GET /conversations`

**Headers:** `Authorization: Bearer {token}`

**Response Success (200):**

```json
[
    {
        "id": 1,
        "user1_id": 1,
        "user2_id": 2,
        "last_message_at": "2024-01-01T01:00:00Z",
        "created_at": "2024-01-01T00:00:00Z",
        "other_participant": {
            "id": 2,
            "name": "Jane Smith",
            "email": "jane@example.com",
            "avatar": null
        },
        "last_message": {
            "id": 5,
            "content": "See you later!",
            "created_at": "2024-01-01T01:00:00Z"
        },
        "unread_count": 2
    }
]
```

#### 2. Search Conversations

**Endpoint:** `GET /conversations/search?query={search_term}`

**Headers:** `Authorization: Bearer {token}`

---

### Message Management

#### 1. Get Messages

**Endpoint:** `GET /messages/conversation/{conversationId}?page=1&limit=50`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**

-   `page` (optional): Halaman (default: 1)
-   `limit` (optional): Jumlah pesan per halaman (default: 50, max: 100)

#### 2. Send Message

**Endpoint:** `POST /messages`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "conversation_id": 1,
    "content": "Hello, how are you?",
    "reply_to_id": null
}
```

#### 3. Update Message

**Endpoint:** `PUT /messages`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "message_id": 2,
    "content": "Hello, how are you doing?"
}
```

#### 4. Delete Message

**Endpoint:** `DELETE /messages`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "message_id": 2
}
```

#### 5. Mark Messages as Read

**Endpoint:** `POST /messages/mark-as-read`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "conversation_id": 1
}
```

---

### Admin Endpoints

_Hanya dapat diakses oleh user dengan role `admin`_

#### 1. Delete User

**Endpoint:** `DELETE /users/{id}`

**Headers:** `Authorization: Bearer {admin_token}`

#### 2. Block User (Admin)

**Endpoint:** `POST /users/{id}/block`

**Headers:** `Authorization: Bearer {admin_token}`

#### 3. Unblock User (Admin)

**Endpoint:** `POST /users/{id}/unblock`

**Headers:** `Authorization: Bearer {admin_token}`

---

## ❌ Error Handling

### Common Error Responses

**401 Unauthorized:**

```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

**403 Forbidden:**

```json
{
    "success": false,
    "message": "Unauthorized access"
}
```

**404 Not Found:**

```json
{
    "success": false,
    "message": "Resource not found"
}
```

**422 Validation Error:**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

**500 Internal Server Error:**

```json
{
    "success": false,
    "message": "Internal server error"
}
```

---

## 🔄 WebSocket Events

API ini mendukung real-time messaging melalui WebSocket dengan events:

-   `MessageSent` - Ketika pesan baru dikirim
-   `MessageUpdated` - Ketika pesan diedit
-   `MessageDeleted` - Ketika pesan dihapus

**Channel:** `chat.{conversation_id}`

**Connection:**

```javascript
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "reverb",
    key: process.env.VITE_REVERB_APP_KEY,
    wsHost: process.env.VITE_REVERB_HOST,
    wsPort: process.env.VITE_REVERB_PORT,
    wssPort: process.env.VITE_REVERB_PORT,
    forceTLS: (process.env.VITE_REVERB_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],
});
```

---

## 📊 Rate Limiting

-   Authentication endpoints: 60 requests per minute
-   Other endpoints: 1000 requests per minute per user

## 📝 Notes

-   Semua timestamp menggunakan format ISO 8601 UTC
-   File upload maksimal 5MB untuk avatar
-   Pesan yang dihapus menggunakan soft delete
-   Sistem role: `admin` dan `users`
-   API menggunakan Laravel Sanctum untuk autentikasi
-   WebSocket menggunakan Laravel Reverb

## 🛠️ Development

### Running Tests

```bash
php artisan test
```

### Code Style

```bash
./vendor/bin/pint
```

### Generate API Documentation

```bash
php artisan l5-swagger:generate
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📞 Support

Jika ada pertanyaan atau masalah, silakan buat issue di repository ini atau hubungi:

-   Email: ifnuu01@gmail.com
-   GitHub: [@ifnuu01](https://github.com/ifnuu01)
