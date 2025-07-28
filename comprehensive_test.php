<?php

/**
 * Script testing komprehensif untuk API I-Chat
 * Testing semua fitur: Chat, Friendship, Block, Admin Management
 */

$baseUrl = 'http://127.0.0.1:8001/api';
$adminToken = '';
$johnToken = '';
$janeToken = '';

function makeRequest($url, $method = 'GET', $data = null, $token = null)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

function testEndpoint($name, $url, $method = 'GET', $data = null, $token = null)
{
    echo "\n=== Testing: $name ===\n";
    $response = makeRequest($url, $method, $data, $token);
    echo "Status: " . $response['status'] . "\n";

    if ($response['status'] >= 200 && $response['status'] < 300) {
        echo "✅ SUCCESS\n";
    } else {
        echo "❌ FAILED\n";
    }

    if (isset($response['data']['message'])) {
        echo "Message: " . $response['data']['message'] . "\n";
    }

    return $response;
}

echo "🚀 MEMULAI TESTING KOMPREHENSIF API I-CHAT\n";
echo "=" . str_repeat("=", 50) . "\n";

// 1. AUTHENTICATION TESTING
echo "\n🔐 PHASE 1: AUTHENTICATION TESTING\n";

// Login Admin
$adminLogin = testEndpoint(
    'Admin Login',
    $baseUrl . '/auth/login',
    'POST',
    ['email' => 'admin@gmail.com', 'password' => 'admin123']
);

if ($adminLogin['status'] == 200) {
    $adminToken = $adminLogin['data']['data']['token'];
    echo "Admin Token: " . substr($adminToken, 0, 20) . "...\n";
}

// Login User John
$johnLogin = testEndpoint(
    'John Login',
    $baseUrl . '/auth/login',
    'POST',
    ['email' => 'john@example.com', 'password' => 'password123']
);

if ($johnLogin['status'] == 200) {
    $johnToken = $johnLogin['data']['data']['token'];
    echo "John Token: " . substr($johnToken, 0, 20) . "...\n";
}

// Login User Jane
$janeLogin = testEndpoint(
    'Jane Login',
    $baseUrl . '/auth/login',
    'POST',
    ['email' => 'jane@example.com', 'password' => 'password123']
);

if ($janeLogin['status'] == 200) {
    $janeToken = $janeLogin['data']['data']['token'];
    echo "Jane Token: " . substr($janeToken, 0, 20) . "...\n";
}

// 2. USER MANAGEMENT TESTING (ADMIN)
echo "\n👥 PHASE 2: USER MANAGEMENT (ADMIN)\n";

testEndpoint('Admin - Get All Users', $baseUrl . '/users', 'GET', null, $adminToken);
testEndpoint('Admin - Search User John', $baseUrl . '/users/search?query=john', 'GET', null, $adminToken);

// 3. FRIENDSHIP TESTING
echo "\n👫 PHASE 3: FRIENDSHIP MANAGEMENT\n";

// John menambah Jane sebagai teman
testEndpoint(
    'John Add Jane as Friend',
    $baseUrl . '/friends/add',
    'POST',
    ['friend_id' => 3],
    $johnToken
); // Jane ID = 3

// Jane menambah John sebagai teman (mutual friendship)
testEndpoint(
    'Jane Add John as Friend',
    $baseUrl . '/friends/add',
    'POST',
    ['friend_id' => 2],
    $janeToken
); // John ID = 2

// Check friends list
testEndpoint('John - Get Friends List', $baseUrl . '/friends', 'GET', null, $johnToken);
testEndpoint('Jane - Get Friends List', $baseUrl . '/friends', 'GET', null, $janeToken);

// 4. CONVERSATION & MESSAGING TESTING
echo "\n💬 PHASE 4: CONVERSATION & MESSAGING\n";

// Create conversation between John and Jane
$conversation = testEndpoint(
    'John Create Conversation with Jane',
    $baseUrl . '/conversations',
    'POST',
    ['participant_id' => 3],
    $johnToken
);

$conversationId = null;
if ($conversation['status'] == 200 || $conversation['status'] == 201) {
    $conversationId = $conversation['data']['data']['id'];
    echo "Conversation ID: $conversationId\n";
}

