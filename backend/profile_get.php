<?php
// backend/profile_get.php
header('Content-Type: application/json');
require __DIR__ . '/db/mysql.php';
require __DIR__ . '/db/redis.php';
$config = require __DIR__ . '/db/config.php';

function getTokenFromRequest() {
    $headers = getallheaders();
    if (!empty($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $m)) return $m[1];
    } elseif (!empty($headers['authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['authorization'], $m)) return $m[1];
    }
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_GET ?: $_POST;
    if (!empty($data['token'])) return $data['token'];
    return null;
}

$token = getTokenFromRequest();
if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Missing token']);
    exit;
}

$sessionKey = "session:$token";
$sessionData = null;
if ($redis) {
    $sessionData = $redis->get($sessionKey);
} else {
    $path = sys_get_temp_dir() . "/session_$token.json";
    if (file_exists($path)) $sessionData = file_get_contents($path);
}

if (!$sessionData) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or expired token']);
    exit;
}

$session = json_decode($sessionData, true);
$user_id = (int)$session['user_id'];

try {
    $stmt = $pdo->prepare('SELECT id, name, email, age, dob, contact, created_at, updated_at FROM users WHERE id = :id');
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    echo json_encode(['success' => true, 'user' => $user]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
    exit;
}
