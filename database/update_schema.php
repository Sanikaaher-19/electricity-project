<?php
// ============================================
// Database Schema Repair Script
// Electricity Complaint Management System
// ============================================
//
// Run this from the project root when an existing database is missing
// newer tables:
// C:\xampp\php\php.exe database\update_schema.php

require __DIR__ . '/../config/db.php';
/** @var mysqli $conn Database connection from config/db.php */

function columnExists($conn, $table, $column) {
    $table = mysqli_real_escape_string($conn, $table);
    $column = mysqli_real_escape_string($conn, $column);
    $result = fetchOne("SHOW COLUMNS FROM `" . $table . "` LIKE '" . $column . "'", $conn);
    return $result !== null;
}

// Upgrade older local databases to the columns used by the current PHP code.
$column_updates = [
    ['users', 'phone', "ALTER TABLE users ADD phone VARCHAR(15) NULL AFTER password"],
    ['users', 'status', "ALTER TABLE users ADD status ENUM('active','inactive') DEFAULT 'active' AFTER role"],
    ['users', 'updated_at', "ALTER TABLE users ADD updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"],
    ['tickets', 'updated_at', "ALTER TABLE tickets ADD updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"],
    ['tickets', 'resolution_notes', "ALTER TABLE tickets ADD resolution_notes LONGTEXT NULL AFTER ai_response"],
    ['tickets', 'resolved_at', "ALTER TABLE tickets ADD resolved_at TIMESTAMP NULL AFTER updated_at"],
    ['chat_messages', 'user_id', "ALTER TABLE chat_messages ADD user_id INT NULL AFTER ticket_id"],
    ['agent_notes', 'is_private', "ALTER TABLE agent_notes ADD is_private BOOLEAN DEFAULT TRUE AFTER note"],
];

foreach ($column_updates as $update) {
    if (!columnExists($conn, $update[0], $update[1])) {
        executeQuery($update[2], $conn);
    }
}

$queries = [
    "CREATE TABLE IF NOT EXISTS ai_responses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_id INT NOT NULL,
        complaint_text LONGTEXT,
        ai_category VARCHAR(100),
        ai_priority VARCHAR(50),
        ai_suggested_response LONGTEXT,
        ai_model VARCHAR(50) DEFAULT 'groq',
        confidence_score FLOAT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
        INDEX idx_ticket_id (ticket_id)
    )",
    "INSERT IGNORE INTO users (name, email, password, phone, role)
        VALUES ('Admin User', 'admin@electricity.com', '202cb962ac59075b964b07152d234b70', '9876543210', 'admin')",
    "INSERT IGNORE INTO users (name, email, password, phone, role)
        VALUES ('Billing Agent', 'billing@electricity.com', '202cb962ac59075b964b07152d234b70', '9876543211', 'agent')",
    "INSERT IGNORE INTO users (name, email, password, phone, role)
        VALUES ('Power Failure Agent', 'power@electricity.com', '202cb962ac59075b964b07152d234b70', '9876543212', 'agent')",
    "INSERT IGNORE INTO users (name, email, password, phone, role)
        VALUES ('Emergency Agent', 'emergency@electricity.com', '202cb962ac59075b964b07152d234b70', '9876543213', 'agent')",
    "INSERT IGNORE INTO users (name, email, password, phone, role)
        VALUES ('Test Customer', 'customer@electricity.com', '202cb962ac59075b964b07152d234b70', '9876543214', 'customer')",
    "UPDATE users SET password = '202cb962ac59075b964b07152d234b70', role = 'admin', status = 'active'
        WHERE email = 'admin@electricity.com'",
    "UPDATE users SET password = '202cb962ac59075b964b07152d234b70', role = 'agent', status = 'active'
        WHERE email IN ('billing@electricity.com', 'power@electricity.com', 'emergency@electricity.com')",
    "UPDATE users SET password = '202cb962ac59075b964b07152d234b70', role = 'customer', status = 'active'
        WHERE email = 'customer@electricity.com'",
];

foreach ($queries as $query) {
    executeQuery($query, $conn);
}

echo "Database schema is up to date." . PHP_EOL;
?>
