<?php
// ============================================
// My Tickets Page
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

if ($_SESSION['role'] !== 'customer') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get filter parameter
$filter_status = sanitizeInput($_GET['status'] ?? 'all');

// Build query
$query = "SELECT id, title, category, priority, status, created_at, updated_at FROM tickets WHERE user_id = " . intval($user_id);

if ($filter_status !== 'all') {
    $query .= " AND status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
}

$query .= " ORDER BY created_at DESC";

$tickets = fetchAll($query, $conn);

// Get status counts
$status_counts = fetchOne(
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'Assigned' THEN 1 ELSE 0 END) as assigned,
        SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved,
        SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) as closed
     FROM tickets WHERE user_id = " . intval($user_id),
    $conn
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints - Electricity Complaint System</title>
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
        .sidebar a:hover, .sidebar a.active {
            background: #f0f4ff;
        }
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .filter-btn {
            padding: 8px 15px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
            font-weight: 600;
            color: #333;
        }
        .filter-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .tickets-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
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
        .status-closed {
            background: #e2e3e5;
            color: #383d41;
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
        .main-content {
            padding: 20px;
        }
        h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 30px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .empty-state h3 {
            color: #666;
            margin-bottom: 20px;
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
                    <a href="complaint_management.php">Complaint Management</a>
                    <a href="create_complaint.php">Register Complaint</a>
                    <a href="my_tickets.php" class="active">My Complaints</a>
                    <a href="chatbot.php">AI Chatbot</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <h1>My Complaints</h1>

                <!-- Filters -->
                <div class="filters">
                    <strong style="color: #333; display: block; margin-bottom: 15px;">Filter by Status:</strong>
                    <a href="my_tickets.php?status=all" class="filter-btn <?php echo $filter_status === 'all' ? 'active' : ''; ?>">
                        All (<?php echo $status_counts['total'] ?? 0; ?>)
                    </a>
                    <a href="my_tickets.php?status=Pending" class="filter-btn <?php echo $filter_status === 'Pending' ? 'active' : ''; ?>">
                        Pending (<?php echo $status_counts['pending'] ?? 0; ?>)
                    </a>
                    <a href="my_tickets.php?status=Assigned" class="filter-btn <?php echo $filter_status === 'Assigned' ? 'active' : ''; ?>">
                        Assigned (<?php echo $status_counts['assigned'] ?? 0; ?>)
                    </a>
                    <a href="my_tickets.php?status=In Progress" class="filter-btn <?php echo $filter_status === 'In Progress' ? 'active' : ''; ?>">
                        In Progress (<?php echo $status_counts['in_progress'] ?? 0; ?>)
                    </a>
                    <a href="my_tickets.php?status=Resolved" class="filter-btn <?php echo $filter_status === 'Resolved' ? 'active' : ''; ?>">
                        Resolved (<?php echo $status_counts['resolved'] ?? 0; ?>)
                    </a>
                </div>

                <!-- Tickets Table -->
                <div class="tickets-table">
                    <?php if (!empty($tickets)): ?>
                        <table class="table table-hover" style="margin-bottom: 0;">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr>
                                        <td><strong>#<?php echo $ticket['id']; ?></strong></td>
                                        <td><?php echo htmlspecialchars(substr($ticket['title'], 0, 35)); ?></td>
                                        <td><?php echo htmlspecialchars($ticket['category']); ?></td>
                                        <td>
                                            <span class="priority-<?php echo strtolower($ticket['priority']); ?>">
                                                <?php echo $ticket['priority']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $ticket['status'])); ?>">
                                                <?php echo $ticket['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($ticket['created_at'])); ?></td>
                                        <td>
                                            <a href="ticket_details.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm" style="background: #667eea; color: white; text-decoration: none; padding: 5px 12px; border-radius: 3px;">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <h3>No Complaints Found</h3>
                            <p>You don't have any complaints matching this filter.</p>
                            <a href="create_complaint.php" class="btn btn-primary" style="background: #667eea; color: white; padding: 10px 25px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 15px;">
                                Create New Complaint
                            </a>
                        </div>
                    <?php endif; ?>
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
