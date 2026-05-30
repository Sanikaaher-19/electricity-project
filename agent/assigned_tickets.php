<?php
// ============================================
// Agent - Assigned Tickets Page
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
$ticket_id = intval($_GET['id'] ?? 0);
$status_error = '';
$status_success = '';
$note_error = '';
$note_success = '';
$allowed_statuses = ['Pending', 'Assigned', 'In Progress', 'On Hold', 'Resolved', 'Closed'];

function agentPillClass($value) {
    if ($value === 'Critical' || $value === 'High') return 'danger';
    if ($value === 'Resolved' || $value === 'Closed') return 'success';
    if ($value === 'In Progress' || $value === 'Assigned' || $value === 'Low') return 'info';
    return 'warning';
}

if ($ticket_id > 0) {
    $ticket = fetchOne(
        "SELECT t.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
         FROM tickets t
         JOIN users u ON t.user_id = u.id
         WHERE t.id = " . $ticket_id . " AND t.assigned_agent = " . $agent_id,
        $conn
    );

    if (!$ticket) {
        header('Location: assigned_tickets.php');
        exit();
    }

    if (isset($_POST['update_status'])) {
        $new_status = sanitizeInput($_POST['status'] ?? '');

        if (in_array($new_status, $allowed_statuses, true)) {
            $resolved_sql = ($new_status === 'Resolved' || $new_status === 'Closed') ? ", resolved_at = COALESCE(resolved_at, NOW())" : ", resolved_at = NULL";
            $update_query = "UPDATE tickets SET status = '" . mysqli_real_escape_string($conn, $new_status) . "'" . $resolved_sql . "
                             WHERE id = " . $ticket_id . " AND assigned_agent = " . $agent_id;

            if (executeQuery($update_query, $conn)) {
                $status_success = 'Ticket status updated successfully.';
                $ticket['status'] = $new_status;
            } else {
                $status_error = 'Failed to update status.';
            }
        } else {
            $status_error = 'Invalid status selected.';
        }
    }

    if (isset($_POST['add_note'])) {
        $note_text = sanitizeInput($_POST['note'] ?? '');
        $is_private = isset($_POST['is_private']) ? 1 : 0;

        if ($note_text === '') {
            $note_error = 'Note cannot be empty.';
        } else {
            $insert_query = "INSERT INTO agent_notes (ticket_id, agent_id, note, is_private)
                             VALUES (" . $ticket_id . ", " . $agent_id . ", '" . mysqli_real_escape_string($conn, $note_text) . "', " . $is_private . ")";

            if (executeQuery($insert_query, $conn)) {
                $note_success = 'Note added successfully.';
            } else {
                $note_error = 'Failed to add note.';
            }
        }
    }

    $messages = fetchAll("SELECT * FROM chat_messages WHERE ticket_id = " . $ticket_id . " ORDER BY created_at DESC LIMIT 8", $conn);
    $notes = fetchAll("SELECT * FROM agent_notes WHERE ticket_id = " . $ticket_id . " ORDER BY created_at DESC", $conn);
}

