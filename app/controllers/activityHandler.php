<?php

include('../../app/middleware/admin.php');
require_once('../../app/config/config.php');
require_once(__DIR__ . '/../../app/models/ActivityModel.php');

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$model  = new ActivityModel($conn);

switch ($action) {

    case 'recent':
        echo json_encode([
            'success' => true,
            'rows'    => $model->recent(),
        ]);
        break;

    case 'all':
        $limit  = max(1,  (int)($_GET['limit']  ?? 20));
        $offset = max(0,  (int)($_GET['offset'] ?? 0));
        $type   = trim($_GET['type'] ?? '');

        $result = $model->all($limit, $offset, $type);

        echo json_encode([
            'success' => true,
            'rows'    => $result['rows'],
            'total'   => $result['total'],
        ]);
        break;

    case 'stats':
        echo json_encode([
            'success' => true,
            ...$model->stats(),
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
