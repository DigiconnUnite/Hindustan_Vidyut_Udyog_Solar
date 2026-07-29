<?php
require_once __DIR__ . '/../../config/auth.php';
header('Content-Type: application/json');

$user = current_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Not authenticated']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?? [];

if (!hash_equals($_SESSION['csrf'] ?? '', $data['csrf'] ?? '')) {
    http_response_code(419);
    echo json_encode(['ok' => false, 'error' => 'Invalid session token, please reload the page.']);
    exit;
}

$jobId = (int) ($data['job_id'] ?? 0);
$status = $data['status'] ?? '';
$note = trim($data['note'] ?? '');
$validStatuses = ['new', 'site_survey', 'approved', 'installing', 'completed', 'cancelled'];

if (!in_array($status, $validStatuses, true)) {
    http_response_code(200);
    echo json_encode(['ok' => false, 'error' => 'Invalid status.']);
    exit;
}

$stmt = db()->prepare('SELECT id FROM installation_jobs WHERE id = ?');
$stmt->execute([$jobId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Job not found.']);
    exit;
}

if ($user['role'] !== 'admin') {
    $stmt = db()->prepare('SELECT 1 FROM job_team_members WHERE job_id = ? AND user_id = ?');
    $stmt->execute([$jobId, $user['id']]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'You are not assigned to this job.']);
        exit;
    }
}

db()->prepare('UPDATE installation_jobs SET status = ? WHERE id = ?')->execute([$status, $jobId]);
db()->prepare('INSERT INTO job_status_history (job_id, status, changed_by, note) VALUES (?, ?, ?, ?)')
    ->execute([$jobId, $status, $user['id'], $note ?: null]);

echo json_encode(['ok' => true, 'status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
