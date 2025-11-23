<?php
// backend/db/redis.php
$config = require __DIR__ . '/config.php';
$rc = $config['redis'];

$redis = null;
try {
    if (class_exists('Redis')) {
        $redis = new Redis();
        $redis->connect($rc['host'], $rc['port'], $rc['timeout']);
    }
} catch (Exception $e) {
    // If Redis not available, we still continue but session features will fallback.
    $redis = null;
}
// backend/db/redis.php

// Ensure config is loaded (though change_password.php does this implicitly)
// $config is typically loaded once, but let's make sure it's available.
if (!isset($config)) {
    $config = require __DIR__ . '/config.php';
}
$redisConfig = $config['redis'];

$redis = null; // Initialize Redis connection object

try {
    // Check if the Redis extension is loaded
    if (class_exists('Redis')) {
        $redis = new Redis();
        // The correct port (6379) is now in config.php
        $redis->connect($redisConfig['host'], $redisConfig['port'], $redisConfig['timeout']);
        // Optional: Check if the connection is successful (e.g., ping)
        // $redis->ping(); 
    } else {
        // Log or handle the case where the Redis extension is missing
        error_log("Redis extension not found. Session management will fail.");
    }
} catch (Exception $e) {
    // Handle connection error (e.g., Redis server not running)
    error_log("Redis connection failed: " . $e->getMessage());
    $redis = null; // Ensure $redis is null on failure
}
// $redis will be null if connection failed or extension is missing.
// change_password.php handles $redis being null gracefully: 
// $sessionData = $redis ? $redis->get($sessionKey) : null;
