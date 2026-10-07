<?php
// ============================================
// DATABASE CONFIGURATION
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Change to your MySQL username
define('DB_PASS', '');            // Change to your MySQL password
define('DB_NAME', 'car_rental_db');

// Fine per day (configurable)
define('DEFAULT_FINE_PER_DAY', 15.00);

// App settings
define('APP_NAME', 'DriveEase');
define('APP_URL', 'http://localhost/car-rental');
define('SESSION_LIFETIME', 3600); // 1 hour

function getDBConnection() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
