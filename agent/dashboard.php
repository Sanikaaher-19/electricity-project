<?php
// ============================================
// Agent Dashboard
// Electricity Complaint Management System
// ============================================

session_start();
include '../config/db.php';
/** @var mysqli $conn Database connection from config/db.php */

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SESSION['role'] !== 'agent') {
    header('Location: ../index.php');
    exit();
}

$agent_id = intval($_SESSION['user_id']);
$agent_name = $_SESSION['user_name'];
$agent_initials = strtoupper(substr($agent_name, 0, 1)) . (strpos($agent_name, ' ') !== false ? strtoupper(substr(strrchr($agent_name, ' '), 1, 1)) : '');

$agent_ticket_rows = fetchAll(
    "SELECT status, priority FROM tickets WHERE assigned_agent = " . $agent_id,
    $conn
);

$stats = [
    'total_assigned' => 0,
    'open_tickets' => 0,
    'in_progress' => 0,
    'resolved' => 0,
    'high_priority' => 0,
];

foreach ($agent_ticket_rows as $row) {
    $status = trim($row['status'] ?? '');
    $priority = trim($row['priority'] ?? '');
    $is_resolved = in_array($status, ['Resolved', 'Closed'], true);

    $stats['total_assigned']++;

    if (in_array($status, ['Pending', 'Assigned', 'On Hold'], true)) {
        $stats['open_tickets']++;
    }

    if ($status === 'In Progress') {
        $stats['in_progress']++;
    }

    if ($is_resolved) {
        $stats['resolved']++;
    }

    if (!$is_resolved && in_array($priority, ['High', 'Critical'], true)) {
        $stats['high_priority']++;
    }
}

$tickets = fetchAll("SELECT id, title, status, priority, category, created_at 
                     FROM tickets 
                     WHERE assigned_agent = " . $agent_id . " 
                     ORDER BY FIELD(priority, 'Critical', 'High', 'Medium', 'Low'), created_at DESC 
                     LIMIT 8", $conn);

$month_rows = fetchAll("SELECT DATE_FORMAT(created_at, '%b %y') AS month_label, COUNT(*) AS total
                        FROM tickets
                        WHERE assigned_agent = " . $agent_id . "
                          AND created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
                        GROUP BY YEAR(created_at), MONTH(created_at), month_label
                        ORDER BY YEAR(created_at), MONTH(created_at)", $conn);
$month_labels = [];
for ($i = 5; $i >= 0; $i--) {
    $month_labels[date('M y', strtotime("-" . $i . " months"))] = 0;
}
foreach ($month_rows as $row) {
    $month_labels[$row['month_label']] = intval($row['total']);
}

function agentPillClass($value) {
    if ($value === 'Critical' || $value === 'High') return 'danger';
    if ($value === 'Resolved' || $value === 'Closed') return 'success';
    if ($value === 'In Progress' || $value === 'Assigned' || $value === 'Low') return 'info';
    return 'warning';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard - PowerDesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/agent-powerdesk.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../Chart.js"></script>
</head>
<body class="powerdesk-admin powerdesk-agent">
    <div class="pd-shell">
        <aside class="pd-sidebar">
            <div class="pd-brand">
                <div class="pd-logo">PD</div>
                <div><strong>PowerDesk</strong><span>Agent Portal</span></div>
            </div>
            <div class="pd-profile">
                <div class="pd-avatar"><?php echo htmlspecialchars($agent_initials ?: 'AG'); ?></div>
                <div><strong><?php echo htmlspecialchars($agent_name); ?></strong><span>Field Resolution Officer</span></div>
            </div>
            <nav class="pd-nav">
                <a href="dashboard.php" class="active"><span class="pd-nav-icon">OV</span> Overview</a>
                <a href="assigned_tickets.php"><span class="pd-nav-icon">TK</span> Assigned Tickets</a>
                <a href="../logout.php"><span class="pd-nav-icon">LO</span> Logout</a>
            </nav>
        </aside>

        <main class="pd-main">
            <header class="pd-header">
                <div>
                    <h1>Agent Dashboard</h1>
                    <p>Government electricity ticket operations</p>
                </div>
            </header>

            <section class="pd-content">
                <div class="agent-work-banner">
                    <small>Consumer Service Desk</small>
                    <h2>Welcome back, <?php echo htmlspecialchars($agent_name); ?></h2>
                    <p>Review assigned complaints, prioritize urgent electricity issues, and update resolution progress.</p>
                </div>

                <div class="pd-stat-grid agent-stat-grid">
                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top"><span class="pd-stat-label">Assigned</span><span class="pd-stat-mark">TK</span></div>
                        <h3><?php echo intval($stats['total_assigned'] ?? 0); ?></h3>
                        <p>Your queue</p>
                    </div>
                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top"><span class="pd-stat-label">Open</span><span class="pd-stat-mark">OP</span></div>
                        <h3><?php echo intval($stats['open_tickets'] ?? 0); ?></h3>
                        <p>Needs action</p>
                    </div>
                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top"><span class="pd-stat-label">In Progress</span><span class="pd-stat-mark">IP</span></div>
                        <h3><?php echo intval($stats['in_progress'] ?? 0); ?></h3>
                        <p>Being resolved</p>
                    </div>
                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top"><span class="pd-stat-label">Resolved</span><span class="pd-stat-mark">RS</span></div>
                        <h3><?php echo intval($stats['resolved'] ?? 0); ?></h3>
                        <p>Completed</p>
                    </div>
                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top"><span class="pd-stat-label">High Priority</span><span class="pd-stat-mark danger">HP</span></div>
                        <h3><?php echo intval($stats['high_priority'] ?? 0); ?></h3>
                        <p>Urgent</p>
                    </div>
                </div>

                <div class="pd-chart-grid">
                    <div class="pd-card pd-panel">
                        <h3>Monthly Assigned Trend</h3>
                        <div class="pd-chart-wrap">
                            <canvas id="agentTrendChart"></canvas>
                        </div>
                    </div>

                    <div class="pd-card pd-table-card">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($tickets)): ?>
                                    <?php foreach ($tickets as $ticket): ?>
                                        <tr>
                                            <td>
                                                <strong>TKT-<?php echo intval($ticket['id']); ?></strong><br>
                                                <span style="color: #8292a8;"><?php echo htmlspecialchars(substr($ticket['title'], 0, 48)); ?></span>
                                            </td>
                                            <td><span class="pd-pill <?php echo agentPillClass($ticket['priority']); ?>"><?php echo htmlspecialchars($ticket['priority']); ?></span></td>
                                            <td><span class="pd-pill <?php echo agentPillClass($ticket['status']); ?>"><?php echo htmlspecialchars($ticket['status']); ?></span></td>
                                            <td><a class="pd-btn" href="assigned_tickets.php?id=<?php echo intval($ticket['id']); ?>">Open</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" style="text-align: center; color: #8292a8; padding: 32px;">No assigned tickets</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        new Chart(document.getElementById('agentTrendChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($month_labels)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($month_labels)); ?>,
                    backgroundColor: '#23b7d9',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#8292a8' } },
                    y: { beginAtZero: true, ticks: { precision: 0, color: '#8292a8' }, grid: { color: '#e5ebf2' } }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
