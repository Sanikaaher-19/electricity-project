<?php
// ============================================
// Create Ticket API Endpoint
// Electricity Complaint Management System
// ============================================

header('Content-Type: application/json');
session_start();
include '../config/db.php';
/** @var mysqli $conn Database connection from config/db.php */
include __DIR__ . '/ai_service.php';

// Response array
$response = [
    'success' => false,
    'message' => '',
    'ticket_id' => null,
    'error' => null
];

try {
    // Check user authentication
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
    } else {
        $title = sanitizeInput($input['title'] ?? '');
        $description = sanitizeInput($input['description'] ?? '');
    }

    // Validation
    if (empty($title)) {
        throw new Exception('Title is required');
    }
    if (empty($description)) {
        throw new Exception('Description is required');
    }
    if (strlen($title) < 5) {
        throw new Exception('Title must be at least 5 characters');
    }
    if (strlen($description) < 10) {
        throw new Exception('Description must be at least 10 characters');
    }

    $user_id = intval($_SESSION['user_id']);

    // ============================================
    // Create Ticket with Default Values
    // ============================================

    $insert_query = "INSERT INTO tickets 
                    (user_id, title, description, category, priority, status, assigned_agent)
                    VALUES (
                        " . $user_id . ",
                        '" . mysqli_real_escape_string($conn, $title) . "',
                        '" . mysqli_real_escape_string($conn, $description) . "',
                        'General',
                        'Medium',
                        'Pending',
                        2
                    )";

    if (!executeQuery($insert_query, $conn)) {
        throw new Exception('Failed to create ticket');
    }

    $ticket_id = getLastId($conn);

    // Analyze and assign the ticket in the same logged-in request.
    $analysis = applyAiAnalysisToTicket($conn, $ticket_id, $description);

    // ============================================
    // Return Success Response
    // ============================================

    $response['success'] = true;
    $response['message'] = 'Ticket created successfully';
    $response['ticket_id'] = $ticket_id;
    $response['analysis'] = $analysis;

} catch (Exception $e) {
    // Log error
    error_log('Create Ticket Error: ' . $e->getMessage());
    
    $response['success'] = false;
    $response['error'] = $e->getMessage();
    http_response_code(400);
}

// Return JSON response
echo json_encode($response);
?>
