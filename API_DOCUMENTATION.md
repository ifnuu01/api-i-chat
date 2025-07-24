# 📱 I-Chat API Documentation

## 🎯 Overview

I-Chat adalah aplikasi chat API yang mendukung fitur chat real-time, manajemen pertemanan, sistem blokir, dan panel admin.

## 🚀 Hasil Testing

✅ **SEMUA FITUR BERFUNGSI NORMAL:**

-   ✅ Authentication (Admin & User login)
-   ✅ User Management (Admin dapat melihat & mengelola user)
-   ✅ Friendship System (Add/Remove friends)
-   ✅ Chat System (Conversations & Messaging)
-   ✅ Block System (Block/Unblock users)
-   ✅ Admin Controls (Full user management)
-   ✅ Google OAuth (Ready for integration)

## 🔐 Authentication

### Admin Login

**Endpoint:** `POST /api/auth/login`

**Request Body:**

```json
{
    "email": "admin@gmail.com", // Required: string, email format
    "password": "admin123" // Required: string, min 6 characters
}
```

**Response Success (200):**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "token": "1|HV7DZcrECLYWWcOxoXsU7UAzOXAhLewXlMxltNC69b3402ff",
        "name": "Admin",
        "email": "admin@gmail.com",
        "role": "admin"
    }
}
```

**Response Error (401):**

```json
{
    "message": "Invalid credentials"
}
```

### User Login

**Endpoint:** `POST /api/auth/login`

**Request Body:**

```json
{
    "email": "john@example.com", // Required: string, email format
    "password": "password123" // Required: string, min 6 characters
}
```

**Response Success (200):**

```json
{
    "success": true,
    "data": {
        "id": 2,
        "token": "2|XMF4HNDYd9DU2zxDTAyiiA2V0AUUjuxncxaveXkA5346d966",
        "name": "John Doe",
        "email": "john@example.com",
        "role": "users"
    }
}
```

### Google OAuth

**Endpoint:** `POST /api/auth/google`

**Request Body:**

```json
{
    "id_token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9..." // Required: Google ID Token
}
```

**Response Success (200):**

```json
{
    "access_token": "3|YFH5GhJHfg7DhJG8fhJH8fhJH8fhJH8fhJH8fhJH8fhJH",
    "token_type": "Bearer",
    "user": {
        "id": 5,
        "name": "John Google",
        "email": "john@gmail.com",
        "role": "users"
    }
}
```

### Logout

**Endpoint:** `POST /api/auth/logout`

**Headers:**

```
Authorization: Bearer {token}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Logout successful"
}
```

## 👥 User Management (Admin Only)

### Get All Users

**Endpoint:** `GET /api/users`

**Headers:**

```
Authorization: Bearer {admin_token}
```

**Response Success (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": 2,
            "name": "John Doe",
            "email": "john@example.com",
            "email_verified_at": "2025-07-24T13:05:50.000000Z",
            "avatar": null,
            "role": "users",
            "is_blocked": false,
            "created_at": "2025-07-24T13:05:50.000000Z",
            "updated_at": "2025-07-24T13:05:50.000000Z"
        },
        {
            "id": 3,
            "name": "Jane Smith",
            "email": "jane@example.com",
            "email_verified_at": "2025-07-24T13:05:50.000000Z",
            "avatar": null,
            "role": "users",
            "is_blocked": false,
            "created_at": "2025-07-24T13:05:50.000000Z",
            "updated_at": "2025-07-24T13:05:50.000000Z"
        }
    ]
}
```

### Search Users

**Endpoint:** `GET /api/users/search`

**Headers:**

```
Authorization: Bearer {admin_token}
```

**Query Parameters:**

-   `query` (required): string, minimum 2 characters

**Example:** `GET /api/users/search?query=john`

