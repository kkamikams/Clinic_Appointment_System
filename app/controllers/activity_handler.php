<?php

include('../../app/middleware/admin.php');
require_once('../../app/config/config.php');

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'recent':
        $rows = $conn->query("
            SELECT * FROM recentActivity
            ORDER BY createdAt DESC
            LIMIT 8
        ")->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'rows' => $rows]);
        break;

    case 'all':
        $limit  = max(1, (int)($_GET['limit'] ?? 20));
        $offset = max(0, (int)($_GET['offset'] ?? 0));
        $type   = $conn->real_escape_string($_GET['type'] ?? '');
        $where  = $type ? "WHERE activityType LIKE '%$type%'" : '';
        $total  = $conn->query("SELECT COUNT(*) FROM recentActivity $where")->fetch_row()[0];
        $rows   = $conn->query("SELECT * FROM recentActivity $where ORDER BY createdAt DESC LIMIT $limit OFFSET $offset")->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'rows' => $rows, 'total' => (int)$total]);
        break;

    case 'stats':
        $today  = date('Y-m-d');
        $todayCnt  = $conn->query("SELECT COUNT(*) FROM recentActivity WHERE DATE(createdAt)='$today'")->fetch_row()[0];
        $totalAppt = $conn->query("SELECT COUNT(*) FROM appointments WHERE appointmentDate='$today'")->fetch_row()[0];
        $onDuty    = $conn->query("SELECT COUNT(*) FROM doctors WHERE status='On Duty'")->fetch_row()[0];
        echo json_encode([
            'success'        => true,
            'today_activity' => (int)$todayCnt,
            'appt_today'     => (int)$totalAppt,
            'on_duty'        => (int)$onDuty,
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
