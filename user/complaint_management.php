<?php
// ============================================
// User Dashboard
// Electricity Complaint Management System
// ============================================

session_start();
include '../config/db.php';
/** @var mysqli $conn Database connection from config/db.php */

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
$user_name = $_SESSION['user_name'];

// Get user statistics
$query = "SELECT 
            COUNT(*) as total_tickets,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_tickets,
            SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved_tickets,
            SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_tickets
         FROM tickets WHERE user_id = " . $user_id;

$stats = fetchOne($query, $conn);

// Get recent tickets
$query = "SELECT id, title, status, priority, created_at FROM tickets 
         WHERE user_id = " . $user_id . " 
         ORDER BY created_at DESC LIMIT 5";

$recent_tickets = fetchAll($query, $conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Electricity Complaint System</title>
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
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: center;
            border-left: 5px solid #667eea;
        }
        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        .stat-card p {
            color: #666;
            margin: 0;
        }
        .ticket-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .badge-pending {
            background: #ffc107;
            color: black;
        }
        .badge-in-progress {
            background: #17a2b8;
        }
        .badge-resolved {
            background: #28a745;
        }
        .btn-action {
            padding: 8px 15px;
            font-size: 0.9rem;
            border-radius: 5px;
        }
        .main-content {
            padding: 20px;
        }
        h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 30px;
        }
        .content-header {
            margin-bottom: 30px;
        }
        .btn-new-complaint {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-new-complaint:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
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
                        <a class="nav-link active" href="complaint_management.php">Complaint Management</a>
                    </li>
                    <li class="nav-item">
                        <span class="navbar-text text-white me-3">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
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
                    <a href="complaint_management.php" class="active">Complaint Management</a>
                    <a href="create_complaint.php">Register Complaint</a>
                    <a href="my_tickets.php">My Complaints</a>
                    <a href="chatbot.php">AI Chatbot</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="content-header">
                    <div class="user-service-banner">
                        <small>Official Complaint Management</small>
                        <strong>Electricity Consumer Grievance Dashboard</strong><br>
                        Submit electricity complaints, track ticket status, and communicate with support staff.
                    </div>
                    <h1>Complaint Management</h1>
                    <a href="create_complaint.php" class="btn-new-complaint">+ Create New Complaint</a>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h3><?php echo $stats['total_tickets'] ?? 0; ?></h3>
                            <p>Total Complaints</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="border-left-color: #ffc107;">
                            <h3><?php echo $stats['pending_tickets'] ?? 0; ?></h3>
                            <p>Pending</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="border-left-color: #17a2b8;">
                            <h3><?php echo $stats['in_progress_tickets'] ?? 0; ?></h3>
                            <p>In Progress</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="border-left-color: #28a745;">
                            <h3><?php echo $stats['resolved_tickets'] ?? 0; ?></h3>
                            <p>Resolved</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Tickets Table -->
                <div style="margin-top: 30px;">
                    <h3 style="color: #333; margin-bottom: 20px;">Recent Complaints</h3>
                    <div class="ticket-table">
                        <table class="table table-hover" style="margin-bottom: 0;">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_tickets)): ?>
                                    <?php foreach ($recent_tickets as $ticket): ?>
                                        <tr>
                                            <td>#<?php echo $ticket['id']; ?></td>
                                            <td><?php echo htmlspecialchars(substr($ticket['title'], 0, 30)); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $ticket['status'])); ?>">
                                                    <?php echo $ticket['status']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $ticket['priority']; ?></td>
                                            <td><?php echo date('d M Y', strtotime($ticket['created_at'])); ?></td>
                                            <td>
                                                <a href="ticket_details.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-action" style="background: #667eea; color: white; text-decoration: none;">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 40px;">
                                            <p style="color: #999; margin: 0;">No complaints yet. <a href="create_complaint.php">Create one now</a></p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="text-align: right; margin-top: 15px;">
                        <a href="my_tickets.php" style="color: #667eea; text-decoration: none;">View all complaints →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <a href="chatbot.php" class="floating-chatbot" aria-label="Open AI chatbot">
        <span>AI</span><span>Chatbot</span>
    </a>
</body>
</html>
