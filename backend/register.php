<?php
// backend/register.php
header('Content-Type: application/json');
require __DIR__ . '/db/mysql.php';
require __DIR__ . '/db/redis.php';
// optional mongo; include only if extension present
if (file_exists(__DIR__ . '/db/mongo.php')) {
    require __DIR__ . '/db/mongo.php';
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || !isset($data['name'], $data['email'], $data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$name = trim($data['name']);
$email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
$password = $data['password'];

if (!$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 6 characters']);
    exit;
}

try {
    // check existing
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Email already registered']);
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $insert = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)');
    $insert->execute([
        'name' => $name,
        'email' => $email,
        'password_hash' => $password_hash
    ]);
    $userId = $pdo->lastInsertId();

    // optional: log to MongoDB
    if (isset($mongoDb)) {
        try {
            $mongoDb->audit_log->insertOne([
                'type' => 'register',
                'user_id' => (int)$userId,
                'email' => $email,
                'name' => $name,
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ]);
        } catch (Exception $e) {
            // ignore mongo errors
        }
    }

    echo json_encode(['success' => true, 'user_id' => (int)$userId]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
    exit;
}
