<?php
// ============================================================
//  tasks_handler.php
//  Handles all AJAX requests from tasks.php
// ============================================================

date_default_timezone_set('Asia/Manila');
require_once('../../app/config/config.php');   // provides $conn (mysqli)

header('Content-Type: application/json');

// ── Helper: send JSON response ──────────────────────────────
function respond(bool $success, array $extra = []): void
{
    echo json_encode(array_merge(['success' => $success], $extra));
    exit;
}

// ── Helper: sanitize string input ───────────────────────────
function clean(mysqli $conn, ?string $val): string
{
    return $conn->real_escape_string(trim((string)$val));
}

// ── Route ───────────────────────────────────────────────────
$action = $_GET['action'] ?? '';

switch ($action) {

    // ────────────────────────────────────────────────────────
    //  LIST  — returns rows + stats + category breakdown
    // ────────────────────────────────────────────────────────
    case 'list':
        $filter = $_GET['filter'] ?? 'all';
        $search = clean($conn, $_GET['search'] ?? '');

        // Build WHERE clauses
        $where = [];

        if ($filter === 'pending')  $where[] = "t.status = 'Pending'";
        if ($filter === 'done')     $where[] = "t.status = 'Done'";
        if ($filter === 'high')     $where[] = "t.priority = 'High'";
        if ($filter === 'medium')   $where[] = "t.priority = 'Medium'";
        if ($filter === 'low')      $where[] = "t.priority = 'Low'";

        if ($search !== '') {
            $where[] = "t.title LIKE '%{$search}%'";
        }

        $whereSQL = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Main query — LEFT JOIN users for assignee name
        $sql = "
            SELECT
                t.id,
                t.title,
                t.category,
                t.priority,
                t.status,
                t.dueDate,
                t.assignedTo,
                CONCAT(u.firstName, ' ', u.lastName) AS assigneeName
            FROM tasks t
            LEFT JOIN users u ON u.id = t.assignedTo
            {$whereSQL}
            ORDER BY
                FIELD(t.priority,'High','Medium','Low'),
                FIELD(t.status,'Pending','Done'),
                t.dueDate ASC,
                t.createdAt DESC
        ";

        $result = $conn->query($sql);
        if (!$result) respond(false, ['error' => $conn->error]);

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        // ── Stats (always across ALL tasks, ignoring current filter) ──
        $statsSQL = "
            SELECT
                COUNT(*)                                          AS total,
                SUM(status = 'Done')                             AS done,
                SUM(status = 'Pending')                          AS pending,
                SUM(status = 'Pending' AND priority = 'High')    AS high_pending
            FROM tasks
        ";
        $sr   = $conn->query($statsSQL)->fetch_assoc();
        $stats = [
            'total'        => (int)$sr['total'],
            'done'         => (int)$sr['done'],
            'pending'      => (int)$sr['pending'],
            'high_pending' => (int)$sr['high_pending'],
        ];

        // ── Category breakdown ────────────────────────────────────────
        $catSQL = "
            SELECT
                category,
                COUNT(*)             AS total,
                SUM(status = 'Done') AS done
            FROM tasks
            GROUP BY category
            ORDER BY total DESC
        ";
        $cr   = $conn->query($catSQL);
        $categories = [];
        while ($c = $cr->fetch_assoc()) {
            $categories[] = [
                'category' => $c['category'],
                'total'    => (int)$c['total'],
                'done'     => (int)$c['done'],
            ];
        }

        respond(true, [
            'rows'       => $rows,
            'stats'      => $stats,
            'categories' => $categories,
        ]);


        // ────────────────────────────────────────────────────────
        //  ADD
        // ────────────────────────────────────────────────────────
    case 'add':
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $title      = clean($conn, $body['title']    ?? '');
        $category   = clean($conn, $body['category'] ?? 'General');
        $priority   = clean($conn, $body['priority'] ?? 'Medium');
        $status     = clean($conn, $body['status']   ?? 'Pending');
        $dueDate    = !empty($body['dueDate'])    ? clean($conn, $body['dueDate'])    : null;
        $assignedTo = !empty($body['assignedTo']) ? (int)$body['assignedTo']          : null;

        if ($title === '') respond(false, ['error' => 'Title is required.']);

        // Validate ENUM values
        $validCats  = ['Clinical', 'Admin', 'Inventory', 'Follow-up', 'General'];
        $validPris  = ['High', 'Medium', 'Low'];
        $validStats = ['Pending', 'Done'];

        if (!in_array($category, $validCats))  $category  = 'General';
        if (!in_array($priority, $validPris))  $priority  = 'Medium';
        if (!in_array($status,   $validStats)) $status    = 'Pending';

        $dueDateSQL    = $dueDate    ? "'{$dueDate}'"    : 'NULL';
        $assignedToSQL = $assignedTo ? (int)$assignedTo  : 'NULL';

        $sql = "
            INSERT INTO tasks (title, category, priority, status, dueDate, assignedTo)
            VALUES ('{$title}', '{$category}', '{$priority}', '{$status}', {$dueDateSQL}, {$assignedToSQL})
        ";

        if (!$conn->query($sql)) respond(false, ['error' => $conn->error]);

        // Log activity
        $newId = $conn->insert_id;
        logActivity($conn, 'Task Added', "Task \"{$title}\" was created.", $newId, 'Task');

        respond(true, ['id' => $newId]);


        // ────────────────────────────────────────────────────────
        //  EDIT
        // ────────────────────────────────────────────────────────
    case 'edit':
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $id         = (int)($body['id']         ?? 0);
        $title      = clean($conn, $body['title']    ?? '');
        $category   = clean($conn, $body['category'] ?? 'General');
        $priority   = clean($conn, $body['priority'] ?? 'Medium');
        $status     = clean($conn, $body['status']   ?? 'Pending');
        $dueDate    = !empty($body['dueDate'])    ? clean($conn, $body['dueDate'])    : null;
        $assignedTo = !empty($body['assignedTo']) ? (int)$body['assignedTo']          : null;

        if ($id === 0 || $title === '') respond(false, ['error' => 'Invalid input.']);

        $dueDateSQL    = $dueDate    ? "'{$dueDate}'" : 'NULL';
        $assignedToSQL = $assignedTo ? $assignedTo    : 'NULL';

        $sql = "
            UPDATE tasks SET
                title      = '{$title}',
                category   = '{$category}',
                priority   = '{$priority}',
                status     = '{$status}',
                dueDate    = {$dueDateSQL},
                assignedTo = {$assignedToSQL}
            WHERE id = {$id}
        ";

        if (!$conn->query($sql)) respond(false, ['error' => $conn->error]);

        logActivity($conn, 'Task Updated', "Task \"{$title}\" was updated.", $id, 'Task');

        respond(true);


        // ────────────────────────────────────────────────────────
        //  TOGGLE  (Pending ↔ Done)
        // ────────────────────────────────────────────────────────
    case 'toggle':
        $id = (int)($_POST['id'] ?? 0);
        if ($id === 0) respond(false, ['error' => 'Invalid ID.']);

        // Fetch current status
        $row = $conn->query("SELECT status, title FROM tasks WHERE id = {$id}")->fetch_assoc();
        if (!$row) respond(false, ['error' => 'Task not found.']);

        $newStatus = ($row['status'] === 'Done') ? 'Pending' : 'Done';
        $conn->query("UPDATE tasks SET status = '{$newStatus}' WHERE id = {$id}");

        logActivity($conn, 'Task Toggled', "Task \"{$row['title']}\" marked as {$newStatus}.", $id, 'Task');

        respond(true, ['newStatus' => $newStatus]);


        // ────────────────────────────────────────────────────────
        //  DELETE  (single)
        // ────────────────────────────────────────────────────────
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id === 0) respond(false, ['error' => 'Invalid ID.']);

        $row = $conn->query("SELECT title FROM tasks WHERE id = {$id}")->fetch_assoc();
        $conn->query("DELETE FROM tasks WHERE id = {$id}");

        if ($row) logActivity($conn, 'Task Deleted', "Task \"{$row['title']}\" was deleted.", $id, 'Task');

        respond(true);


        // ────────────────────────────────────────────────────────
        //  BULK: mark_all_done
        // ────────────────────────────────────────────────────────
    case 'mark_all_done':
        $conn->query("UPDATE tasks SET status = 'Done'");
        logActivity($conn, 'Bulk Action', 'All tasks marked as done.', null, null);
        respond(true);


        // ────────────────────────────────────────────────────────
        //  BULK: clear_done
        // ────────────────────────────────────────────────────────
    case 'clear_done':
        $conn->query("DELETE FROM tasks WHERE status = 'Done'");
        logActivity($conn, 'Bulk Action', 'All completed tasks were cleared.', null, null);
        respond(true);


        // ────────────────────────────────────────────────────────
        //  BULK: clear_all
        // ────────────────────────────────────────────────────────
    case 'clear_all':
        $conn->query("DELETE FROM tasks");
        logActivity($conn, 'Bulk Action', 'All tasks were deleted.', null, null);
        respond(true);


        // ────────────────────────────────────────────────────────
        //  GET USERS  — for "Assigned To" dropdown
        // ────────────────────────────────────────────────────────
    case 'get_users':
        $res   = $conn->query("SELECT id, CONCAT(firstName,' ',lastName) AS name FROM users ORDER BY firstName");
        $users = [];
        while ($u = $res->fetch_assoc()) {
            $users[] = ['id' => $u['id'], 'name' => $u['name']];
        }
        respond(true, ['data' => $users]);


        // ────────────────────────────────────────────────────────
        //  Unknown action
        // ────────────────────────────────────────────────────────
    default:
        respond(false, ['error' => 'Unknown action.']);
}


// ============================================================
//  Helper — insert into recentActivity
// ============================================================
function logActivity(mysqli $conn, string $type, string $desc, ?int $refId, ?string $refType): void
{
    $type    = $conn->real_escape_string($type);
    $desc    = $conn->real_escape_string($desc);
    $refIdSQL   = $refId   ? (int)$refId                          : 'NULL';
    $refTypeSQL = $refType ? "'{$conn->real_escape_string($refType)}'" : 'NULL';

    $conn->query("
        INSERT INTO recentActivity (activityType, description, referenceId, referenceType)
        VALUES ('{$type}', '{$desc}', {$refIdSQL}, {$refTypeSQL})
    ");
}
