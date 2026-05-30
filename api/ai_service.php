<?php
// ============================================
// Shared AI Complaint Analysis Service
// Electricity Complaint Management System
// ============================================

require_once __DIR__ . '/../config/env.php';

// Set GROQ_API_KEY in .env. Leave it blank to use the offline fallback analyzer.
if (!defined('GROQ_API_KEY')) {
    define('GROQ_API_KEY', envValue('GROQ_API_KEY', ''));
}

if (!defined('GROQ_API_URL')) {
    define('GROQ_API_URL', envValue('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions'));
}

if (!defined('GROQ_MODEL')) {
    define('GROQ_MODEL', envValue('GROQ_MODEL', 'llama-3.1-8b-instant'));
}

/**
 * Analyze a complaint using Groq when configured; otherwise use local rules.
 */
function analyzeComplaint($complaint_text) {
    $complaint_text = trim($complaint_text);

    if ($complaint_text === '') {
        throw new Exception('Complaint text is required');
    }

    if (GROQ_API_KEY !== '' && function_exists('curl_init')) {
        try {
            return analyzeComplaintWithGroq($complaint_text);
        } catch (Exception $e) {
            error_log('Groq analysis failed, using fallback: ' . $e->getMessage());
        }
    }

    return analyzeComplaintLocally($complaint_text);
}

/**
 * Call Groq's OpenAI-compatible chat completion API.
 */
function analyzeComplaintWithGroq($complaint_text) {
    $system_prompt = "You are an AI assistant for an electricity complaint management system. Analyze the customer's complaint and provide:\n"
        . "CATEGORY: Choose from Billing, Power Failure, Electrical Hazard, Meter Issue, Connection Issue, General\n"
        . "PRIORITY: Choose from Low, Medium, High, Critical\n"
        . "INITIAL_RESPONSE: A brief professional acknowledgement and next step.\n\n"
        . "Return only these three labels.";

    $request_body = [
        'model' => GROQ_MODEL,
        'messages' => [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $complaint_text],
        ],
        'max_tokens' => 500,
        'temperature' => 0.4,
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => GROQ_API_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($request_body),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GROQ_API_KEY,
        ],
    ]);

    $api_response = curl_exec($curl);
    $curl_error = curl_error($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($curl_error) {
        throw new Exception('API connection error: ' . $curl_error);
    }

    $api_data = json_decode($api_response, true);
    if ($http_code < 200 || $http_code >= 300) {
        $message = $api_data['error']['message'] ?? 'Groq API returned HTTP ' . $http_code;
        throw new Exception($message);
    }

    $ai_content = $api_data['choices'][0]['message']['content'] ?? '';
    if ($ai_content === '') {
        throw new Exception('Invalid API response format');
    }

    return parseAiAnalysis($ai_content);
}

/**
 * Local fallback keeps the app usable even when no API key or internet exists.
 */
function analyzeComplaintLocally($complaint_text) {
    $text = strtolower($complaint_text);
    $category = 'General';
    $priority = 'Medium';

    if (preg_match('/bill|payment|amount|charge|invoice|tariff/', $text)) {
        $category = 'Billing';
        $priority = 'Medium';
    } elseif (preg_match('/power cut|outage|no electricity|blackout|supply/', $text)) {
        $category = 'Power Failure';
        $priority = 'High';
    } elseif (preg_match('/spark|fire|shock|burn|wire|hazard|danger/', $text)) {
        $category = 'Electrical Hazard';
        $priority = 'Critical';
    } elseif (preg_match('/meter|reading|unit|faulty meter/', $text)) {
        $category = 'Meter Issue';
        $priority = 'Medium';
    } elseif (preg_match('/connection|new line|disconnect|reconnect/', $text)) {
        $category = 'Connection Issue';
        $priority = 'Medium';
    }

    if (preg_match('/urgent|emergency|fire|shock|sparking|danger/', $text)) {
        $priority = 'Critical';
    }

    return [
        'category' => $category,
        'priority' => $priority,
        'initial_response' => 'Thank you for reporting this issue. Your complaint has been categorized as ' . $category . ' with ' . $priority . ' priority, and our team will review it soon.',
    ];
}

