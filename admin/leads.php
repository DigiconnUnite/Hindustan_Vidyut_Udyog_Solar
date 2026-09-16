<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_login();
$pageTitle = 'Leads';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $leadId = (int) ($_POST['lead_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $stmt = db()->prepare('SELECT * FROM leads WHERE id = ?');
    $stmt->execute([$leadId]);
    $lead = $stmt->fetch();

    if (!$lead) {
        flash('error', 'Lead not found.');
        redirect('/admin/leads.php');
    }

    if ($action === 'contacted') {
        db()->prepare("UPDATE leads SET status = 'contacted' WHERE id = ?")->execute([$leadId]);
        flash('success', 'Lead marked as contacted.');
    } elseif ($action === 'convert') {
        db()->beginTransaction();
        $insert = db()->prepare(
            'INSERT INTO installation_jobs (lead_id, customer_name, customer_phone, address, status) VALUES (?, ?, ?, ?, ?)'
        );
        $insert->execute([$lead['id'], $lead['name'], $lead['phone'], $lead['address'] ?: 'TBD', 'new']);
        $jobId = db()->lastInsertId();

        db()->prepare('INSERT INTO job_status_history (job_id, status, changed_by, note) VALUES (?, ?, ?, ?)')
            ->execute([$jobId, 'new', $user['id'], 'Converted from lead']);

        db()->prepare("UPDATE leads SET status = 'converted', converted_job_id = ? WHERE id = ?")
            ->execute([$jobId, $leadId]);
        db()->commit();

        flash('success', 'Lead converted to job.');
        redirect('/admin/job.php?id=' . $jobId);
    }

    redirect('/admin/leads.php');
}

$statusFilter = $_GET['status'] ?? '';
$statuses = ['new', 'contacted', 'converted', 'closed'];

$sql = 'SELECT * FROM leads';
$params = [];
if (in_array($statusFilter, $statuses, true)) {
    $sql .= ' WHERE status = ?';
    $params[] = $statusFilter;
}
$sql .= ' ORDER BY created_at DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll();

$statusColors = [
    'new' => 'bg-gray-100 text-gray-700',
    'contacted' => 'bg-blue-50 text-blue-700',
    'converted' => 'bg-primary-50 text-primary-700',
    'closed' => 'bg-red-50 text-red-700',
];

require __DIR__ . '/../components/admin-header.php';
?>

<h1 class="mb-6 text-2xl font-semibold text-gray-900"><?= e($pageTitle) ?></h1>

<!-- Filter Section: Dropdown for Mobile, Badges for Tablet/Desktop -->
<div class="mb-6">
  <!-- Mobile Filter: Dropdown (< sm) -->
  <div class="block sm:hidden w-full">
    <label for="lead-status-filter" class="sr-only">Filter by Status</label>
    <select id="lead-status-filter" onchange="window.location.href=this.value" class="input w-full bg-white border border-gray-300 rounded-lg py-2 px-3 text-sm text-gray-700">
      <option value="/admin/leads.php" <?= $statusFilter === '' ? 'selected' : '' ?>>All Statuses</option>
      <?php foreach ($statuses as $status): ?>
        <option value="/admin/leads.php?status=<?= $status ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
          <?= e(ucfirst($status)) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <!-- Tablet/Desktop Filter: Badges (>= sm) -->
  <div class="hidden sm:flex gap-2 flex-wrap">
    <a href="/admin/leads.php" class="badge <?= $statusFilter === '' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">All</a>
    <?php foreach ($statuses as $status): ?>
      <a href="/admin/leads.php?status=<?= $status ?>" class="badge <?= $statusFilter === $status ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
        <?= e(ucfirst($status)) ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- Desktop Table View (>= md) -->
<div class="card overflow-x-auto hidden md:block">
  <table class="w-full text-sm">
    <thead>
      <tr class="text-left text-gray-500 border-b border-gray-100">
        <th class="py-2 pr-4">Name</th>
        <th class="py-2 pr-4">Phone</th>
        <th class="py-2 pr-4">Message</th>
        <th class="py-2 pr-4">Status</th>
        <th class="py-2 pr-4">Date</th>
        <th class="py-2 pr-4">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($leads as $lead): ?>
        <tr class="border-b border-gray-50">
          <td class="py-3 pr-4 font-medium text-gray-900"><?= e($lead['name']) ?></td>
          <td class="py-3 pr-4 text-gray-600"><?= e($lead['phone']) ?></td>
          <td class="py-3 pr-4 text-gray-600 max-w-xs truncate"><?= e($lead['message'] ?: '—') ?></td>
          <td class="py-3 pr-4"><span class="badge <?= $statusColors[$lead['status']] ?>"><?= e(ucfirst($lead['status'])) ?></span></td>
          <td class="py-3 pr-4 text-gray-500"><?= e($lead['created_at']) ?></td>
          <td class="py-3 pr-4 space-x-2 whitespace-nowrap">
            <?php if ($lead['status'] === 'new'): ?>
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                <input type="hidden" name="action" value="contacted">
                <button type="submit" class="text-xs text-primary-600 hover:underline">Mark Contacted</button>
              </form>
            <?php endif; ?>
            <?php if (in_array($lead['status'], ['new', 'contacted'], true)): ?>
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                <input type="hidden" name="action" value="convert">
                <button type="submit" class="text-xs text-accent-600 hover:underline">Convert to Job</button>
              </form>
            <?php endif; ?>
            <?php if ($lead['converted_job_id']): ?>
              <a href="/admin/job.php?id=<?= $lead['converted_job_id'] ?>" class="text-xs text-gray-500 hover:underline">View Job</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$leads): ?>
        <tr><td colspan="6" class="py-6 text-center text-gray-400">No leads found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Mobile & Tablet Card View (< md) -->
