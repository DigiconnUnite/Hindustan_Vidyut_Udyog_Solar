<?php
require_once __DIR__ . '/../../config/auth.php';
header('Content-Type: application/json');

$user = current_user();
if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Admin access required.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?? [];

if (!hash_equals($_SESSION['csrf'] ?? '', $data['csrf'] ?? '')) {
    http_response_code(419);
    echo json_encode(['ok' => false, 'error' => 'Invalid session token, please reload the page.']);
    exit;
}

$jobId = (int) ($data['job_id'] ?? 0);
$userId = (int) ($data['user_id'] ?? 0);
$action = $data['action'] ?? '';

if (!$jobId || !$userId || !in_array($action, ['add', 'remove'], true)) {
    echo json_encode(['ok' => false, 'error' => 'Invalid request.']);
    exit;
}

if ($action === 'add') {
    $stmt = db()->prepare('INSERT IGNORE INTO job_team_members (job_id, user_id) VALUES (?, ?)');
    $stmt->execute([$jobId, $userId]);
} else {
    $stmt = db()->prepare('DELETE FROM job_team_members WHERE job_id = ? AND user_id = ?');
    $stmt->execute([$jobId, $userId]);
}

echo json_encode(['ok' => true]);