**Response Success (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": 2,
            "name": "John Doe",
            "email": "john@example.com"
        }
    ]
}
```

### Get User Profile

**Endpoint:** `GET /api/user/profile`

**Headers:**

```
Authorization: Bearer {token}
```

**Response Success (200):**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Admin",
        "email": "admin@gmail.com",
        "email_verified_at": null,
        "avatar": null,
        "role": "admin",
        "is_blocked": false,
        "created_at": "2025-07-24T10:30:00.000000Z",
        "updated_at": "2025-07-24T10:30:00.000000Z"
    }
}
```

### Block User (Admin)

**Endpoint:** `POST /api/users/{user_id}/block`

**Headers:**

```
Authorization: Bearer {admin_token}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "User berhasil diblokir"
}
```

### Unblock User (Admin)

**Endpoint:** `POST /api/users/{user_id}/unblock`

**Headers:**

```
Authorization: Bearer {admin_token}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "User berhasil di-unblock"
}
```

### Delete User (Admin)

**Endpoint:** `DELETE /api/users/{user_id}`

**Headers:**

```
Authorization: Bearer {admin_token}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "User berhasil dihapus"
}
```

## 👫 Friendship Management

### Add Friend

**Endpoint:** `POST /api/friends/add`

**Headers:**

```
Authorization: Bearer {user_token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "friend_id": 3 // Required: integer, existing user ID
}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Anda berhasil berteman"
}
```

**Response Error (400):**

```json
{
    "error": "Kamu sudah berteman"
}
```

### Remove Friend

**Endpoint:** `POST /api/friends/remove`

**Headers:**

```
Authorization: Bearer {user_token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "friend_id": 3 // Required: integer, existing friend ID
}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Berhasil menghapus pertemanan"
}
```

**Response Error (400):**

```json
{
    "error": "User belum pernah berteman"
}
```

### Get Friends List

**Endpoint:** `GET /api/friends`

**Headers:**

```
Authorization: Bearer {user_token}
```

