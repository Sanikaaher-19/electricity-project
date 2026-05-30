<?php
// ============================================
// Admin - Manage Agents
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
$success_message = '';
$error_message = '';

// Add a new agent account.
if (isset($_POST['add_agent'])) {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = strtolower(sanitizeInput($_POST['email'] ?? ''));
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error_message = 'Name, email, and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (strlen($password) < 3) {
        $error_message = 'Password must be at least 3 characters.';
    } else {
        $existing = fetchOne("SELECT id FROM users WHERE email = '" . mysqli_real_escape_string($conn, $email) . "' LIMIT 1", $conn);

        if ($existing) {
            $error_message = 'An account already exists with this email.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (name, email, password, phone, role, status)
                      VALUES (
                        '" . mysqli_real_escape_string($conn, $name) . "',
                        '" . mysqli_real_escape_string($conn, $email) . "',
                        '" . mysqli_real_escape_string($conn, $hashed_password) . "',
                        '" . mysqli_real_escape_string($conn, $phone) . "',
                        'agent',
                        'active'
                      )";

            if (executeQuery($query, $conn)) {
                $success_message = 'Agent account created successfully.';
            } else {
                $error_message = 'Unable to create agent account.';
            }
        }
    }
}

// Remove means deactivate access while preserving ticket history.
if (isset($_POST['remove_agent'])) {
    $agent_id = intval($_POST['agent_id'] ?? 0);
    if ($agent_id > 0) {
        executeQuery("UPDATE users SET status = 'inactive' WHERE id = " . $agent_id . " AND role = 'agent'", $conn);
        executeQuery("UPDATE tickets SET assigned_agent = NULL, status = 'Pending' WHERE assigned_agent = " . $agent_id . " AND status NOT IN ('Resolved','Closed')", $conn);
        $success_message = 'Agent access removed. Open tickets were returned to pending queue.';
    }
}

if (isset($_POST['restore_agent'])) {
    $agent_id = intval($_POST['agent_id'] ?? 0);
    if ($agent_id > 0) {
        executeQuery("UPDATE users SET status = 'active' WHERE id = " . $agent_id . " AND role = 'agent'", $conn);
        $success_message = 'Agent access restored.';
    }
}

$agents = fetchAll("SELECT id, name, email, phone, status, created_at FROM users WHERE role = 'agent' ORDER BY status ASC, created_at DESC", $conn);
$active_agents = intval(fetchOne("SELECT COUNT(*) AS count FROM users WHERE role = 'agent' AND status = 'active'", $conn)['count'] ?? 0);
$inactive_agents = intval(fetchOne("SELECT COUNT(*) AS count FROM users WHERE role = 'agent' AND status = 'inactive'", $conn)['count'] ?? 0);

