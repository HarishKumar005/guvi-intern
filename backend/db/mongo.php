<?php
// backend/db/mongo.php - optional; requires mongodb PHP extension and composer mongodb/mongodb
$mongoClient = null;
try {
    if (class_exists('\MongoDB\Client')) {
        $config = require __DIR__ . '/config.php';
        $mongoCfg = $config['mongo'];
        $mongoClient = new MongoDB\Client($mongoCfg['uri']);
        $mongoDb = $mongoClient->selectDatabase($mongoCfg['db']);
    }
} catch (Exception $e) {
    $mongoClient = null;
}
