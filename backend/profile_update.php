<?php
// backend/profile_update.php
header('Content-Type: application/json');
require __DIR__ . '/db/mysql.php';
require __DIR__ . '/db/redis.php';
if (file_exists(__DIR__ . '/db/mongo.php')) {
    require __DIR__ . '/db/mongo.php';
}

$config = require __DIR__ . '/db/config.php';

function getToken() {
    $headers = getallheaders();
    if (!empty($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $m)) return $m[1];
    } elseif (!empty($headers['authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['authorization'], $m)) return $m[1];
    }
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: $_POST;
    return $data['token'] ?? null;
}

$token = getToken();
if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Missing token']);
    exit;
}

$sessionKey = "session:$token";
$sessionData = null;
if ($redis) {
    $sessionData = $redis->get($sessionKey);
}
if (!$sessionData) {
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

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Acceptable fields to update
$fields = [];
$params = ['id' => $user_id];
if (isset($data['name'])) { $fields[] = 'name = :name'; $params['name'] = $data['name']; }
if (isset($data['age']))  { $fields[] = 'age = :age'; $params['age'] = $data['age']; }
if (isset($data['dob']))  { $fields[] = 'dob = :dob'; $params['dob'] = $data['dob']; }
if (isset($data['contact'])) { $fields[] = 'contact = :contact'; $params['contact'] = $data['contact']; }

if (empty($fields)) {
    http_response_code(400);
    echo json_encode(['error' => 'No fields to update']);
    exit;
}

try {
    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // optional mongo audit
    if (isset($mongoDb)) {
        try {
            $mongoDb->audit_log->insertOne([
                'type' => 'profile_update',
                'user_id' => $user_id,
                'changes' => $data,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]);
        } catch (Exception $e) {}
    }

    echo json_encode(['success' => true]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
    exit;
}
