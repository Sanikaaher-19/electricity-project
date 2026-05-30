<?php
// ============================================
// Agent - Status Update Endpoint
// Electricity Complaint Management System
// ============================================

session_start();
include '../config/db.php';
/** @var mysqli $conn Database connection from config/db.php */

// Only logged-in agents can update their own assigned tickets.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header('Location: ../login.php');
    exit();
}

$ticket_id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
$agent_id = intval($_SESSION['user_id']);
$status = sanitizeInput($_POST['status'] ?? '');
$allowed_statuses = ['Pending', 'Assigned', 'In Progress', 'On Hold', 'Resolved', 'Closed'];

if ($ticket_id > 0 && in_array($status, $allowed_statuses, true)) {
    $resolved_sql = ($status === 'Resolved' || $status === 'Closed') ? ", resolved_at = NOW()" : "";
    $query = "UPDATE tickets SET status = '" . mysqli_real_escape_string($conn, $status) . "'" . $resolved_sql
        . " WHERE id = " . $ticket_id . " AND assigned_agent = " . $agent_id;
    executeQuery($query, $conn);
}

header('Location: assigned_tickets.php?id=' . $ticket_id);
exit();
?>