$agent_stats = fetchAll("SELECT u.id, u.name, u.email, u.status,
                        COUNT(t.id) AS total_assigned,
                        SUM(CASE WHEN t.status = 'Resolved' THEN 1 ELSE 0 END) AS resolved,
                        SUM(CASE WHEN t.status IN ('Pending','Assigned','In Progress','On Hold') THEN 1 ELSE 0 END) AS open_tickets
                        FROM users u
                        LEFT JOIN tickets t ON u.id = t.assigned_agent
                        WHERE u.role = 'agent'
                        GROUP BY u.id, u.name, u.email, u.status
                        ORDER BY u.status ASC, u.name ASC", $conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Agents - PowerDesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/admin-powerdesk.css" rel="stylesheet">
</head>
<body class="powerdesk-admin">
    <div class="pd-shell">
        <aside class="pd-sidebar">
            <div class="pd-brand">
                <div class="pd-logo">PD</div>
                <div>
                    <strong>PowerDesk</strong>
                    <span>Admin Portal</span>
                </div>
            </div>

            <div class="pd-profile">
                <div class="pd-avatar"><?php echo htmlspecialchars($admin_initials ?: 'AD'); ?></div>
                <div>
                    <strong><?php echo htmlspecialchars($admin_name); ?></strong>
                    <span>Technical Specialist</span>
                </div>
            </div>

            <nav class="pd-nav">
                <a href="dashboard.php"><span class="pd-nav-icon">OV</span> Overview</a>
                <a href="all_tickets.php"><span class="pd-nav-icon">TK</span> All Tickets</a>
                <a href="analytics.php"><span class="pd-nav-icon">AN</span> Analytics</a>
                <a href="manage_agents.php" class="active"><span class="pd-nav-icon">AG</span> Manage Agents</a>
                <a href="../logout.php"><span class="pd-nav-icon">LO</span> Logout</a>
            </nav>
        </aside>

        <main class="pd-main">
            <header class="pd-header">
                <div>
                    <h1>Agent Management</h1>
                    <p>Create, monitor, remove, and restore agent access</p>
                </div>
            </header>

            <section class="pd-content">
                <?php if ($success_message): ?>
                    <div class="pd-alert success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="pd-alert danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <div class="pd-stat-grid" style="grid-template-columns: repeat(3, minmax(180px, 1fr));">
                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top"><span class="pd-stat-label">Total Agents</span><span class="pd-stat-mark">AG</span></div>
                        <h3><?php echo count($agents); ?></h3>
                        <p>All agent accounts</p>
                    </div>
                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top"><span class="pd-stat-label">Active</span><span class="pd-stat-mark">ON</span></div>
                        <h3><?php echo $active_agents; ?></h3>
                        <p>Can login</p>
                    </div>
                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top"><span class="pd-stat-label">Removed</span><span class="pd-stat-mark danger">OFF</span></div>
                        <h3><?php echo $inactive_agents; ?></h3>
                        <p>Access disabled</p>
                    </div>
                </div>

                <div class="pd-card pd-panel" style="margin-bottom: 28px;">
                    <h3>Add New Agent</h3>
                    <form method="POST" class="pd-form-grid">
                        <div>
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Agent name" required>
                        </div>
                        <div>
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="agent@example.com" required>
                        </div>
                        <div>
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="10-15 digits">
                        </div>
                        <div>
                            <label class="form-label">Password</label>
                            <input type="text" name="password" class="form-control" placeholder="Temporary password" required>
                        </div>
                        <div class="pd-form-actions">
                            <button type="submit" name="add_agent" class="pd-btn" style="border: 0;">Create Agent</button>
                        </div>
                    </form>
                </div>

                <div class="pd-card pd-table-card" style="margin-bottom: 28px;">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Agent</th>
                                <th>Status</th>
                                <th>Total Assigned</th>
                                <th>Open</th>
                                <th>Resolved</th>
                                <th>Resolution Rate</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($agent_stats as $stat): ?>
                                <?php
                                    $total_assigned = intval($stat['total_assigned'] ?? 0);
                                    $resolved = intval($stat['resolved'] ?? 0);
                                    $rate = $total_assigned > 0 ? round(($resolved / $total_assigned) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($stat['name']); ?></strong><br>
                                        <span style="color: #8292a8;"><?php echo htmlspecialchars($stat['email']); ?></span>
                                    </td>
                                    <td>
                                        <span class="pd-pill <?php echo $stat['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($stat['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $total_assigned; ?></td>
                                    <td><?php echo intval($stat['open_tickets'] ?? 0); ?></td>
                                    <td><?php echo $resolved; ?></td>
                                    <td><strong><?php echo $rate; ?>%</strong></td>
                                    <td>
                                        <div class="pd-action-row">
                                            <?php if ($stat['status'] === 'active'): ?>
                                                <form method="POST" onsubmit="return confirm('Remove this agent access? Open tickets will return to pending queue.');">
                                                    <input type="hidden" name="agent_id" value="<?php echo intval($stat['id']); ?>">
                                                    <button type="submit" name="remove_agent" class="pd-btn danger" style="border: 0;">Remove</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST">
                                                    <input type="hidden" name="agent_id" value="<?php echo intval($stat['id']); ?>">
                                                    <button type="submit" name="restore_agent" class="pd-btn secondary" style="border: 0;">Restore</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pd-chart-grid">
                    <?php foreach ($agents as $agent): ?>
                        <div class="pd-card pd-panel">
                            <h3><?php echo htmlspecialchars($agent['name']); ?></h3>
                            <p style="color: #63758b; margin-bottom: 10px;">
                                Email: <?php echo htmlspecialchars($agent['email']); ?><br>
                                Phone: <?php echo htmlspecialchars($agent['phone'] ?: 'Not added'); ?><br>
                                Joined: <?php echo date('d M Y', strtotime($agent['created_at'])); ?>
                            </p>
                            <span class="pd-pill <?php echo $agent['status'] === 'active' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($agent['status']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
