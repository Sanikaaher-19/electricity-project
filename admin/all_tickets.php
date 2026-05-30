<?php
// ============================================
// Admin - All Tickets Page
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

$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$query = "SELECT t.id, t.title, t.priority, t.status, t.category, t.created_at, u.name
          FROM tickets t
          JOIN users u ON t.user_id = u.id
          ORDER BY t.created_at DESC
          LIMIT " . $offset . ", " . $per_page;

$tickets = fetchAll($query, $conn);
$total = intval(fetchOne("SELECT COUNT(*) as count FROM tickets", $conn)['count'] ?? 0);
$total_pages = max(1, ceil($total / $per_page));
$selected_ticket_id = intval($_GET['ticket_id'] ?? 0);

if ($selected_ticket_id === 0 && !empty($tickets)) {
    $selected_ticket_id = intval($tickets[0]['id']);
}

$selected_ticket = null;
$ticket_messages = [];
$ticket_notes = [];

if ($selected_ticket_id > 0) {
    $selected_ticket = fetchOne(
        "SELECT t.*, 
                c.name AS customer_name, c.email AS customer_email, c.phone AS customer_phone,
                a.name AS agent_name, a.email AS agent_email
         FROM tickets t
         JOIN users c ON t.user_id = c.id
         LEFT JOIN users a ON t.assigned_agent = a.id
         WHERE t.id = " . $selected_ticket_id,
        $conn
    );

    if ($selected_ticket) {
        $ticket_messages = fetchAll(
            "SELECT sender, message, created_at FROM chat_messages
             WHERE ticket_id = " . $selected_ticket_id . "
             ORDER BY created_at DESC LIMIT 5",
            $conn
        );

        $ticket_notes = fetchAll(
            "SELECT note, is_private, created_at FROM agent_notes
             WHERE ticket_id = " . $selected_ticket_id . "
             ORDER BY created_at DESC LIMIT 5",
            $conn
        );
    }
}

function adminStatusClass($status) {
    if ($status === 'Resolved' || $status === 'Closed') return 'success';
    if ($status === 'In Progress' || $status === 'Assigned') return 'info';
    if ($status === 'On Hold') return 'warning';
    return 'warning';
}

