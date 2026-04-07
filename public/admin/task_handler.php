<?php
require_once('../../app/config/config.php');
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// ── Helper: log activity ──────────────────────────────────────────
function logActivity($conn, $type, $desc, $refId = null)
{
    $refType = 'Task';
    $stmt = $conn->prepare("INSERT INTO recentActivity (activityType, description, referenceId, referenceType) VALUES (?,?,?,?)");
    $stmt->bind_param('ssis', $type, $desc, $refId, $refType);
    $stmt->execute();
}

// ── Helper: stats ─────────────────────────────────────────────────
function getStats($conn)
{
    $total        = $conn->query("SELECT COUNT(*) FROM tasks")->fetch_row()[0];
    $done         = $conn->query("SELECT COUNT(*) FROM tasks WHERE status='Done'")->fetch_row()[0];
    $pending      = $conn->query("SELECT COUNT(*) FROM tasks WHERE status='Pending'")->fetch_row()[0];
    $high_pending = $conn->query("SELECT COUNT(*) FROM tasks WHERE priority='High' AND status='Pending'")->fetch_row()[0];
    return compact('total', 'done', 'pending', 'high_pending');
}

// ── Helper: category breakdown ────────────────────────────────────
function getCategories($conn)
{
    $rows = $conn->query("
        SELECT category,
               COUNT(*) AS total,
               SUM(status='Done') AS done
        FROM tasks
        GROUP BY category
        ORDER BY total DESC
    ")->fetch_all(MYSQLI_ASSOC);
    return $rows;
}

// ════════════════════════════════════════════════════════════════
// LIST
// ════════════════════════════════════════════════════════════════
if ($action === 'list') {
    $filter = $_GET['filter'] ?? 'all';
    $search = '%' . ($conn->real_escape_string($_GET['search'] ?? '')) . '%';

    $where  = "WHERE title LIKE ?";
    $params = [$search];
    $types  = 's';

    switch ($filter) {
        case 'pending':
            $where .= " AND status = 'Pending'";
            break;
        case 'done':
            $where .= " AND status = 'Done'";
            break;
        case 'high':
            $where .= " AND priority = 'High'";
            break;
        case 'medium':
            $where .= " AND priority = 'Medium'";
            break;
        case 'low':
            $where .= " AND priority = 'Low'";
            break;
    }

    $sql = "
        SELECT t.*, u.firstName AS assigneeFirst, u.lastName AS assigneeLast,
               CONCAT(u.firstName,' ',u.lastName) AS assigneeName
        FROM tasks t
        LEFT JOIN users u ON u.id = t.assignedTo
        $where
        ORDER BY FIELD(t.priority,'High','Medium','Low'), t.createdAt DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'success'    => true,
        'rows'       => $rows,
        'stats'      => getStats($conn),
        'categories' => getCategories($conn),
    ]);
    exit;
}

