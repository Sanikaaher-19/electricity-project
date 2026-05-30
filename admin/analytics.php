<?php
// ============================================
// Admin - Analytics Page
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

$stats = fetchOne("SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN priority = 'Critical' THEN 1 ELSE 0 END) as critical
                  FROM tickets", $conn);

$avg_resolution_time = fetchOne("SELECT ROUND(AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)), 1) as avg_hours
                                FROM tickets WHERE resolved_at IS NOT NULL", $conn);

$resolved_count = intval($stats['resolved'] ?? 0);
$pending_count = intval($stats['pending'] ?? 0);
$total_count = intval($stats['total'] ?? 0);
$other_count = max(0, $total_count - $resolved_count - $pending_count);
$critical_count = intval($stats['critical'] ?? 0);

$priority_rows = fetchAll("SELECT priority, COUNT(*) AS total FROM tickets GROUP BY priority ORDER BY total DESC", $conn);
$priority_labels = [];
$priority_values = [];
foreach ($priority_rows as $row) {
    $priority_labels[] = $row['priority'];
    $priority_values[] = intval($row['total']);
}
if (empty($priority_labels)) {
    $priority_labels = ['No Tickets'];
    $priority_values = [0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - PowerDesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/admin-powerdesk.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../Chart.js"></script>
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
                <a href="analytics.php" class="active"><span class="pd-nav-icon">AN</span> Analytics</a>
                <a href="manage_agents.php"><span class="pd-nav-icon">AG</span> Manage Agents</a>
                <a href="../logout.php"><span class="pd-nav-icon">LO</span> Logout</a>
            </nav>
        </aside>

        <main class="pd-main">
            <header class="pd-header">
                <div>
                    <h1>Analytics</h1>
                    <p>Ticket performance and service health</p>
                </div>
            </header>

            <section class="pd-content">
                <div class="pd-stat-grid" style="grid-template-columns: repeat(4, minmax(170px, 1fr));">
                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top"><span class="pd-stat-label">Total</span><span class="pd-stat-mark">TK</span></div>
                        <h3><?php echo $total_count; ?></h3>
                        <p>Tickets</p>
                    </div>
                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top"><span class="pd-stat-label">Resolved</span><span class="pd-stat-mark">RS</span></div>
                        <h3><?php echo $resolved_count; ?></h3>
                        <p>Closed</p>
                    </div>
                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top"><span class="pd-stat-label">Pending</span><span class="pd-stat-mark">PN</span></div>
                        <h3><?php echo $pending_count; ?></h3>
                        <p>Needs action</p>
                    </div>
                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top"><span class="pd-stat-label">Critical</span><span class="pd-stat-mark danger">CR</span></div>
                        <h3><?php echo $critical_count; ?></h3>
                        <p>Urgent</p>
                    </div>
                </div>

                <div class="pd-chart-grid">
                    <div class="pd-card pd-panel">
                        <h3>Resolution Status</h3>
                        <div class="pd-chart-wrap tall">
                            <canvas id="resolutionChart"></canvas>
                        </div>
                    </div>

                    <div class="pd-card pd-panel">
                        <h3>Priority Mix</h3>
                        <div class="pd-chart-wrap tall">
                            <canvas id="priorityChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="pd-card pd-panel" style="margin-top: 28px;">
                    <h3>Average Resolution Time</h3>
                    <p style="font-size: 42px; font-weight: 900; color: #2563eb; margin: 0;">
                        <?php echo htmlspecialchars($avg_resolution_time['avg_hours'] ?? 0); ?> hours
                    </p>
                    <p style="color: #8292a8; margin: 0;">Calculated from tickets with resolved date.</p>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        new Chart(document.getElementById('resolutionChart'), {
            type: 'doughnut',
            data: {
                labels: ['Resolved', 'Pending', 'Other'],
                datasets: [{
                    data: [<?php echo $resolved_count; ?>, <?php echo $pending_count; ?>, <?php echo $other_count; ?>],
                    backgroundColor: ['#22c55e', '#f97316', '#94a3b8'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: { legend: { position: 'bottom' } }
            }
        });

        new Chart(document.getElementById('priorityChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($priority_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($priority_values); ?>,
                    backgroundColor: ['#f43f5e', '#f97316', '#2563eb', '#22c55e', '#94a3b8'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
</body>
</html>