function adminPriorityClass($priority) {
    if ($priority === 'Critical' || $priority === 'High') return 'danger';
    if ($priority === 'Medium') return 'warning';
    return 'info';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Tickets - PowerDesk</title>
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
                <a href="all_tickets.php" class="active"><span class="pd-nav-icon">TK</span> All Tickets</a>
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
                <div class="pd-card pd-ticket-layout">
                    <div class="pd-ticket-list">
                        <h2>All Tickets (<?php echo $total; ?>)</h2>

                        <div class="pd-filter-row">
                            <span class="active">All</span>
                            <span>High</span>
                            <span>Medium</span>
                            <span>Low</span>
                        </div>
                        <div class="pd-filter-row">
                            <span class="active">All</span>
                            <span>Open</span>
                            <span>In Progress</span>
                            <span>Resolved</span>
                        </div>

                        <?php if (!empty($tickets)): ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <a class="pd-ticket-item <?php echo intval($ticket['id']) === $selected_ticket_id ? 'active' : ''; ?>" href="all_tickets.php?page=<?php echo $page; ?>&ticket_id=<?php echo intval($ticket['id']); ?>">
                                    <small>
                                        <span>TKT-<?php echo intval($ticket['id']); ?></span>
                                        <span><?php echo date('d M', strtotime($ticket['created_at'])); ?></span>
                                    </small>
                                    <h3><?php echo htmlspecialchars($ticket['title']); ?></h3>
                                    <div class="pd-ticket-meta">
                                        <span class="pd-pill <?php echo adminPriorityClass($ticket['priority']); ?>"><?php echo htmlspecialchars($ticket['priority']); ?></span>
                                        <span class="pd-pill <?php echo adminStatusClass($ticket['status']); ?>"><?php echo htmlspecialchars($ticket['status']); ?></span>
                                    </div>
                                    <div class="pd-sla">
                                        <span><?php echo htmlspecialchars($ticket['name']); ?></span>
                                        <span><?php echo htmlspecialchars($ticket['category'] ?: 'General'); ?></span>
                                    </div>
                                    <div class="pd-sla-bar"><span style="width: <?php echo ($ticket['priority'] === 'Critical' || $ticket['priority'] === 'High') ? '70' : '40'; ?>%;"></span></div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="pd-ticket-item">
                                <p style="color: #8292a8; margin: 0;">No tickets found.</p>
                            </div>
                        <?php endif; ?>

                        <?php if ($total_pages > 1): ?>
                            <nav style="margin-top: 20px;">
                                <ul class="pagination">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="all_tickets.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>

                    <?php if ($selected_ticket): ?>
                        <div class="pd-ticket-detail">
                            <div class="pd-detail-header">
                                <div>
                                    <small>TKT-<?php echo intval($selected_ticket['id']); ?></small>
                                    <h2><?php echo htmlspecialchars($selected_ticket['title']); ?></h2>
                                    <div class="pd-ticket-meta">
                                        <span class="pd-pill <?php echo adminPriorityClass($selected_ticket['priority']); ?>"><?php echo htmlspecialchars($selected_ticket['priority']); ?></span>
                                        <span class="pd-pill <?php echo adminStatusClass($selected_ticket['status']); ?>"><?php echo htmlspecialchars($selected_ticket['status']); ?></span>
                                        <span class="pd-pill info"><?php echo htmlspecialchars($selected_ticket['category'] ?: 'General'); ?></span>
                                    </div>
                                </div>
                                <a class="pd-btn" href="update_ticket.php?id=<?php echo intval($selected_ticket['id']); ?>">Edit Ticket</a>
                            </div>

                            <div class="pd-detail-grid">
                                <div class="pd-detail-box">
                                    <span>Customer</span>
                                    <strong><?php echo htmlspecialchars($selected_ticket['customer_name']); ?></strong>
                                    <p><?php echo htmlspecialchars($selected_ticket['customer_email']); ?></p>
                                    <p><?php echo htmlspecialchars($selected_ticket['customer_phone'] ?: 'No phone'); ?></p>
                                </div>
                                <div class="pd-detail-box">
                                    <span>Assigned Agent</span>
                                    <strong><?php echo htmlspecialchars($selected_ticket['agent_name'] ?: 'Unassigned'); ?></strong>
                                    <p><?php echo htmlspecialchars($selected_ticket['agent_email'] ?: 'Assign from edit screen'); ?></p>
                                </div>
                                <div class="pd-detail-box">
                                    <span>Created</span>
                                    <strong><?php echo date('d M Y, h:i A', strtotime($selected_ticket['created_at'])); ?></strong>
                                    <p>Updated: <?php echo !empty($selected_ticket['updated_at']) ? date('d M Y, h:i A', strtotime($selected_ticket['updated_at'])) : 'Not updated'; ?></p>
                                </div>
                                <div class="pd-detail-box">
                                    <span>Resolved</span>
                                    <strong><?php echo !empty($selected_ticket['resolved_at']) ? date('d M Y, h:i A', strtotime($selected_ticket['resolved_at'])) : 'Pending'; ?></strong>
                                    <p><?php echo ($selected_ticket['status'] === 'Closed') ? 'Ticket closed' : 'Active ticket'; ?></p>
                                </div>
                            </div>

                            <div class="pd-detail-section">
                                <h3>Description</h3>
                                <p><?php echo nl2br(htmlspecialchars($selected_ticket['description'])); ?></p>
                            </div>

                            <?php if (!empty($selected_ticket['ai_response'])): ?>
                                <div class="pd-detail-section info">
                                    <h3>AI Analysis</h3>
                                    <p><?php echo nl2br(htmlspecialchars($selected_ticket['ai_response'])); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($selected_ticket['resolution_notes'])): ?>
                                <div class="pd-detail-section success">
                                    <h3>Resolution Notes</h3>
                                    <p><?php echo nl2br(htmlspecialchars($selected_ticket['resolution_notes'])); ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="pd-detail-columns">
                                <div class="pd-detail-section">
                                    <h3>Recent Messages</h3>
                                    <?php if (!empty($ticket_messages)): ?>
                                        <?php foreach ($ticket_messages as $message): ?>
                                            <div class="pd-mini-row">
                                                <strong><?php echo htmlspecialchars(ucfirst($message['sender'])); ?></strong>
                                                <span><?php echo date('d M, h:i A', strtotime($message['created_at'])); ?></span>
                                                <p><?php echo htmlspecialchars($message['message']); ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="pd-empty-text">No messages yet.</p>
                                    <?php endif; ?>
                                </div>

                                <div class="pd-detail-section">
                                    <h3>Agent Notes</h3>
                                    <?php if (!empty($ticket_notes)): ?>
                                        <?php foreach ($ticket_notes as $note): ?>
                                            <div class="pd-mini-row">
                                                <strong><?php echo $note['is_private'] ? 'Private Note' : 'Public Note'; ?></strong>
                                                <span><?php echo date('d M, h:i A', strtotime($note['created_at'])); ?></span>
                                                <p><?php echo htmlspecialchars($note['note']); ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="pd-empty-text">No agent notes yet.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="pd-ticket-empty">
                            <div class="pd-ticket-empty-icon">TK</div>
                            <h2>Select a ticket</h2>
                            <p>Click a ticket to view full details.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
