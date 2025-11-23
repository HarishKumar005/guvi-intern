<?php
// backend/logout.php
header('Content-Type: application/json');
require __DIR__ . '/db/redis.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;
$token = $data['token'] ?? null;

if (!$token) {
    // also allow Authorization header
    $headers = getallheaders();
    if (!empty($headers['Authorization']) && preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $m)) {
        $token = $m[1];
    }
}

if (!$token) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing token']);
    exit;
}

$sessionKey = "session:$token";
$deleted = false;
if ($redis) {
    $deleted = $redis->del($sessionKey);
} else {
    $path = sys_get_temp_dir() . "/session_$token.json";
    if (file_exists($path)) {
        unlink($path);
        $deleted = true;
    }
}

echo json_encode(['success' => true, 'deleted' => (bool)$deleted]);
exit;