// ════════════════════════════════════════════════════════════════
// ADD
// ════════════════════════════════════════════════════════════════
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    $title      = trim($body['title']      ?? '');
    $category   = $body['category']        ?? 'General';
    $priority   = $body['priority']        ?? 'Medium';
    $status     = $body['status']          ?? 'Pending';
    $dueDate    = !empty($body['dueDate'])  ? $body['dueDate']  : null;
    $assignedTo = !empty($body['assignedTo']) ? (int)$body['assignedTo'] : null;

    if (!$title) {
        echo json_encode(['success' => false, 'error' => 'Title required.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO tasks (title, category, priority, status, dueDate, assignedTo) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param('sssssi', $title, $category, $priority, $status, $dueDate, $assignedTo);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        logActivity($conn, 'task_add', "Task added: \"{$title}\"", $newId);
        echo json_encode(['success' => true, 'id' => $newId, 'stats' => getStats($conn)]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}

// ════════════════════════════════════════════════════════════════
// EDIT
// ════════════════════════════════════════════════════════════════
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    $id         = (int)($body['id']          ?? 0);
    $title      = trim($body['title']        ?? '');
    $category   = $body['category']          ?? 'General';
    $priority   = $body['priority']          ?? 'Medium';
    $status     = $body['status']            ?? 'Pending';
    $dueDate    = !empty($body['dueDate'])    ? $body['dueDate']    : null;
    $assignedTo = !empty($body['assignedTo']) ? (int)$body['assignedTo'] : null;

    if (!$title) {
        echo json_encode(['success' => false, 'error' => 'Title required.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE tasks SET title=?, category=?, priority=?, status=?, dueDate=?, assignedTo=? WHERE id=?");
    $stmt->bind_param('sssssii', $title, $category, $priority, $status, $dueDate, $assignedTo, $id);

    if ($stmt->execute()) {
        logActivity($conn, 'task_edit', "Task updated: \"{$title}\"", $id);
        echo json_encode(['success' => true, 'stats' => getStats($conn)]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}

// ════════════════════════════════════════════════════════════════
// TOGGLE STATUS (done <-> pending)
// ════════════════════════════════════════════════════════════════
if ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id  = (int)($_POST['id'] ?? 0);
    $row = $conn->query("SELECT title, status FROM tasks WHERE id=$id")->fetch_assoc();

    if (!$row) {
        echo json_encode(['success' => false]);
        exit;
    }

    $newStatus = $row['status'] === 'Done' ? 'Pending' : 'Done';
    $stmt = $conn->prepare("UPDATE tasks SET status=? WHERE id=?");
    $stmt->bind_param('si', $newStatus, $id);

    if ($stmt->execute()) {
        $verb = $newStatus === 'Done' ? 'completed' : 're-opened';
        logActivity($conn, 'task_toggle', "Task \"{$row['title']}\" {$verb}", $id);
        echo json_encode(['success' => true, 'newStatus' => $newStatus, 'stats' => getStats($conn)]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// ════════════════════════════════════════════════════════════════
// DELETE
// ════════════════════════════════════════════════════════════════
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = (int)($_POST['id'] ?? 0);
    $title = $conn->query("SELECT title FROM tasks WHERE id=$id")->fetch_row()[0] ?? '';

    $stmt = $conn->prepare("DELETE FROM tasks WHERE id=?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        if ($title) logActivity($conn, 'task_delete', "Task deleted: \"{$title}\"");
        echo json_encode(['success' => true, 'stats' => getStats($conn)]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// ════════════════════════════════════════════════════════════════
// BULK — mark all done
// ════════════════════════════════════════════════════════════════
if ($action === 'mark_all_done' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->query("UPDATE tasks SET status='Done'");
    logActivity($conn, 'task_bulk', "All tasks marked as done");
    echo json_encode(['success' => true, 'stats' => getStats($conn)]);
    exit;
}

// ════════════════════════════════════════════════════════════════
// BULK — clear completed
// ════════════════════════════════════════════════════════════════
if ($action === 'clear_done' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->query("DELETE FROM tasks WHERE status='Done'");
    logActivity($conn, 'task_bulk', "All completed tasks cleared");
    echo json_encode(['success' => true, 'stats' => getStats($conn)]);
    exit;
}

// ════════════════════════════════════════════════════════════════
// BULK — clear all
// ════════════════════════════════════════════════════════════════
if ($action === 'clear_all' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->query("DELETE FROM tasks");
    logActivity($conn, 'task_bulk', "All tasks cleared");
    echo json_encode(['success' => true, 'stats' => getStats($conn)]);
    exit;
}

// ════════════════════════════════════════════════════════════════
// GET USERS (for assignee dropdown)
// ════════════════════════════════════════════════════════════════
if ($action === 'get_users') {
    $rows = $conn->query("SELECT id, CONCAT(firstName,' ',lastName) AS name FROM users ORDER BY firstName")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
