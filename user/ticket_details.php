<?php
// ============================================
// Ticket Details Page
// Electricity Complaint Management System
// ============================================

session_start();
include '../config/db.php';
/** @var mysqli $conn Database connection from config/db.php */

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$ticket_id = intval($_GET['id'] ?? 0);

// Get ticket details
$query = "SELECT t.*, u.name as assigned_agent_name 
         FROM tickets t 
         LEFT JOIN users u ON t.assigned_agent = u.id 
         WHERE t.id = " . $ticket_id . " AND t.user_id = " . $user_id;

$ticket = fetchOne($query, $conn);

if (!$ticket) {
    header('Location: my_tickets.php');
    exit();
}

// Get chat messages
$query = "SELECT * FROM chat_messages WHERE ticket_id = " . $ticket_id . " ORDER BY created_at ASC";
$messages = fetchAll($query, $conn);

// Handle new message
$message_error = '';
$message_success = '';

if (isset($_POST['add_message'])) {
    $message_text = sanitizeInput($_POST['message'] ?? '');
    
    if (empty($message_text)) {
        $message_error = "Message cannot be empty!";
    } else {
        $insert_query = "INSERT INTO chat_messages (ticket_id, user_id, sender, message) 
                        VALUES (" . $ticket_id . ", " . $user_id . ", 'customer', '" . mysqli_real_escape_string($conn, $message_text) . "')";
        
        if (executeQuery($insert_query, $conn)) {
            $message_success = "Message added successfully!";
            header("refresh:2;url=ticket_details.php?id=" . $ticket_id);
        } else {
            $message_error = "Failed to add message!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?php echo $ticket_id; ?> - Electricity Complaint System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/user-electricity.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
        }
        .sidebar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            padding: 12px 15px;
            color: #667eea;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 8px;
        }
        .sidebar a:hover {
            background: #f0f4ff;
        }
        .ticket-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .ticket-info {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-box {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        .info-box h6 {
            color: #999;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .info-box p {
            margin: 0;
            font-weight: 600;
            color: #333;
        }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-assigned {
            background: #cce5ff;
            color: #004085;
        }
        .status-in-progress {
            background: #d1ecf1;
            color: #0c5460;
        }
        .status-resolved {
            background: #d4edda;
            color: #155724;
        }
        .priority-high {
            color: #dc3545;
            font-weight: 700;
        }
        .priority-medium {
            color: #ffc107;
            font-weight: 700;
        }
        .priority-low {
            color: #28a745;
            font-weight: 700;
        }
        .priority-critical {
            color: #721c24;
            font-weight: 700;
        }
        .description-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .description-box h5 {
            color: #333;
            margin-bottom: 15px;
            font-weight: 700;
        }
        .description-box p {
            color: #666;
            line-height: 1.6;
            margin: 0;
        }
        .chat-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 500px;
            margin-bottom: 20px;
        }
        .chat-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            font-weight: 600;
            color: #333;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        .message {
            margin-bottom: 15px;
        }
        .message.user {
            text-align: right;
        }
        .message-content {
            display: inline-block;
            max-width: 70%;
            padding: 12px 15px;
            border-radius: 10px;
            word-wrap: break-word;
        }
        .message.user .message-content {
            background: #667eea;
            color: white;
            border-radius: 10px 0 10px 10px;
        }
        .message.agent .message-content {
            background: #e9ecef;
            color: #333;
            border-radius: 0 10px 10px 10px;
        }
        .message-sender {
            font-size: 12px;
            color: #999;
            margin-bottom: 5px;
        }
        .chat-input-area {
            padding: 20px;
            border-top: 1px solid #ddd;
        }
        .main-content {
            padding: 20px;
        }
        h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 30px;
        }
        .back-link {
            color: #667eea;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="gov-top-strip">
        <div class="container-fluid">
            <span>Government Electricity Consumer Service Portal</span>
            <span>Helpline: 1912 | Emergency Supply Support</span>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Consumer Grievance Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="complaint_management.php">Complaint Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid main-content">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="sidebar">
                    <h5 style="margin-bottom: 20px; color: #333;">Menu</h5>
                    <a href="dashboard.php">Home</a>
                    <a href="complaint_management.php">Complaint Management</a>
                    <a href="create_complaint.php">Register Complaint</a>
                    <a href="my_tickets.php" class="active">My Complaints</a>
                    <a href="chatbot.php">AI Chatbot</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <a href="my_tickets.php" class="back-link">← Back to Complaints</a>

                <h1>Ticket #<?php echo $ticket_id; ?> - Details</h1>

                <!-- Ticket Header -->
                <div class="ticket-header">
                    <h2><?php echo htmlspecialchars($ticket['title']); ?></h2>
                    
                    <div class="ticket-info" style="margin-top: 20px;">
                        <div class="info-box">
                            <h6>Status</h6>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $ticket['status'])); ?>">
                                <?php echo $ticket['status']; ?>
                            </span>
                        </div>
                        <div class="info-box">
                            <h6>Priority</h6>
                            <p class="priority-<?php echo strtolower($ticket['priority']); ?>">
                                <?php echo $ticket['priority']; ?>
                            </p>
                        </div>
                        <div class="info-box">
                            <h6>Category</h6>
                            <p><?php echo htmlspecialchars($ticket['category']); ?></p>
                        </div>
                        <div class="info-box">
                            <h6>Created</h6>
                            <p><?php echo date('d M Y, H:i', strtotime($ticket['created_at'])); ?></p>
                        </div>
                        <div class="info-box">
                            <h6>Last Updated</h6>
                            <p><?php echo date('d M Y, H:i', strtotime($ticket['updated_at'])); ?></p>
                        </div>
                        <div class="info-box">
                            <h6>Assigned Agent</h6>
                            <p><?php echo htmlspecialchars($ticket['assigned_agent_name'] ?? 'Not assigned'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="description-box">
                    <h5>Description</h5>
                    <p><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
                </div>

                <!-- AI Response -->
                <?php if (!empty($ticket['ai_response'])): ?>
                    <div class="description-box" style="background: #f0f4ff; border-left: 4px solid #667eea;">
                        <h5>AI Analysis</h5>
                        <p><?php echo nl2br(htmlspecialchars($ticket['ai_response'])); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Messages -->
                <h3 style="color: #333; margin-top: 30px; margin-bottom: 20px; font-weight: 700;">Conversation</h3>

                <div class="chat-container">
                    <div class="chat-header">
                        Messages
                    </div>
                    <div class="chat-messages" id="chatMessages">
                        <?php if (!empty($messages)): ?>
                            <?php foreach ($messages as $msg): ?>
                                <div class="message <?php echo ($msg['sender'] === 'customer') ? 'user' : 'agent'; ?>">
                                    <div class="message-sender">
                                        <?php echo htmlspecialchars($msg['sender']); ?> • <?php echo date('d M, H:i', strtotime($msg['created_at'])); ?>
                                    </div>
                                    <div class="message-content">
                                        <?php echo htmlspecialchars($msg['message']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 40px 20px; color: #999;">
                                No messages yet
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Add Message -->
                <?php if ($ticket['status'] !== 'Closed'): ?>
                    <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <?php if (!empty($message_error)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($message_error); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($message_success)): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($message_success); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="ticket_details.php?id=<?php echo $ticket_id; ?>">
                            <div style="display: flex; gap: 10px;">
                                <textarea 
                                    name="message" 
                                    placeholder="Add your message..."
                                    style="flex: 1; border: 1px solid #ddd; border-radius: 5px; padding: 12px; font-size: 14px; font-family: inherit; resize: vertical; min-height: 80px;"
                                    required
                                ></textarea>
                            </div>
                            <button 
                                type="submit" 
                                name="add_message" 
                                style="background: #667eea; color: white; border: none; border-radius: 5px; padding: 10px 20px; font-weight: 600; cursor: pointer; margin-top: 10px;"
                            >
                                Send Message
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center; color: #666;">
                        This ticket is closed and cannot be updated.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <a href="chatbot.php" class="floating-chatbot" aria-label="Open AI chatbot">
        <span>AI</span><span>Chatbot</span>
    </a>
    <script>
        // Auto-scroll to bottom
        document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
    </script>
</body>
</html>