**Response Success (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": 3,
            "name": "Jane Smith",
            "email": "jane@example.com"
        },
        {
            "id": 4,
            "name": "Bob Wilson",
            "email": "bob@example.com"
        }
    ]
}
```

## 💬 Chat System

### Create Conversation

**Endpoint:** `POST /api/conversations`

**Headers:**

```
Authorization: Bearer {user_token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "participant_id": 3 // Required: integer, user ID to chat with
}
```

**Response Success (201):**

```json
{
    "success": true,
    "message": "Conversation created successfully",
    "data": {
        "id": 1,
        "user1_id": 2,
        "user2_id": 3,
        "user1_last_read_at": "2025-07-24T13:06:30.000000Z",
        "user2_last_read_at": "2025-07-24T13:06:30.000000Z",
        "last_message_at": "2025-07-24T13:06:30.000000Z",
        "created_at": "2025-07-24T13:06:30.000000Z",
        "updated_at": "2025-07-24T13:06:30.000000Z",
        "user1": {
            "id": 2,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "user2": {
            "id": 3,
            "name": "Jane Smith",
            "email": "jane@example.com"
        },
        "other_participant": {
            "id": 3,
            "name": "Jane Smith",
            "email": "jane@example.com"
        }
    }
}
```

### Get Conversations

**Endpoint:** `GET /api/conversations`

**Headers:**

```
Authorization: Bearer {user_token}
```

**Response Success (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user1_id": 2,
            "user2_id": 3,
            "user1_last_read_at": "2025-07-24T13:06:30.000000Z",
            "user2_last_read_at": "2025-07-24T13:07:45.000000Z",
            "last_message_at": "2025-07-24T13:07:30.000000Z",
            "created_at": "2025-07-24T13:06:30.000000Z",
            "updated_at": "2025-07-24T13:07:45.000000Z",
            "other_participant": {
                "id": 3,
                "name": "Jane Smith",
                "email": "jane@example.com"
            },
            "last_message": {
                "id": 2,
                "content": "Hi John! Baik banget nih, kamu gimana?",
                "type": "text",
                "created_at": "2025-07-24T13:07:30.000000Z"
            },
            "unread_count": 0
        }
    ]
}
```

### Send Message

**Endpoint:** `POST /api/messages`

**Headers:**

```
Authorization: Bearer {user_token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "conversation_id": 1, // Required: integer, existing conversation ID
    "content": "Hello! How are you?", // Required if type=text: string, max 1000 chars
    "type": "text", // Optional: text|image|file (default: text)
    "reply_to_id": null, // Optional: integer, message ID to reply to
    "file": null // Required if type=image|file: file, max 10MB
}
```

**Response Success (201):**

```json
{
    "success": true,
    "message": "Message sent successfully",
    "data": {
        "id": 1,
        "conversation_id": 1,
        "sender_id": 2,
        "reply_to_id": null,
        "content": "Hello! How are you?",
        "type": "text",
        "file_url": null,
        "file_name": null,
        "file_size": null,
        "is_edited": false,
        "is_deleted": false,
        "edited_at": null,
        "deleted_at": null,
        "created_at": "2025-07-24T13:07:00.000000Z",
        "updated_at": "2025-07-24T13:07:00.000000Z",
        "sender": {
            "id": 2,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "reply_to": null
    }
}
```

### Get Messages

**Endpoint:** `GET /api/messages/conversation/{conversation_id}`

**Headers:**

```
Authorization: Bearer {user_token}
```

**Query Parameters:**

-   `page` (optional): integer, page number (default: 1)
-   `limit` (optional): integer, messages per page (default: 50)

**Example:** `GET /api/messages/conversation/1?page=1&limit=20`

**Response Success (200):**

```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 2,
                "conversation_id": 1,
                "sender_id": 3,
                "reply_to_id": 1,
                "content": "Hi John! Baik banget nih, kamu gimana?",
                "type": "text",
                "file_url": null,
                "file_name": null,
                "file_size": null,
                "is_edited": false,
                "is_deleted": false,
                "created_at": "2025-07-24T13:07:30.000000Z",
                "sender": {
                    "id": 3,
                    "name": "Jane Smith",
                    "email": "jane@example.com"
                },
                "reply_to": {
                    "id": 1,
                    "content": "Hello! How are you?",
                    "sender_id": 2
                }
            },
            {
                "id": 1,
                "conversation_id": 1,
                "sender_id": 2,
                "reply_to_id": null,
                "content": "Hello! How are you?",
                "type": "text",
                "file_url": null,
                "file_name": null,
                "file_size": null,
                "is_edited": false,
                "is_deleted": false,
                "created_at": "2025-07-24T13:07:00.000000Z",
                "sender": {
                    "id": 2,
                    "name": "John Doe",
                    "email": "john@example.com"
                },
                "reply_to": null
            }
        ],
        "first_page_url": "http://127.0.0.1:8001/api/messages/conversation/1?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://127.0.0.1:8001/api/messages/conversation/1?page=1",
        "links": [],
        "next_page_url": null,
        "path": "http://127.0.0.1:8001/api/messages/conversation/1",
        "per_page": 50,
        "prev_page_url": null,
        "to": 2,
        "total": 2
    }
}
```

### Edit Message

**Endpoint:** `PUT /api/messages/{message_id}`

**Headers:**

```
Authorization: Bearer {user_token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "content": "Updated message content" // Required: string, max 1000 chars
}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Message updated successfully",
    "data": {
        "id": 1,
        "conversation_id": 1,
        "sender_id": 2,
        "content": "Updated message content",
        "type": "text",
        "is_edited": true,
        "edited_at": "2025-07-24T13:08:00.000000Z",
        "created_at": "2025-07-24T13:07:00.000000Z",
        "updated_at": "2025-07-24T13:08:00.000000Z",
        "sender": {
            "id": 2,
            "name": "John Doe",
            "email": "john@example.com"
        }
    }
}
```

### Delete Message

**Endpoint:** `DELETE /api/messages/{message_id}`

**Headers:**

```
Authorization: Bearer {user_token}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Message deleted successfully"
}
```

### Mark Conversation as Read

**Endpoint:** `POST /api/conversations/{conversation_id}/read`

**Headers:**

```
Authorization: Bearer {user_token}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Conversation marked as read"
}
```

## 🚫 Block Management

### Block User

**Endpoint:** `POST /api/blocked`

**Headers:**

```
Authorization: Bearer {user_token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "blocked_id": 4 // Required: integer, user ID to block
}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "User berhasil diblokir"
}
```

**Response Error (400):**

```json
{
    "error": "User sudah diblokir"
}
```

### Unblock User

**Endpoint:** `DELETE /api/blocked`

**Headers:**

```
Authorization: Bearer {user_token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "blocked_id": 4 // Required: integer, user ID to unblock
}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Berhasil membuka blok"
}
```

**Response Error (400):**

```json
{
    "error": "User belum pernah diblokir"
}
```

### Get Blocked Users

**Endpoint:** `GET /api/blocked`

**Headers:**

```
Authorization: Bearer {user_token}
```

**Response Success (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": 4,
            "name": "Bob Wilson",
            "email": "bob@example.com"
        }
    ]
}
```

## 🔑 Abilities & Permissions

### Admin Abilities

-   `*` (Full access to all endpoints)
-   Dapat mengelola semua user
-   Dapat memblokir/unblock user
-   Dapat menghapus akun user

### User Abilities

-   `users` (Limited access)
-   Dapat chat dengan sesama user
-   Dapat mengelola pertemanan
-   Dapat memblokir user lain

## 📊 Database Structure

### Tables:

-   `users` - User data with roles (admin/users)
-   `friendships` - User friendships (many-to-many)
-   `blocked_users` - Blocked users (many-to-many)
-   `conversations` - Chat conversations between users
-   `messages` - Messages in conversations
-   `personal_access_tokens` - API tokens (Laravel Sanctum)

## 🎯 Key Features

### ✅ Tested & Working:

1. **Authentication System**

    - Admin login dengan email/password
    - User login dengan email/password
    - Google OAuth ready

2. **Chat Functionality**

    - Create conversations
    - Send/receive messages
    - Edit/delete messages
    - Mark as read
    - File attachments (ready)

3. **Friendship System**

    - Add friends
    - Remove friends
    - View friends list
    - Mutual friendship support

4. **Block System**

    - Block/unblock users
    - View blocked users list
    - Admin can override blocks

5. **Admin Panel**
    - View all users
    - Search users
    - Block/unblock any user
    - Delete user accounts
    - Full system control

## 🚀 How to Run

1. **Start Server:**

    ```bash
    php artisan serve --host=127.0.0.1 --port=8001
    ```

2. **Admin Access:**

    - Email: `admin@gmail.com`
    - Password: `admin123`

3. **Test Users:**
    - John: `john@example.com` / `password123`
    - Jane: `jane@example.com` / `password123`
    - Bob: `bob@example.com` / `password123`

## 📈 API Status: ✅ PRODUCTION READY

Semua endpoint telah ditest dan berfungsi dengan normal. API siap untuk deployment dan integrasi dengan frontend aplikasi chat.

## 📖 Usage Scenarios & Examples

### 🎯 Scenario 1: Admin Managing Users

**Step 1: Admin Login**

```bash
POST /api/auth/login
{
  "email": "admin@gmail.com",
  "password": "admin123"
}
# Response: token untuk admin
```

**Step 2: View All Users**

```bash
GET /api/users
Authorization: Bearer {admin_token}
# Response: list semua users (exclude admin)
```

**Step 3: Search Specific User**

```bash
GET /api/users/search?query=john
Authorization: Bearer {admin_token}
# Response: users yang namanya mengandung "john"
```

**Step 4: Block Troublesome User**

```bash
POST /api/users/4/block
Authorization: Bearer {admin_token}
# Response: user dengan ID 4 diblokir
```

### 🎯 Scenario 2: User Chat Flow

**Step 1: User Login**

```bash
POST /api/auth/login
{
  "email": "john@example.com",
  "password": "password123"
}
# Response: token untuk John (ID: 2)
```

**Step 2: Search for Friends**

```bash
GET /api/users/search?query=jane
Authorization: Bearer {john_token}
# Response: find Jane (ID: 3)
```

**Step 3: Add Friend**

```bash
POST /api/friends/add
Authorization: Bearer {john_token}
{
  "friend_id": 3
}
# Response: berhasil berteman dengan Jane
```

**Step 4: Create Conversation**

```bash
POST /api/conversations
Authorization: Bearer {john_token}
{
  "participant_id": 3
}
# Response: conversation dibuat (ID: 1)
```

**Step 5: Send Message**

```bash
POST /api/messages
Authorization: Bearer {john_token}
{
  "conversation_id": 1,
  "content": "Hi Jane! Apa kabar?",
  "type": "text"
}
# Response: message terkirim
```

**Step 6: Jane Reply (switch to Jane's token)**

```bash
POST /api/auth/login
{
  "email": "jane@example.com",
  "password": "password123"
}

