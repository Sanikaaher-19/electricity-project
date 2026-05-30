<?php
// ============================================
// Admin - Update Ticket
// Electricity Complaint Management System
// ============================================

session_start();
include '../config/db.php';
/** @var mysqli $conn Database connection from config/db.php */

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$admin_name = $_SESSION['user_name'];
$admin_initials = strtoupper(substr($admin_name, 0, 1)) . (strpos($admin_name, ' ') !== false ? strtoupper(substr(strrchr($admin_name, ' '), 1, 1)) : '');
$ticket_id = intval($_GET['id'] ?? 0);

if ($ticket_id === 0) {
    header('Location: all_tickets.php');
    exit();
}

$status_message = '';
$error_message = '';
$allowed_statuses = ['Pending', 'Assigned', 'In Progress', 'On Hold', 'Resolved', 'Closed'];
$allowed_priorities = ['Low', 'Medium', 'High', 'Critical'];
$agents = fetchAll("SELECT id, name, email FROM users WHERE role = 'agent' AND status = 'active' ORDER BY name ASC", $conn);

if (isset($_POST['update_ticket'])) {
    $status = sanitizeInput($_POST['status'] ?? '');
    $priority = sanitizeInput($_POST['priority'] ?? '');
    $assigned_agent = intval($_POST['assigned_agent'] ?? 0);
    $resolution_notes = sanitizeInput($_POST['resolution_notes'] ?? '');

    if (!in_array($status, $allowed_statuses, true)) {
        $error_message = 'Invalid ticket status.';
    } elseif (!in_array($priority, $allowed_priorities, true)) {
        $error_message = 'Invalid ticket priority.';
    } else {
        $resolved_sql = ($status === 'Resolved' || $status === 'Closed') ? ", resolved_at = COALESCE(resolved_at, NOW())" : ", resolved_at = NULL";
        $agent_sql = $assigned_agent > 0 ? intval($assigned_agent) : "NULL";

        $query = "UPDATE tickets SET "
            . "status = '" . mysqli_real_escape_string($conn, $status) . "', "
            . "priority = '" . mysqli_real_escape_string($conn, $priority) . "', "
            . "assigned_agent = " . $agent_sql . ", "
            . "resolution_notes = '" . mysqli_real_escape_string($conn, $resolution_notes) . "'"
            . $resolved_sql
            . " WHERE id = " . $ticket_id;

        if (executeQuery($query, $conn)) {
            $status_message = 'Ticket updated successfully.';
        } else {
            $error_message = 'Unable to update ticket. Please try again.';
        }
    }
}

$ticket = fetchOne(
    "SELECT t.*, c.name AS customer_name, c.email AS customer_email, c.phone AS customer_phone, a.name AS agent_name "
    . "FROM tickets t "
    . "JOIN users c ON t.user_id = c.id "
    . "LEFT JOIN users a ON t.assigned_agent = a.id "
    . "WHERE t.id = " . $ticket_id,
    $conn
);

if (!$ticket) {
    header('Location: all_tickets.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Ticket #<?php echo $ticket_id; ?> - PowerDesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/admin-powerdesk.css" rel="stylesheet">
</head>
<body class="powerdesk-admin">
    <div class="pd-shell">
        <aside class="pd-sidebar">
            <div class="pd-brand">
                <div class="pd-logo">PD</div>
                <div><strong>PowerDesk</strong><span>Admin Portal</span></div>
            </div>
            <div class="pd-profile">
                <div class="pd-avatar"><?php echo htmlspecialchars($admin_initials ?: 'AD'); ?></div>
                <div><strong><?php echo htmlspecialchars($admin_name); ?></strong><span>Technical Specialist</span></div>
            </div>
            <nav class="pd-nav">
                <a href="dashboard.php"><span class="pd-nav-icon">OV</span> Overview</a>
                <a href="all_tickets.php" class="active"><span class="pd-nav-icon">TK</span> All Tickets</a>
                <a href="analytics.php"><span class="pd-nav-icon">AN</span> Analytics</a>
                <a href="manage_agents.php"><span class="pd-nav-icon">AG</span> Manage Agents</a>
                <a href="../logout.php"><span class="pd-nav-icon">LO</span> Logout</a>
            </nav>
        </aside>

        <main class="pd-main">
            <header class="pd-header">
                <div>
                    <h1>Update Ticket #<?php echo $ticket_id; ?></h1>
                    <p>Reassign, prioritize, and close complaint records</p>
                </div>
                <a href="all_tickets.php?ticket_id=<?php echo $ticket_id; ?>" class="pd-btn secondary">Back to Tickets</a>
            </header>

            <section class="pd-content">
                <?php if ($status_message): ?>
                    <div class="pd-alert success"><?php echo htmlspecialchars($status_message); ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="pd-alert danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <div class="pd-chart-grid">
                    <div class="pd-card pd-panel">
                        <h3><?php echo htmlspecialchars($ticket['title']); ?></h3>
                        <div class="pd-detail-grid" style="grid-template-columns: 1fr 1fr;">
                            <div class="pd-detail-box">
                                <span>Customer</span>
                                <strong><?php echo htmlspecialchars($ticket['customer_name']); ?></strong>
                                <p><?php echo htmlspecialchars($ticket['customer_email']); ?></p>
                                <p><?php echo htmlspecialchars($ticket['customer_phone'] ?: 'No phone'); ?></p>
                            </div>
                            <div class="pd-detail-box">
                                <span>Current Agent</span>
                                <strong><?php echo htmlspecialchars($ticket['agent_name'] ?: 'Unassigned'); ?></strong>
                                <p><?php echo htmlspecialchars($ticket['category'] ?: 'General'); ?></p>
                            </div>
                        </div>
                        <div class="pd-detail-section">
                            <h3>Description</h3>
                            <p><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
                        </div>
                        <?php if (!empty($ticket['ai_response'])): ?>
                            <div class="pd-detail-section info">
                                <h3>AI Analysis</h3>
                                <p><?php echo nl2br(htmlspecialchars($ticket['ai_response'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="pd-card pd-panel">
                        <h3>Admin Controls</h3>
                        <form method="POST" action="update_ticket.php?id=<?php echo $ticket_id; ?>">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <?php foreach ($allowed_statuses as $status): ?>
                                        <option value="<?php echo $status; ?>" <?php echo $ticket['status'] === $status ? 'selected' : ''; ?>><?php echo $status; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select" required>
                                    <?php foreach ($allowed_priorities as $priority): ?>
                                        <option value="<?php echo $priority; ?>" <?php echo $ticket['priority'] === $priority ? 'selected' : ''; ?>><?php echo $priority; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Assigned Agent</label>
                                <select name="assigned_agent" class="form-select">
                                    <option value="0">Unassigned</option>
                                    <?php foreach ($agents as $agent): ?>
                                        <option value="<?php echo $agent['id']; ?>" <?php echo intval($ticket['assigned_agent']) === intval($agent['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($agent['name'] . ' - ' . $agent['email']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Resolution Notes</label>
                                <textarea name="resolution_notes" class="form-control" rows="7" placeholder="Add resolution or admin notes..."><?php echo htmlspecialchars($ticket['resolution_notes'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" name="update_ticket" class="pd-btn" style="border: 0;">Save Changes</button>
                            <a href="all_tickets.php?ticket_id=<?php echo $ticket_id; ?>" class="pd-btn secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
