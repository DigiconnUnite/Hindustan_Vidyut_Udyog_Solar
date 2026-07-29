<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_login();
$pageTitle = 'Jobs';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $customerName = trim($_POST['customer_name'] ?? '');
    $customerPhone = trim($_POST['customer_phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $systemSize = trim($_POST['system_size_kw'] ?? '');
    $leadId = $_POST['lead_id'] ?? null;

    if ($customerName === '' || $customerPhone === '' || $address === '') {
        flash('error', 'Customer name, phone and address are required.');
        redirect('/admin/jobs.php');
    }

    $stmt = db()->prepare(
        'INSERT INTO installation_jobs (lead_id, customer_name, customer_phone, address, system_size_kw, status) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$leadId ?: null, $customerName, $customerPhone, $address, $systemSize ?: null, 'new']);
    $jobId = db()->lastInsertId();

    db()->prepare('INSERT INTO job_status_history (job_id, status, changed_by, note) VALUES (?, ?, ?, ?)')
        ->execute([$jobId, 'new', $user['id'], 'Job created']);

    flash('success', 'Job created.');
    redirect('/admin/job.php?id=' . $jobId);
}

$statusFilter = $_GET['status'] ?? '';
$statuses = ['new', 'site_survey', 'approved', 'installing', 'completed', 'cancelled'];

$sql = 'SELECT j.*, GROUP_CONCAT(u.name SEPARATOR ", ") AS technicians
        FROM installation_jobs j
        LEFT JOIN job_team_members jtm ON jtm.job_id = j.id
        LEFT JOIN users u ON u.id = jtm.user_id';
$params = [];
if (in_array($statusFilter, $statuses, true)) {
    $sql .= ' WHERE j.status = ?';
    $params[] = $statusFilter;
}
$sql .= ' GROUP BY j.id ORDER BY j.created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

$statusColors = [
    'new' => 'bg-gray-100 text-gray-700',
    'site_survey' => 'bg-blue-50 text-blue-700',
    'approved' => 'bg-amber-50 text-amber-700',
    'installing' => 'bg-accent-500/10 text-accent-600',
    'completed' => 'bg-primary-50 text-primary-700',
    'cancelled' => 'bg-red-50 text-red-700',
];

require __DIR__ . '/../components/admin-header.php';
?>

<div class="flex items-center justify-between mb-6">
  <div class="flex gap-2 flex-wrap">
    <a href="/admin/jobs.php" class="badge <?= $statusFilter === '' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700' ?>">All</a>
    <?php foreach ($statuses as $status): ?>
      <a href="/admin/jobs.php?status=<?= $status ?>" class="badge <?= $statusFilter === $status ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700' ?>">
        <?= e(str_replace('_', ' ', $status)) ?>
      </a>
    <?php endforeach; ?>
  </div>
  <button onclick="document.getElementById('new-job-modal').classList.remove('hidden')" class="btn-primary text-sm">+ New Job</button>
</div>

<div class="card overflow-x-auto">
  <table class="w-full text-sm">
    <thead>
      <tr class="text-left text-gray-500 border-b border-gray-100">
        <th class="py-2 pr-4">Customer</th>
        <th class="py-2 pr-4">Address</th>
        <th class="py-2 pr-4">Status</th>
        <th class="py-2 pr-4">Technician(s)</th>
        <th class="py-2 pr-4">Updated</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($jobs as $job): ?>
        <tr class="border-b border-gray-50 hover:bg-gray-50 cursor-pointer" onclick="window.location='/admin/job.php?id=<?= $job['id'] ?>'">
          <td class="py-3 pr-4 font-medium text-gray-900"><?= e($job['customer_name']) ?></td>
          <td class="py-3 pr-4 text-gray-600"><?= e($job['address']) ?></td>
          <td class="py-3 pr-4"><span class="badge <?= $statusColors[$job['status']] ?>"><?= e(str_replace('_', ' ', $job['status'])) ?></span></td>
          <td class="py-3 pr-4 text-gray-600"><?= e($job['technicians'] ?: '—') ?></td>
          <td class="py-3 pr-4 text-gray-500"><?= e($job['updated_at'] ?? $job['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$jobs): ?>
        <tr><td colspan="5" class="py-6 text-center text-gray-400">No jobs found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div id="new-job-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center px-4 z-50">
  <div class="card w-full max-w-md">
    <h2 class="font-semibold text-gray-900 mb-4">New Job</h2>
    <form method="post" action="/admin/jobs.php" class="space-y-3">
      <?= csrf_field() ?>
      <div>
        <label class="text-sm font-medium text-gray-700">Customer Name *</label>
        <input type="text" name="customer_name" required class="input mt-1">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Phone *</label>
        <input type="tel" name="customer_phone" required class="input mt-1">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Address *</label>
        <input type="text" name="address" required class="input mt-1">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">System Size (kW)</label>
        <input type="number" step="0.1" name="system_size_kw" class="input mt-1">
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('new-job-modal').classList.add('hidden')" class="btn-outline flex-1">Cancel</button>
        <button type="submit" class="btn-primary flex-1">Create</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../components/admin-footer.php'; ?>
