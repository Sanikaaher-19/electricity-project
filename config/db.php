<?php
// ============================================
// Database Configuration
// Electricity Complaint Management System
// ============================================

require_once __DIR__ . '/env.php';

// Keep real credentials in .env. Do not commit .env to GitHub.
define('DB_HOST', envValue('DB_HOST', '127.0.0.1'));
define('DB_USER', envValue('DB_USER', 'root'));
define('DB_PASS', envValue('DB_PASS', ''));
define('DB_NAME', envValue('DB_NAME', 'electricity_complaint_system'));

// Create one shared database connection for every page that includes this file.
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    error_log("Database Connection Failed: " . mysqli_connect_error());
    die("Database connection failed. Please start MySQL in XAMPP and import database/complaint_system.sql.");
}

mysqli_set_charset($conn, "utf8mb4");

// Convert mysqli errors into exceptions so failures are visible during development.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Run a SQL query and return the mysqli result object.
function executeQuery($query, $conn) {
    try {
        return mysqli_query($conn, $query);
    } catch (mysqli_sql_exception $e) {
        error_log("Query Error: " . $e->getMessage() . " | SQL: " . $query);
        return false;
    }
}

// Fetch all rows for SELECT queries.
function fetchAll($query, $conn) {
    $result = executeQuery($query, $conn);
    if (!$result) return [];
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Fetch one row for SELECT queries.
function fetchOne($query, $conn) {
    $result = executeQuery($query, $conn);
    if (!$result) return null;
    return mysqli_fetch_assoc($result);
}

// Return the auto-increment id from the most recent INSERT.
function getLastId($conn) {
    return mysqli_insert_id($conn);
}

// Clean text before displaying or storing simple form values.
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

?>