$tickets = fetchAll(
    "SELECT id, title, status, priority, category, created_at
     FROM tickets
     WHERE assigned_agent = " . $agent_id . "
     ORDER BY FIELD(priority, 'Critical', 'High', 'Medium', 'Low'), created_at DESC",
    $conn
);
$queue_count = intval(fetchOne(
    "SELECT COUNT(*) AS count FROM tickets WHERE assigned_agent = " . $agent_id,
    $conn
)['count'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigned Tickets - PowerDesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/agent-powerdesk.css" rel="stylesheet">
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
                <a href="dashboard.php"><span class="pd-nav-icon">OV</span> Overview</a>
                <a href="assigned_tickets.php" class="active"><span class="pd-nav-icon">TK</span> Assigned Tickets</a>
                <a href="../logout.php"><span class="pd-nav-icon">LO</span> Logout</a>
            </nav>
        </aside>

        <main class="pd-main">
            <header class="pd-header">
                <div>
                    <h1>Assigned Tickets</h1>
                    <p>Prioritize, update, and document consumer complaints</p>
                </div>
            </header>

            <section class="pd-content">
                <div class="pd-card pd-ticket-layout">
                    <div class="pd-ticket-list">
                        <h2>My Queue (<?php echo $queue_count; ?>)</h2>

                        <?php if (!empty($tickets)): ?>
                            <?php foreach ($tickets as $ticket_item): ?>
                                <a class="pd-ticket-item <?php echo intval($ticket_item['id']) === $ticket_id ? 'active' : ''; ?>" href="assigned_tickets.php?id=<?php echo intval($ticket_item['id']); ?>">
                                    <small>
                                        <span>TKT-<?php echo intval($ticket_item['id']); ?></span>
                                        <span><?php echo date('d M', strtotime($ticket_item['created_at'])); ?></span>
                                    </small>
                                    <h3><?php echo htmlspecialchars($ticket_item['title']); ?></h3>
                                    <div class="pd-ticket-meta">
                                        <span class="pd-pill <?php echo agentPillClass($ticket_item['priority']); ?>"><?php echo htmlspecialchars($ticket_item['priority']); ?></span>
                                        <span class="pd-pill <?php echo agentPillClass($ticket_item['status']); ?>"><?php echo htmlspecialchars($ticket_item['status']); ?></span>
                                    </div>
                                    <div class="pd-sla">
                                        <span><?php echo htmlspecialchars($ticket_item['category'] ?: 'General'); ?></span>
                                        <span><?php echo ($ticket_item['priority'] === 'Critical' || $ticket_item['priority'] === 'High') ? 'Urgent' : 'Standard'; ?></span>
                                    </div>
                                    <div class="pd-sla-bar"><span style="width: <?php echo ($ticket_item['priority'] === 'Critical' || $ticket_item['priority'] === 'High') ? '72' : '42'; ?>%;"></span></div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="pd-ticket-item">
                                <p style="color: #8292a8; margin: 0;">No assigned tickets.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($ticket_id > 0 && !empty($ticket)): ?>
                        <div class="pd-ticket-detail">
                            <div class="pd-detail-header">
                                <div>
                                    <small>TKT-<?php echo intval($ticket['id']); ?></small>
                                    <h2><?php echo htmlspecialchars($ticket['title']); ?></h2>
                                    <div class="pd-ticket-meta">
                                        <span class="pd-pill <?php echo agentPillClass($ticket['priority']); ?>"><?php echo htmlspecialchars($ticket['priority']); ?></span>
                                        <span class="pd-pill <?php echo agentPillClass($ticket['status']); ?>"><?php echo htmlspecialchars($ticket['status']); ?></span>
                                        <span class="pd-pill info"><?php echo htmlspecialchars($ticket['category'] ?: 'General'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <?php if ($status_success): ?><div class="pd-alert success"><?php echo htmlspecialchars($status_success); ?></div><?php endif; ?>
                            <?php if ($status_error): ?><div class="pd-alert danger"><?php echo htmlspecialchars($status_error); ?></div><?php endif; ?>
                            <?php if ($note_success): ?><div class="pd-alert success"><?php echo htmlspecialchars($note_success); ?></div><?php endif; ?>
                            <?php if ($note_error): ?><div class="pd-alert danger"><?php echo htmlspecialchars($note_error); ?></div><?php endif; ?>

                            <div class="pd-detail-grid">
                                <div class="pd-detail-box">
                                    <span>Customer</span>
                                    <strong><?php echo htmlspecialchars($ticket['customer_name']); ?></strong>
                                    <p><?php echo htmlspecialchars($ticket['customer_email']); ?></p>
                                    <p><?php echo htmlspecialchars($ticket['customer_phone'] ?: 'No phone'); ?></p>
                                </div>
                                <div class="pd-detail-box">
                                    <span>Created</span>
                                    <strong><?php echo date('d M Y, h:i A', strtotime($ticket['created_at'])); ?></strong>
                                    <p>Updated: <?php echo !empty($ticket['updated_at']) ? date('d M Y, h:i A', strtotime($ticket['updated_at'])) : 'Not updated'; ?></p>
                                </div>
                                <div class="pd-detail-box">
                                    <span>Resolution</span>
                                    <strong><?php echo !empty($ticket['resolved_at']) ? date('d M Y, h:i A', strtotime($ticket['resolved_at'])) : 'Pending'; ?></strong>
                                    <p><?php echo htmlspecialchars($ticket['status']); ?></p>
                                </div>
                                <div class="pd-detail-box">
                                    <span>Service Type</span>
                                    <strong><?php echo htmlspecialchars($ticket['category'] ?: 'General'); ?></strong>
                                    <p><?php echo htmlspecialchars($ticket['priority']); ?> priority</p>
                                </div>
                            </div>

                            <div class="agent-detail-layout">
                                <div>
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

                                    <div class="pd-detail-section">
                                        <h3>Recent Conversation</h3>
                                        <?php if (!empty($messages)): ?>
                                            <?php foreach ($messages as $message): ?>
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
                                </div>

                                <div>
                                    <div class="pd-detail-section">
                                        <h3>Update Status</h3>
                                        <form method="POST">
                                            <select name="status" class="form-select mb-3">
                                                <?php foreach ($allowed_statuses as $status): ?>
                                                    <option value="<?php echo $status; ?>" <?php echo $ticket['status'] === $status ? 'selected' : ''; ?>><?php echo $status; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="update_status" class="pd-btn" style="border: 0;">Update Status</button>
                                        </form>
                                    </div>

                                    <div class="pd-detail-section">
                                        <h3>Add Agent Note</h3>
                                        <form method="POST">
                                            <textarea name="note" class="form-control mb-3" rows="5" placeholder="Add field observation or internal note..."></textarea>
                                            <label class="form-check mb-3">
                                                <input type="checkbox" name="is_private" value="1" checked class="form-check-input">
                                                <span class="form-check-label">Private note</span>
                                            </label>
                                            <button type="submit" name="add_note" class="pd-btn" style="border: 0;">Add Note</button>
                                        </form>
                                    </div>

                                    <div class="pd-detail-section">
                                        <h3>Notes</h3>
                                        <?php if (!empty($notes)): ?>
                                            <?php foreach ($notes as $note): ?>
                                                <div class="pd-mini-row">
                                                    <strong><?php echo $note['is_private'] ? 'Private Note' : 'Public Note'; ?></strong>
                                                    <span><?php echo date('d M, h:i A', strtotime($note['created_at'])); ?></span>
                                                    <p><?php echo htmlspecialchars($note['note']); ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="pd-empty-text">No notes yet.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="pd-ticket-empty">
                            <div class="pd-ticket-empty-icon">TK</div>
                            <h2>Select a ticket</h2>
                            <p>Click a ticket in your queue to view details and update progress.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