POST /api/messages
Authorization: Bearer {jane_token}
{
  "conversation_id": 1,
  "content": "Hai John! Baik kok, kamu gimana?",
  "type": "text",
  "reply_to_id": 1
}
# Response: reply message terkirim
```

**Step 7: John Read Messages**

```bash
GET /api/messages/conversation/1
Authorization: Bearer {john_token}
# Response: semua messages dalam conversation

POST /api/conversations/1/read
Authorization: Bearer {john_token}
# Response: conversation marked as read
```

### 🎯 Scenario 3: Block User Flow

**Step 1: User Experience Harassment**

```bash
# Jane menerima pesan tidak pantas dari Bob
GET /api/messages/conversation/2
Authorization: Bearer {jane_token}
# Response: messages dari Bob yang tidak pantas
```

**Step 2: Block User**

```bash
POST /api/blocked
Authorization: Bearer {jane_token}
{
  "blocked_id": 4
}
# Response: Bob (ID: 4) berhasil diblokir
```

**Step 3: Check Blocked List**

```bash
GET /api/blocked
Authorization: Bearer {jane_token}
# Response: list users yang diblokir Jane
```

**Step 4: Later Unblock (if needed)**

```bash
DELETE /api/blocked
Authorization: Bearer {jane_token}
{
  "blocked_id": 4
}
# Response: Bob berhasil di-unblock
```

### 🎯 Scenario 4: Google OAuth Integration

**Step 1: Frontend Gets Google ID Token**

```javascript
// Frontend code (example)
function handleGoogleLogin(response) {
    const idToken = response.credential;

    // Send to backend
    fetch("/api/auth/google", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            id_token: idToken,
        }),
    });
}
```

**Step 2: Backend Processes Token**

```bash
POST /api/auth/google
{
  "id_token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9..."
}
# Response: user dibuat/login otomatis dengan Google account
```

## 🔧 Error Handling Examples

### Validation Errors (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 6 characters."]
    }
}
```

### Unauthorized (401)

```json
{
    "message": "Unauthenticated."
}
```

### Forbidden (403)

```json
{
    "message": "This action is unauthorized."
}
```

### Not Found (404)

```json
{
    "error": "Conversation not found or access denied"
}
```

### Server Error (500)

```json
{
    "message": "Server Error",
    "error": "Internal server error occurred"
}
```

## 💡 Best Practices

### 1. Token Management

-   Simpan token di secure storage (localStorage/sessionStorage)
-   Include token di header untuk setiap authenticated request
-   Handle token expiry dan refresh

### 2. Pagination

-   Gunakan parameter `page` dan `limit` untuk large datasets
-   Default limit adalah 50 items per page

### 3. File Uploads

-   Maximum file size: 10MB
-   Supported types: images, documents
-   Files disimpan di storage/app/public/chat-files

### 4. Real-time Features

-   Implement WebSocket/Pusher untuk real-time messaging
-   Poll conversation list untuk update notifications
-   Use conversation `last_message_at` untuk sorting

### 5. Security

-   Validate semua input di frontend dan backend
-   Implement rate limiting untuk API calls
-   Log suspicious activities untuk admin review
