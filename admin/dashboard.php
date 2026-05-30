<?php
// ============================================
// Admin Dashboard
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

// Dashboard metrics
$total_tickets = intval(fetchOne("SELECT COUNT(*) as count FROM tickets", $conn)['count'] ?? 0);
$open_tickets = intval(fetchOne("SELECT COUNT(*) as count FROM tickets WHERE status IN ('Pending','Assigned','On Hold')", $conn)['count'] ?? 0);
$in_progress_tickets = intval(fetchOne("SELECT COUNT(*) as count FROM tickets WHERE status='In Progress'", $conn)['count'] ?? 0);
$resolved_tickets = intval(fetchOne("SELECT COUNT(*) as count FROM tickets WHERE status IN ('Resolved','Closed')", $conn)['count'] ?? 0);
$high_priority_tickets = intval(fetchOne("SELECT COUNT(*) as count FROM tickets WHERE priority IN ('High','Critical')", $conn)['count'] ?? 0);
$agent_count = intval(fetchOne("SELECT COUNT(*) as count FROM users WHERE role='agent' AND status='active'", $conn)['count'] ?? 0);

// Monthly trend for the last six months.
$month_rows = fetchAll("SELECT DATE_FORMAT(created_at, '%b %y') AS month_label, COUNT(*) AS total
                        FROM tickets
                        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
                        GROUP BY YEAR(created_at), MONTH(created_at), month_label
                        ORDER BY YEAR(created_at), MONTH(created_at)", $conn);

$month_labels = [];
$month_values = [];
for ($i = 5; $i >= 0; $i--) {
    $label = date('M y', strtotime("-" . $i . " months"));
    $month_labels[$label] = 0;
}
foreach ($month_rows as $row) {
    $month_labels[$row['month_label']] = intval($row['total']);
}
$month_values = array_values($month_labels);
$month_labels = array_keys($month_labels);

// Category distribution for doughnut chart.
$category_rows = fetchAll("SELECT COALESCE(NULLIF(category, ''), 'General') AS category_name, COUNT(*) AS total
                           FROM tickets
                           GROUP BY category_name
                           ORDER BY total DESC
                           LIMIT 6", $conn);
$category_labels = [];
$category_values = [];
foreach ($category_rows as $row) {
    $category_labels[] = $row['category_name'];
    $category_values[] = intval($row['total']);
}
if (empty($category_labels)) {
    $category_labels = ['No Tickets'];
    $category_values = [0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PowerDesk</title>
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
                <a href="dashboard.php" class="active"><span class="pd-nav-icon">OV</span> Overview</a>
                <a href="all_tickets.php"><span class="pd-nav-icon">TK</span> All Tickets</a>
                <a href="analytics.php"><span class="pd-nav-icon">AN</span> Analytics</a>
                <a href="manage_agents.php"><span class="pd-nav-icon">AG</span> Manage Agents</a>
                <a href="../logout.php"><span class="pd-nav-icon">LO</span> Logout</a>
            </nav>
        </aside>

        <main class="pd-main">
            <header class="pd-header">
                <div>
                    <h1>Admin Dashboard</h1>
                    <p>Real-time ticket operations</p>
                </div>
            </header>

            <section class="pd-content">
                <div class="pd-page-title">
                    <h2>Overview</h2>
                    <p>Welcome back, <?php echo htmlspecialchars($admin_name); ?></p>
                </div>

                <div class="pd-stat-grid">
                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top">
                            <span class="pd-stat-label">Total</span>
                            <span class="pd-stat-mark">TK</span>
                        </div>
                        <h3><?php echo $total_tickets; ?></h3>
                        <p>All tickets</p>
                    </div>

                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top">
                            <span class="pd-stat-label">Open</span>
                            <span class="pd-stat-mark">OP</span>
                        </div>
                        <h3><?php echo $open_tickets; ?></h3>
                        <p>Needs action</p>
                    </div>

                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top">
                            <span class="pd-stat-label">In Progress</span>
                            <span class="pd-stat-mark">IP</span>
                        </div>
                        <h3><?php echo $in_progress_tickets; ?></h3>
                        <p>Being resolved</p>
                    </div>

                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top">
                            <span class="pd-stat-label">Resolved</span>
                            <span class="pd-stat-mark">RS</span>
                        </div>
                        <h3><?php echo $resolved_tickets; ?></h3>
                        <p>Closed</p>
                    </div>

                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top">
                            <span class="pd-stat-label">High Priority</span>
                            <span class="pd-stat-mark danger">HP</span>
                        </div>
                        <h3><?php echo $high_priority_tickets; ?></h3>
                        <p>Urgent</p>
                    </div>

                    <div class="pd-card pd-stat">
                        <div class="pd-stat-top">
                            <span class="pd-stat-label">Agents</span>
                            <span class="pd-stat-mark">AG</span>
                        </div>
                        <h3><?php echo $agent_count; ?></h3>
                        <p>Active team</p>
                    </div>
                </div>

                <div class="pd-chart-grid">
                    <div class="pd-card pd-panel">
                        <h3>Monthly Trend</h3>
                        <div class="pd-chart-wrap tall">
                            <canvas id="monthlyTrendChart"></canvas>
                        </div>
                    </div>

                    <div class="pd-card pd-panel">
                        <h3>By Category</h3>
                        <div class="pd-chart-wrap tall">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const monthLabels = <?php echo json_encode($month_labels); ?>;
        const monthValues = <?php echo json_encode($month_values); ?>;
        const categoryLabels = <?php echo json_encode($category_labels); ?>;
        const categoryValues = <?php echo json_encode($category_values); ?>;

        new Chart(document.getElementById('monthlyTrendChart'), {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Tickets',
                    data: monthValues,
                    backgroundColor: '#3b82f6',
                    borderRadius: 6,
                    barPercentage: 0.38,
                    categoryPercentage: 0.7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { displayColors: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#8292a8' }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#8292a8' },
                        grid: { color: '#e5ebf2' }
                    }
                }
            }
        });

        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryValues,
                    backgroundColor: ['#23b7d9', '#2563eb', '#22c55e', '#f97316', '#f43f5e', '#94a3b8'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 8, color: '#43576d' }
                    }
                }
            }
        });
    </script>
</body>
</html>
