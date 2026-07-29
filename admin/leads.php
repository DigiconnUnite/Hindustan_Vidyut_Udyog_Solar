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

<div class="flex gap-2 flex-wrap mb-6">
  <a href="/admin/leads.php" class="badge <?= $statusFilter === '' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700' ?>">All</a>
  <?php foreach ($statuses as $status): ?>
    <a href="/admin/leads.php?status=<?= $status ?>" class="badge <?= $statusFilter === $status ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700' ?>">
      <?= e(ucfirst($status)) ?>
    </a>
  <?php endforeach; ?>
</div>

<div class="card overflow-x-auto">
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

<?php require __DIR__ . '/../components/admin-footer.php'; ?>
