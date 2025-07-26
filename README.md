# � I-Chat API

Real-time chat application backend built with Laravel. Supports messaging, friendship management and admin controls.

## 🎯 What is this?

**I-Chat API** adalah backend untuk aplikasi chat yang menyediakan:

-   **Authentication** - Login/register dengan token
-   **Friendship System** - Add, accept, reject friends
-   **Real-time Messaging** - Send text, images, files
-   **User Management** - Block/unblock users
-   **Admin Panel** - Full user control

## 🚀 Quick Setup

### 1. Install Dependencies

```bash
git clone <repository-url>
cd api-i-chat
composer install
```

### 2. Setup Database

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

### 3. Start Server

```bash
php artisan serve --host=127.0.0.1 --port=8001
```

## 🔑 Test Accounts

| Role  | Email            | Password    |
| ----- | ---------------- | ----------- |
| Admin | admin@gmail.com  | admin123    |
| User  | john@example.com | password123 |
| User  | jane@example.com | password123 |

## 📡 API Endpoints

**Base URL:** `http://127.0.0.1:8001/api`

### Authentication

```
POST /auth/login      → Login
POST /auth/register   → Register
POST /auth/logout     → Logout
```

### Friendship Management

```
GET    /friends              → List friends
POST   /friends              → Send friend request
GET    /friends/requests     → Get friend requests
POST   /friends/accept       → Accept friend request
POST   /friends/reject       → Reject friend request
DELETE /friends              → Remove friend
GET    /friends/search       → Search users
```

### Conversations

```
GET    /conversations        → List conversations
GET    /conversations/search → Search conversations
GET    /conversations/{id}   → Get conversation details
POST   /conversations/{id}/read → Mark as read
```

### Messages

```
GET    /messages/conversation/{id}  → Get messages (paginated)
POST   /messages                    → Send message
GET    /messages/{id}               → Get message details
PUT    /messages/{id}               → Edit message
DELETE /messages/{id}               → Delete message
POST   /messages/mark-as-read       → Mark messages as read
```

### User Management (Admin)

```
GET    /users           → List all users
GET    /users/search    → Search users
DELETE /users/{id}      → Delete user
POST   /users/{id}/block → Block user
```

## � API Usage

### Authentication

#### Login

```bash
POST /api/auth/login
{
  "email": "john@example.com",
  "password": "password123"
}
```

#### Register

```bash
POST /api/auth/register
{
  "name": "New User",
  "email": "newuser@example.com",
  "password": "password123"
}
```

#### Logout

```bash
POST /api/auth/logout
Authorization: Bearer {token}
```

### Friendship Management

#### Get Friend List

```bash
GET /api/friends
Authorization: Bearer {token}
```

#### Send Friend Request

```bash
POST /api/friends
Authorization: Bearer {token}
{
  "friend_id": 2
}
```

#### Get Friend Requests

```bash
GET /api/friends/requests
Authorization: Bearer {token}
```

#### Accept Friend Request

```bash
POST /api/friends/accept
Authorization: Bearer {token}
{
  "friend_id": 2
}
```

#### Reject Friend Request

```bash
POST /api/friends/reject
Authorization: Bearer {token}
{
  "friend_id": 2
}
```

#### Remove Friend

```bash
DELETE /api/friends
Authorization: Bearer {token}
{
  "friend_id": 2
}
```

#### Search Users

```bash
GET /api/friends/search?query=john
Authorization: Bearer {token}
```

### Conversations

#### Get All Conversations

```bash
GET /api/conversations
Authorization: Bearer {token}
```

#### Search Conversations

```bash
GET /api/conversations/search?query=jane
Authorization: Bearer {token}
```

#### Get Conversation Details

```bash
GET /api/conversations/1
Authorization: Bearer {token}
```

#### Mark Conversation as Read

```bash
POST /api/conversations/1/read
Authorization: Bearer {token}
```

### Messages

#### Get Messages (Paginated)

```bash
GET /api/messages/conversation/1?page=1&limit=20
Authorization: Bearer {token}
```

#### Send Text Message

```bash
POST /api/messages
Authorization: Bearer {token}
{
  "conversation_id": 1,
  "content": "Hello! How are you?",
  "type": "text"
}
```

#### Send File/Image Message

```bash
POST /api/messages
Authorization: Bearer {token}
Content-Type: multipart/form-data

conversation_id: 1
type: image
file: [file upload]
```

#### Reply to Message

```bash
POST /api/messages
Authorization: Bearer {token}
{
  "conversation_id": 1,
  "content": "Thanks for the info!",
  "type": "text",
  "reply_to_id": 15
}
```

#### Get Message Details

```bash
GET /api/messages/15
Authorization: Bearer {token}
```

#### Edit Message

```bash
PUT /api/messages/15
Authorization: Bearer {token}
{
  "content": "Updated message content"
}
```

#### Delete Message

```bash
DELETE /api/messages/15
Authorization: Bearer {token}
```

#### Mark Messages as Read

```bash
POST /api/messages/mark-as-read
Authorization: Bearer {token}
{
  "conversation_id": 1
}
```

### User Management (Admin Only)

#### Get All Users

```bash
GET /api/users
Authorization: Bearer {admin_token}
```

#### Search Users

```bash
GET /api/users/search?query=john
Authorization: Bearer {admin_token}
```

#### Get User Details

```bash
GET /api/users/2
Authorization: Bearer {admin_token}
```

#### Delete User

```bash
DELETE /api/users/2
Authorization: Bearer {admin_token}
```

#### Block User

```bash
POST /api/users/2/block
Authorization: Bearer {admin_token}
```

#### Unblock User

```bash
POST /api/users/2/unblock
Authorization: Bearer {admin_token}
```

## 🛠️ Features

-   ✅ **JWT Authentication** with Sanctum
-   ✅ **Real-time messaging** ready
-   ✅ **File uploads** (images, documents)
-   ✅ **Message editing** and deletion
-   ✅ **Read receipts** tracking
-   ✅ **Friend system** with pending/accepted status
-   ✅ **User blocking** system
-   ✅ **Admin controls** for user management
-   ✅ **Pagination** for performance
-   ✅ **Raw SQL queries** for optimization

## 📱 Frontend Integration

This API is ready to be consumed by:

-   **Mobile apps** (React Native, Flutter)
-   **Web apps** (React, Vue, Angular)
-   **Desktop apps** (Electron)

All endpoints return JSON responses with consistent structure.