<div class="block md:hidden space-y-3">
  <?php foreach ($leads as $lead): ?>
    <div class="card p-4 border border-gray-100 rounded-lg bg-white space-y-3">
      <!-- Header: Name, Date & Status -->
      <div class="flex items-start justify-between gap-2">
        <div>
          <h3 class="font-medium text-gray-900 text-base"><?= e($lead['name']) ?></h3>
          <p class="text-xs text-gray-500 mt-0.5"><?= e($lead['phone']) ?></p>
        </div>
        <span class="badge shrink-0 <?= $statusColors[$lead['status']] ?>"><?= e(ucfirst($lead['status'])) ?></span>
      </div>

      <!-- Message Area -->
      <div class="text-sm text-gray-600 bg-gray-50 p-4 rounded-md break-words">
        <span class="text-xs text-gray-400 block mb-1">Message:</span>
        <?= e($lead['message'] ?: '—') ?>
      </div>

      <!-- Date & Actions Footer -->
      <div class="border-t border-gray-100 pt-2.5 flex items-center justify-between gap-2" style="padding-top: 10px;">
        <span class="text-xs text-gray-400"><?= e($lead['created_at']) ?></span>
        
        <div class="flex items-center gap-3">
          <?php if ($lead['status'] === 'new'): ?>
            <form method="post" class="inline">
              <?= csrf_field() ?>
              <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
              <input type="hidden" name="action" value="contacted">
              <button type="submit" class="text-xs font-medium text-primary-600 hover:underline">Mark Contacted</button>
            </form>
          <?php endif; ?>

          <?php if (in_array($lead['status'], ['new', 'contacted'], true)): ?>
            <form method="post" class="inline">
              <?= csrf_field() ?>
              <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
              <input type="hidden" name="action" value="convert">
              <button type="submit" class="text-xs font-medium text-accent-600 hover:underline">Convert to Job</button>
            </form>
          <?php endif; ?>

          <?php if ($lead['converted_job_id']): ?>
            <a href="/admin/job.php?id=<?= $lead['converted_job_id'] ?>" class="text-xs font-medium text-gray-500 hover:underline">View Job</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (!$leads): ?>
    <div class="card p-6 text-center text-gray-400">No leads found.</div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../components/admin-footer.php'; ?>
