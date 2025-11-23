<?php
header('Content-Type: application/json');

require __DIR__ . '/db/mysql.php';
require __DIR__ . '/db/redis.php';
if (file_exists(__DIR__ . '/db/mongo.php')) {
    require __DIR__ . '/db/mongo.php';
}

function getToken() {
    $headers = getallheaders();
    if (!empty($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $m)) return $m[1];
    }
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return $data['token'] ?? null;
}

$token = getToken();
if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Missing token']);
    exit;
}

$sessionKey = "session:$token";
$sessionData = $redis ? $redis->get($sessionKey) : null;

if (!$sessionData) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or expired token']);
    exit;
}

$session = json_decode($sessionData, true);
$user_id = (int)$session['user_id'];

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$currentPassword = $data['currentPassword'] ?? '';
$newPassword     = $data['newPassword'] ?? '';
$confirmPassword = $data['confirmNewPassword'] ?? '';

if (!$currentPassword || !$newPassword || !$confirmPassword) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    http_response_code(400);
    echo json_encode(['error' => 'New passwords do not match']);
    exit;
}

if (strlen($newPassword) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 6 characters']);
    exit;
}

try {
    // Fetch user
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Current password incorrect']);
        exit;
    }

    // Update password
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

    $update = $pdo->prepare("UPDATE users SET password_hash = :ph WHERE id = :id");
    $update->execute([
        'ph' => $newHash,
        'id' => $user_id
    ]);



    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
    exit;
}
?>
