<?php
// ============================================
// Groq API Integration
// AI Response for Complaint Analysis
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
    'data' => null,
    'error' => null
];

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Check user authentication
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        // Try POST data
        $complaint_text = sanitizeInput($_POST['complaint'] ?? $_POST['message'] ?? '');
        $ticket_id = isset($_POST['ticket_id']) ? intval($_POST['ticket_id']) : null;
    } else {
        $complaint_text = sanitizeInput($input['complaint'] ?? $input['message'] ?? '');
        $ticket_id = isset($input['ticket_id']) ? intval($input['ticket_id']) : null;
    }

    if (empty($complaint_text)) {
        throw new Exception('Complaint text is required');
    }

    $analysis = analyzeComplaint($complaint_text);
    $agent_id = findAgentForCategory($conn, $analysis['category']);

    // ============================================
    // Store in Database (if ticket_id provided)
    // ============================================
    
    if ($ticket_id) {
        $analysis = applyAiAnalysisToTicket($conn, $ticket_id, $complaint_text);
        $agent_id = $analysis['assigned_agent_id'];
    }

    // ============================================
    // Return Success Response
    // ============================================
    
    $response['success'] = true;
    $response['message'] = 'AI analysis completed successfully';
    $response['data'] = [
        'category' => $analysis['category'],
        'priority' => $analysis['priority'],
        'initial_response' => $analysis['initial_response'],
        'assigned_agent_id' => $agent_id
    ];

} catch (Exception $e) {
    // Log error
    error_log('AI Response Error: ' . $e->getMessage());
    
    $response['success'] = false;
    $response['error'] = $e->getMessage();
    http_response_code(400);
}

// Return JSON response
echo json_encode($response);
?>