/**
 * Convert labelled AI text into a predictable array for the rest of the app.
 */
function parseAiAnalysis($ai_content) {
    $category = 'General';
    $priority = 'Medium';
    $initial_response = trim($ai_content);

    if (preg_match('/CATEGORY:\s*(.+?)(?:\r?\n|$)/i', $ai_content, $matches)) {
        $category = trim($matches[1]);
    }

    if (preg_match('/PRIORITY:\s*(.+?)(?:\r?\n|$)/i', $ai_content, $matches)) {
        $priority = trim($matches[1]);
    }

    if (preg_match('/INITIAL_RESPONSE:\s*(.+?)$/is', $ai_content, $matches)) {
        $initial_response = trim($matches[1]);
    }

    $allowed_categories = ['Billing', 'Power Failure', 'Electrical Hazard', 'Meter Issue', 'Connection Issue', 'General'];
    $allowed_priorities = ['Low', 'Medium', 'High', 'Critical'];

    if (!in_array($category, $allowed_categories, true)) {
        $category = 'General';
    }

    if (!in_array($priority, $allowed_priorities, true)) {
        $priority = 'Medium';
    }

    return [
        'category' => $category,
        'priority' => $priority,
        'initial_response' => $initial_response,
    ];
}

/**
 * Choose an active agent based on the AI category.
 */
function findAgentForCategory($conn, $category) {
    $preferred_email = 'billing@electricity.com';

    if ($category === 'Power Failure') {
        $preferred_email = 'power@electricity.com';
    } elseif ($category === 'Electrical Hazard') {
        $preferred_email = 'emergency@electricity.com';
    }

    $email = mysqli_real_escape_string($conn, $preferred_email);
    $agent = fetchOne("SELECT id FROM users WHERE role = 'agent' AND status = 'active' AND email = '" . $email . "' LIMIT 1", $conn);

    if (!$agent) {
        $agent = fetchOne("SELECT id FROM users WHERE role = 'agent' AND status = 'active' ORDER BY id ASC LIMIT 1", $conn);
    }

    return $agent ? intval($agent['id']) : null;
}

/**
 * Save AI analysis and apply it to an existing ticket.
 */
function applyAiAnalysisToTicket($conn, $ticket_id, $complaint_text) {
    $analysis = analyzeComplaint($complaint_text);
    $agent_id = findAgentForCategory($conn, $analysis['category']);
    $status = $agent_id ? 'Assigned' : 'Pending';

    $insert_query = "INSERT INTO ai_responses "
        . "(ticket_id, complaint_text, ai_category, ai_priority, ai_suggested_response, ai_model, confidence_score) VALUES ("
        . intval($ticket_id) . ", "
        . "'" . mysqli_real_escape_string($conn, $complaint_text) . "', "
        . "'" . mysqli_real_escape_string($conn, $analysis['category']) . "', "
        . "'" . mysqli_real_escape_string($conn, $analysis['priority']) . "', "
        . "'" . mysqli_real_escape_string($conn, $analysis['initial_response']) . "', "
        . "'" . (GROQ_API_KEY !== '' ? 'groq' : 'local-fallback') . "', "
        . (GROQ_API_KEY !== '' ? '0.85' : '0.60')
        . ")";
    executeQuery($insert_query, $conn);

    $update_query = "UPDATE tickets SET "
        . "category = '" . mysqli_real_escape_string($conn, $analysis['category']) . "', "
        . "priority = '" . mysqli_real_escape_string($conn, $analysis['priority']) . "', "
        . "assigned_agent = " . ($agent_id ? intval($agent_id) : "NULL") . ", "
        . "ai_response = '" . mysqli_real_escape_string($conn, $analysis['initial_response']) . "', "
        . "status = '" . $status . "' "
        . "WHERE id = " . intval($ticket_id);
    executeQuery($update_query, $conn);

    $analysis['assigned_agent_id'] = $agent_id;
    return $analysis;
}
?>
