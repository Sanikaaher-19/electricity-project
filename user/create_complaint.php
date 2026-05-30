<?php
// ============================================
// Create Complaint Page
// Electricity Complaint Management System
// ============================================

session_start();
include '../config/db.php';
/** @var mysqli $conn Database connection from config/db.php */
include __DIR__ . '/../api/ai_service.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if user is a customer
if ($_SESSION['role'] !== 'customer') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle complaint submission
if (isset($_POST['submit_complaint'])) {
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');

    // Validation
    if (empty($title)) {
        $error = "Title is required!";
    } elseif (empty($description)) {
        $error = "Description is required!";
    } elseif (strlen($title) < 5) {
        $error = "Title must be at least 5 characters!";
    } elseif (strlen($description) < 20) {
        $error = "Description must be at least 20 characters!";
    } else {
        // Insert complaint with default status
        $insert_query = "INSERT INTO tickets 
                        (user_id, title, description, category, priority, status, assigned_agent)
                        VALUES (
                            " . intval($user_id) . ",
                            '" . mysqli_real_escape_string($conn, $title) . "',
                            '" . mysqli_real_escape_string($conn, $description) . "',
                            'General',
                            'Medium',
                            'Pending',
                            2
                        )";

        if (executeQuery($insert_query, $conn)) {
            $ticket_id = getLastId($conn);
            
            // Analyze the complaint and assign it to the best available agent.
            applyAiAnalysisToTicket($conn, $ticket_id, $description);

            $success = "Complaint submitted successfully! Ticket #" . $ticket_id . " created.";
            
            // Add chat message for ticket creation
            $chat_query = "INSERT INTO chat_messages 
                          (ticket_id, user_id, sender, message)
                          VALUES (
                              " . $ticket_id . ",
                              " . $user_id . ",
                              'customer',
                              'Complaint submitted: " . mysqli_real_escape_string($conn, $title) . "'
                          )";
            executeQuery($chat_query, $conn);

            header("refresh:2;url=ticket_details.php?id=" . $ticket_id);
        } else {
            $error = "Failed to submit complaint. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Complaint - Electricity Complaint System</title>
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
            transition: all 0.3s;
        }
        .sidebar a:hover {
            background: #f0f4ff;
            padding-left: 20px;
        }
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-card h2 {
            color: #667eea;
            margin-bottom: 25px;
            font-weight: 700;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-group input,
        .form-group textarea {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            width: 100%;
            font-size: 14px;
            font-family: inherit;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        textarea {
            resize: vertical;
            min-height: 150px;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .alert {
            margin-bottom: 20px;
        }
        .main-content {
            padding: 20px;
        }
        h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 30px;
        }
        .char-count {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
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
                    <a href="create_complaint.php" class="active">Register Complaint</a>
                    <a href="my_tickets.php">My Complaints</a>
                    <a href="chatbot.php">AI Chatbot</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <h1>Create New Complaint</h1>

                <!-- Error Message -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Success Message -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Complaint Form -->
                <div class="form-card">
                    <form method="POST" action="create_complaint.php">
                        <div class="form-group">
                            <label for="title">Complaint Title *</label>
                            <input type="text" id="title" name="title" placeholder="Brief title of your complaint" required maxlength="255">
                            <div class="char-count"><span id="title-count">0</span>/255</div>
                        </div>

                        <div class="form-group">
                            <label for="description">Detailed Description *</label>
                            <textarea id="description" name="description" placeholder="Please provide detailed information about your complaint. Include what happened, when it happened, and how it's affecting you." required></textarea>
                            <div class="char-count">Minimum 20 characters required</div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 25px;">
                            <a href="dashboard.php" class="btn" style="background: #ddd; color: #333; text-decoration: none; padding: 12px; border-radius: 5px; font-weight: 600; text-align: center;">Cancel</a>
                            <button type="submit" name="submit_complaint" class="btn-submit">Submit Complaint</button>
                        </div>
                    </form>
                </div>

                <!-- Tips Section -->
                <div style="background: #f0f4ff; padding: 20px; border-radius: 10px; margin-top: 30px;">
                    <h5 style="color: #667eea; margin-bottom: 15px;">Tips for Better Resolution</h5>
                    <ul style="margin: 0; padding-left: 20px;">
                        <li>Be as specific as possible about the issue</li>
                        <li>Mention the date and time of the problem</li>
                        <li>Include any error messages or reference numbers</li>
                        <li>Describe the impact on your service</li>
                        <li>Attach photos if available (feature coming soon)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <a href="chatbot.php" class="floating-chatbot" aria-label="Open AI chatbot">
        <span>AI</span><span>Chatbot</span>
    </a>
    <script>
        // Character counter for title
        document.getElementById('title').addEventListener('input', function() {
            document.getElementById('title-count').textContent = this.value.length;
        });
    </script>
</body>
</html>
