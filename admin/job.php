<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_login();

$jobId = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM installation_jobs WHERE id = ?');
$stmt->execute([$jobId]);
$job = $stmt->fetch();

if (!$job) {
    http_response_code(404);
    exit('Job not found.');
}

// Staff can only view/update jobs they're assigned to.
if ($user['role'] !== 'admin') {
    $stmt = db()->prepare('SELECT 1 FROM job_team_members WHERE job_id = ? AND user_id = ?');
    $stmt->execute([$jobId, $user['id']]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        exit('Forbidden: you are not assigned to this job.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $notes = trim($_POST['notes'] ?? '');
    db()->prepare('UPDATE installation_jobs SET notes = ? WHERE id = ?')->execute([$notes, $jobId]);
    flash('success', 'Notes updated.');
    redirect('/admin/job.php?id=' . $jobId);
}

$pageTitle = 'Job — ' . $job['customer_name'];

$statuses = ['new', 'site_survey', 'approved', 'installing', 'completed', 'cancelled'];
$statusColors = [
    'new' => 'bg-gray-100 text-gray-700',
    'site_survey' => 'bg-blue-50 text-blue-700',
    'approved' => 'bg-amber-50 text-amber-700',
    'installing' => 'bg-accent-500/10 text-accent-600',
    'completed' => 'bg-primary-50 text-primary-700',
    'cancelled' => 'bg-red-50 text-red-700',
];

$assignedStmt = db()->prepare(
    'SELECT u.id, u.name FROM job_team_members jtm JOIN users u ON u.id = jtm.user_id WHERE jtm.job_id = ?'
);
$assignedStmt->execute([$jobId]);
$assigned = $assignedStmt->fetchAll();
$assignedIds = array_column($assigned, 'id');

$allStaff = db()->query("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name")->fetchAll();

$historyStmt = db()->prepare(
    'SELECT h.*, u.name AS changed_by_name FROM job_status_history h JOIN users u ON u.id = h.changed_by WHERE h.job_id = ? ORDER BY h.created_at DESC'
);
$historyStmt->execute([$jobId]);
$history = $historyStmt->fetchAll();

require __DIR__ . '/../components/admin-header.php';
?>

<a href="/admin/jobs.php" class="text-sm text-primary-600 hover:underline">&larr; Back to Jobs</a>

<div class="grid gap-6 lg:grid-cols-3 mt-4">
  <div class="lg:col-span-2 space-y-6">
    <div class="card">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-900 text-lg"><?= e($job['customer_name']) ?></h2>
        <span id="status-badge" class="badge <?= $statusColors[$job['status']] ?>"><?= e(str_replace('_', ' ', $job['status'])) ?></span>
      </div>
      <dl class="grid grid-cols-2 gap-4 text-sm">
        <div><dt class="text-gray-500">Phone</dt><dd class="text-gray-900"><?= e($job['customer_phone']) ?></dd></div>
        <div><dt class="text-gray-500">Address</dt><dd class="text-gray-900"><?= e($job['address']) ?></dd></div>
        <div><dt class="text-gray-500">System Size</dt><dd class="text-gray-900"><?= e($job['system_size_kw'] ? $job['system_size_kw'] . ' kW' : '—') ?></dd></div>
        <div><dt class="text-gray-500">Created</dt><dd class="text-gray-900"><?= e($job['created_at']) ?></dd></div>
      </dl>

      <form method="post" class="mt-4">
        <?= csrf_field() ?>
        <label class="text-sm font-medium text-gray-700">Notes</label>
        <textarea name="notes" rows="3" class="input mt-1"><?= e($job['notes']) ?></textarea>
        <button type="submit" class="btn-outline text-sm mt-2 flex items-center justify-center flex-1 text-center" style="padding-left: 6px;">Save Notes</button>
      </form>
    </div>

    <div class="card">
      <h3 class="font-semibold text-gray-900 mb-3">Update Status</h3>
      <div class="flex flex-wrap gap-2" id="status-buttons">
        <?php foreach ($statuses as $status): ?>
          <button data-status="<?= $status ?>"
            class="badge <?= $job['status'] === $status ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?> status-btn">
            <?= e(str_replace('_', ' ', $status)) ?>
          </button>
        <?php endforeach; ?>
      </div>
      <p id="status-message" class="text-sm mt-3"></p>
    </div>

    <div class="card">
      <h3 class="font-semibold text-gray-900 mb-3">Status History</h3>
      <ul class="space-y-3 text-sm">
        <?php foreach ($history as $entry): ?>
          <li class="border-l-2 border-primary-200 pl-3">
            <span class="font-medium text-gray-900"><?= e(str_replace('_', ' ', $entry['status'])) ?></span>
            <span class="text-gray-500"> by <?= e($entry['changed_by_name']) ?> — <?= e($entry['created_at']) ?></span>
            <?php if ($entry['note']): ?><p class="text-gray-600"><?= e($entry['note']) ?></p><?php endif; ?>
          </li>
        <?php endforeach; ?>
        <?php if (!$history): ?><li class="text-gray-400">No history yet.</li><?php endif; ?>
      </ul>
    </div>
  </div>

  <div class="card h-fit">
    <h3 class="font-semibold text-gray-900 mb-3">Assigned Technicians</h3>
    <div id="assigned-list" class="space-y-2 mb-4">
      <?php foreach ($assigned as $tech): ?>
        <div class="flex items-center justify-between text-sm bg-gray-50 rounded-lg px-3 py-2" data-user-id="<?= $tech['id'] ?>">
          <span><?= e($tech['name']) ?></span>
          <?php if ($user['role'] === 'admin'): ?>
            <button class="text-red-600 text-xs hover:underline unassign-btn" data-user-id="<?= $tech['id'] ?>">Remove</button>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <?php if (!$assigned): ?><p class="text-sm text-gray-400" id="no-assigned">No one assigned yet.</p><?php endif; ?>
    </div>

    <?php if ($user['role'] === 'admin'): ?>
      <div class="flex gap-2">
        <select id="assign-select" class="input" style="display: flex; flex: 2;">
          <option value="">Assign technician…</option>
          <?php foreach ($allStaff as $staff): ?>
            <?php if (!in_array($staff['id'], $assignedIds, true)): ?>
              <option value="<?= $staff['id'] ?>"><?= e($staff['name']) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
        <button id="assign-btn" class="btn-outline text-sm whitespace-nowrap flex items-center justify-center flex-1 text-center">Add</button>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
const jobId = <?= (int) $jobId ?>;
const csrf = '<?= e(csrf_token()) ?>';
const statusColors = <?= json_encode($statusColors) ?>;

document.querySelectorAll('.status-btn').forEach(btn => {
  btn.addEventListener('click', async () => {
    const status = btn.dataset.status;
    const note = prompt('Optional note for this status change:') || '';
    const res = await fetch('/admin/api/job-status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ job_id: jobId, status, note, csrf }),
    });
    const data = await res.json();
    const msg = document.getElementById('status-message');
    if (data.ok) {
      document.querySelectorAll('.status-btn').forEach(b => {
        b.classList.remove('bg-primary-600', 'text-white');
        b.classList.add('bg-gray-100', 'text-gray-700');
      });
      btn.classList.add('bg-primary-600', 'text-white');
      btn.classList.remove('bg-gray-100', 'text-gray-700');
      const badge = document.getElementById('status-badge');
      badge.className = 'badge ' + statusColors[status];
      badge.textContent = status.replace('_', ' ');
      msg.textContent = 'Status updated.';
      msg.className = 'text-sm mt-3 text-primary-700';
      setTimeout(() => location.reload(), 600);
    } else {
      msg.textContent = data.error || 'Something went wrong.';
      msg.className = 'text-sm mt-3 text-red-600';
    }
  });
});

document.getElementById('assign-btn')?.addEventListener('click', async () => {
  const select = document.getElementById('assign-select');
  const userId = select.value;
  if (!userId) return;
  const res = await fetch('/admin/api/job-assign.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ job_id: jobId, user_id: userId, action: 'add', csrf }),
  });
  const data = await res.json();
  if (data.ok) location.reload();
  else alert(data.error || 'Something went wrong.');
});

document.querySelectorAll('.unassign-btn').forEach(btn => {
  btn.addEventListener('click', async () => {
    const userId = btn.dataset.userId;
    const res = await fetch('/admin/api/job-assign.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ job_id: jobId, user_id: userId, action: 'remove', csrf }),
    });
    const data = await res.json();
    if (data.ok) location.reload();
    else alert(data.error || 'Something went wrong.');
  });
});
</script>

<?php require __DIR__ . '/../components/admin-footer.php'; ?>