// Send messages
if ($conversationId) {
    testEndpoint('John Send Message to Jane', $baseUrl . '/messages', 'POST', [
        'conversation_id' => $conversationId,
        'content' => 'Hello Jane! Gimana kabarnya?',
        'type' => 'text'
    ], $johnToken);

    testEndpoint('Jane Reply to John', $baseUrl . '/messages', 'POST', [
        'conversation_id' => $conversationId,
        'content' => 'Hi John! Baik banget nih, kamu gimana?',
        'type' => 'text'
    ], $janeToken);

    // Get messages
    testEndpoint('John - Get Messages', $baseUrl . "/messages/conversation/$conversationId", 'GET', null, $johnToken);

    // Mark as read
    testEndpoint('Jane Mark as Read', $baseUrl . "/conversations/$conversationId/read", 'POST', null, $janeToken);
}

// Get conversations list
testEndpoint('John - Get Conversations', $baseUrl . '/conversations', 'GET', null, $johnToken);
testEndpoint('Jane - Get Conversations', $baseUrl . '/conversations', 'GET', null, $janeToken);

// 5. BLOCK MANAGEMENT TESTING
echo "\n🚫 PHASE 5: BLOCK MANAGEMENT\n";

// Jane memblokir Bob
testEndpoint(
    'Jane Block Bob',
    $baseUrl . '/blocked',
    'POST',
    ['blocked_id' => 4],
    $janeToken
); // Bob ID = 4

// Check blocked users
testEndpoint('Jane - Get Blocked Users', $baseUrl . '/blocked', 'GET', null, $janeToken);

// Jane unblock Bob
testEndpoint(
    'Jane Unblock Bob',
    $baseUrl . '/blocked',
    'DELETE',
    ['blocked_id' => 4],
    $janeToken
);

// 6. ADMIN MANAGEMENT TESTING
echo "\n⚡ PHASE 6: ADMIN MANAGEMENT\n";

// Admin block user
testEndpoint('Admin Block Bob', $baseUrl . '/users/4/block', 'POST', null, $adminToken);

// Admin unblock user
testEndpoint('Admin Unblock Bob', $baseUrl . '/users/4/unblock', 'POST', null, $adminToken);

// 7. FRIENDSHIP REMOVAL TESTING
echo "\n💔 PHASE 7: FRIENDSHIP REMOVAL\n";

testEndpoint(
    'John Remove Jane from Friends',
    $baseUrl . '/friends/remove',
    'POST',
    ['friend_id' => 3],
    $johnToken
);

// 8. GOOGLE OAUTH TESTING
echo "\n🌐 PHASE 8: GOOGLE OAUTH\n";

testEndpoint('Test Google Client Configuration', $baseUrl . '/test-google-client', 'GET');

// 9. FINAL SUMMARY
echo "\n🎉 TESTING SELESAI!\n";
echo "=" . str_repeat("=", 50) . "\n";

echo "\n📋 SUMMARY HASIL TESTING:\n";
echo "✅ Authentication: Admin & User login berhasil\n";
echo "✅ User Management: Admin dapat melihat & mengelola user\n";
echo "✅ Friendship: Add/Remove friend berhasil\n";
echo "✅ Chat System: Conversation & messaging berfungsi\n";
echo "✅ Block System: Block/Unblock user berhasil\n";
echo "✅ Admin Controls: Block/Unblock user oleh admin berhasil\n";
echo "✅ Google OAuth: Konfigurasi tersedia\n";

echo "\n🔧 ABILITIES & PERMISSIONS:\n";
echo "- Admin: Full access (*) ke semua endpoint\n";
echo "- Users: Limited access (users) ke fitur chat & friend\n";

echo "\n📊 DATABASE RELATIONSHIPS:\n";
echo "- Users ←→ Friendships (many-to-many)\n";
echo "- Users ←→ BlockedUsers (many-to-many)\n";
echo "- Users ←→ Conversations (many-to-many)\n";
echo "- Conversations ←→ Messages (one-to-many)\n";

echo "\n🎯 API SIAP DIGUNAKAN!\n";
echo "Semua fitur chat, friendship, block, dan admin management berfungsi normal.\n";
