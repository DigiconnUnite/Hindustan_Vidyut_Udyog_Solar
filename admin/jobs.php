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

<!-- Header Area: Filters & Action Button -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
  
  <!-- Mobile Filter: Dropdown (< sm) -->
  <div class="flex sm:hidden w-full" style="flex: 4;">
    <label for="status-filter" class="sr-only">Filter by Status</label>
    <select id="status-filter" onchange="window.location.href=this.value" class="input w-full bg-white border border-gray-300 rounded-lg py-2 px-3 text-sm text-gray-700">
      <option value="/admin/jobs.php" <?= $statusFilter === '' ? 'selected' : '' ?>>All Statuses</option>
      <?php foreach ($statuses as $status): ?>
        <option value="/admin/jobs.php?status=<?= $status ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
          <?= e(str_replace('_', ' ', $status)) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <!-- Desktop/Tablet Filter: Badges (>= sm) -->
  <div class="hidden sm:flex gap-2 flex-wrap">
    <a href="/admin/jobs.php" class="badge <?= $statusFilter === '' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">All</a>
    <?php foreach ($statuses as $status): ?>
      <a href="/admin/jobs.php?status=<?= $status ?>" class="badge <?= $statusFilter === $status ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
        <?= e(str_replace('_', ' ', $status)) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Action Button -->
  <button onclick="document.getElementById('new-job-modal').classList.remove('hidden')" class="btn-primary text-sm w-full sm:w-auto shrink-0 flex items-center justify-center flex-1 text-center">+ New Job</button>
</div>

<!-- Desktop Table View (>= md) -->
<div class="card overflow-x-auto hidden md:block">
  <table class="w-full text-sm text-left">
    <thead>
      <tr class="text-gray-500 border-b border-gray-100">
        <th class="py-3 px-4">Customer</th>
        <th class="py-3 px-4">Address</th>
        <th class="py-3 px-4">Status</th>
        <th class="py-3 px-4">Technician(s)</th>
        <th class="py-3 px-4">Updated</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-50">
      <?php foreach ($jobs as $job): ?>
        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='/admin/job.php?id=<?= $job['id'] ?>'">
          <td class="py-3 px-4 font-medium text-gray-900"><?= e($job['customer_name']) ?></td>
          <td class="py-3 px-4 text-gray-600"><?= e($job['address']) ?></td>
          <td class="py-3 px-4"><span class="badge <?= $statusColors[$job['status']] ?>"><?= e(str_replace('_', ' ', $job['status'])) ?></span></td>
          <td class="py-3 px-4 text-gray-600"><?= e($job['technicians'] ?: '—') ?></td>
          <td class="py-3 px-4 text-gray-500"><?= e($job['updated_at'] ?? $job['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$jobs): ?>
        <tr><td colspan="5" class="py-6 text-center text-gray-400">No jobs found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Mobile & Tablet Card View (< md) -->
<div class="block md:hidden space-y-3">
  <?php foreach ($jobs as $job): ?>
    <div class="card p-4 hover:shadow-md transition-shadow cursor-pointer border border-gray-100 rounded-lg bg-white" onclick="window.location='/admin/job.php?id=<?= $job['id'] ?>'">
      <div class="flex justify-between items-start mb-2 gap-2">
        <h3 class="font-medium text-gray-900 text-base"><?= e($job['customer_name']) ?></h3>
        <span class="badge shrink-0 <?= $statusColors[$job['status']] ?>"><?= e(str_replace('_', ' ', $job['status'])) ?></span>
      </div>
      
      <div class="text-sm text-gray-600 space-y-1 mb-3">
        <p class="flex items-start gap-1">
          <span class="text-gray-400 font-normal shrink-0">Address:</span>
          <span><?= e($job['address']) ?></span>
        </p>
        <p class="flex items-center gap-1">
          <span class="text-gray-400 font-normal shrink-0">Technician:</span>
          <span><?= e($job['technicians'] ?: '—') ?></span>
        </p>
      </div>

      <div class="text-xs text-gray-400 border-t border-gray-50 pt-2 flex justify-end">
        Updated: <?= e($job['updated_at'] ?? $job['created_at']) ?>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (!$jobs): ?>
    <div class="card p-6 text-center text-gray-400">No jobs found.</div>
  <?php endif; ?>
</div>

<!-- Modal Component -->
<div id="new-job-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50 overflow-y-auto">
  <div class="card w-full max-w-md my-8">
    <h2 class="font-semibold text-gray-900 mb-4 text-lg">New Job</h2>
    <form method="post" action="/admin/jobs.php" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="text-sm font-medium text-gray-700 block">Customer Name *</label>
        <input type="text" name="customer_name" required class="input mt-1 w-full">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700 block">Phone *</label>
        <input type="tel" name="customer_phone" required class="input mt-1 w-full">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700 block">Address *</label>
        <input type="text" name="address" required class="input mt-1 w-full">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700 block">System Size (kW)</label>
        <input type="number" step="0.1" name="system_size_kw" class="input mt-1 w-full">
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('new-job-modal').classList.add('hidden')" class="btn-outline flex items-center justify-center flex-1 text-center">Cancel</button>
        <button type="submit" class="btn-primary flex items-center justify-center flex-1 text-center">Create</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../components/admin-footer.php'; ?>
