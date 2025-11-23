<?php
// backend/login.php
header('Content-Type: application/json');
require __DIR__ . '/db/mysql.php';
require __DIR__ . '/db/redis.php';
$config = require __DIR__ . '/db/config.php';
$session_ttl = $config['session_ttl'] ?? 86400;

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || !isset($data['email'], $data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
$password = $data['password'];

try {
    $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }

    // Generate token
    $token = bin2hex(random_bytes(32));
    $sessionKey = "session:$token";
    $sessionData = json_encode([
        'user_id' => (int)$user['id'],
        'created_at' => time()
    ]);

    if ($redis) {
        $redis->set($sessionKey, $sessionData, $session_ttl);
    } else {
        // fallback: store in temp file (development only)
        file_put_contents(sys_get_temp_dir() . "/session_$token.json", $sessionData);
    }

    echo json_encode(['success' => true, 'token' => $token, 'expires_in' => $session_ttl]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
    exit;
}
