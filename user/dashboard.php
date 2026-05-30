<?php
// ============================================
// Customer Website Home
// Electricity Complaint Management System
// ============================================

session_start();
include '../config/db.php';
/** @var mysqli $conn Database connection from config/db.php */

// Only logged-in customers can view this portal home.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SESSION['role'] !== 'customer') {
    header('Location: ../index.php');
    exit();
}

$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electricity Consumer Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/user-electricity.css" rel="stylesheet">
    <style>
        .portal-hero {
            background:
                linear-gradient(90deg, rgba(18, 49, 90, 0.96), rgba(15, 95, 159, 0.9)),
                url('../assets/images/electricity-office.jpg');
            color: #ffffff;
            padding: 54px 34px;
            border-radius: 6px;
            border-left: 6px solid var(--board-gold);
            box-shadow: 0 8px 24px rgba(18, 49, 90, 0.16);
            margin-bottom: 24px;
        }

        .portal-hero small {
            color: #f7df8a;
            display: block;
            font-weight: 800;
            letter-spacing: 0;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .portal-hero h1 {
            color: #ffffff !important;
            font-size: 34px;
            margin-bottom: 12px;
        }

        .portal-hero p {
            max-width: 760px;
            margin-bottom: 24px;
            font-size: 16px;
        }

        .service-tile {
            background: #ffffff;
            border: 1px solid var(--board-border);
            border-top: 4px solid var(--board-blue);
            border-radius: 6px;
            padding: 22px;
            height: 100%;
            box-shadow: 0 6px 18px rgba(18, 49, 90, 0.08);
        }

        .service-tile h5 {
            color: var(--board-navy);
            font-weight: 800;
            margin-bottom: 10px;
        }

        .notice-board {
            background: #fff9e6;
            border: 1px solid #efd27a;
            border-left: 5px solid var(--board-gold);
            border-radius: 6px;
            padding: 18px;
            margin-top: 24px;
        }
    </style>
</head>
<body>
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
                        <a class="nav-link active" href="dashboard.php">Home</a>
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
        <section class="portal-hero">
            <small>Official Electricity Department Portal</small>
            <h1>Reliable Power Service For Every Consumer</h1>
            <p>
                Access electricity complaint services, track submitted applications,
                and get assistance from the consumer support desk through one secure portal.
            </p>
            <a href="complaint_management.php" class="btn btn-primary">Open Complaint Management</a>
            <a href="create_complaint.php" class="btn btn-light ms-2">Register New Complaint</a>
        </section>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="service-tile">
                    <h5>Complaint Management</h5>
                    <p>View your dashboard, ticket counts, recent complaints, and current service status.</p>
                    <a href="complaint_management.php" class="btn btn-primary">Go to Dashboard</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-tile">
                    <h5>Register Complaint</h5>
                    <p>Submit issues related to power failure, billing, meter problems, connection, or hazards.</p>
                    <a href="create_complaint.php" class="btn btn-primary">Submit Complaint</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-tile">
                    <h5>AI Assistance</h5>
                    <p>Use the chatbot to describe your issue and receive category and priority guidance.</p>
                    <a href="chatbot.php" class="btn btn-primary">Open Chatbot</a>
                </div>
            </div>
        </div>

        <div class="notice-board">
            <strong>Consumer Notice:</strong>
            For electrical hazard, sparking, fire risk, or live wire complaints, register immediately and call the helpline 1912.
        </div>
    </div>

    <a href="chatbot.php" class="floating-chatbot" aria-label="Open AI chatbot">
        <span>AI</span><span>Chatbot</span>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
