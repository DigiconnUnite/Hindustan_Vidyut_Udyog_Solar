<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_login();
$pageTitle = 'Dashboard';

$activeJobs = db()->query("SELECT COUNT(*) c FROM installation_jobs WHERE status NOT IN ('completed','cancelled')")->fetch()['c'];
$newLeads = db()->query("SELECT COUNT(*) c FROM leads WHERE status = 'new'")->fetch()['c'];
$teamCount = db()->query("SELECT COUNT(*) c FROM users WHERE is_active = 1")->fetch()['c'];
$completedThisMonth = db()->query(
    "SELECT COUNT(*) c FROM installation_jobs WHERE status = 'completed' AND MONTH(updated_at) = MONTH(CURRENT_DATE()) AND YEAR(updated_at) = YEAR(CURRENT_DATE())"
)->fetch()['c'];

$recentJobs = db()->query('SELECT * FROM installation_jobs ORDER BY created_at DESC LIMIT 5')->fetchAll();
$recentLeads = db()->query("SELECT * FROM leads WHERE status = 'new' ORDER BY created_at DESC LIMIT 5")->fetchAll();

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

<h1 class="mb-6 text-2xl font-semibold text-gray-900"><?= e($pageTitle) ?></h1>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
  <div class="card">
    <p class="text-sm text-gray-500">Active Jobs</p>
    <p class="mt-1 text-3xl font-bold text-gray-900"><?= (int) $activeJobs ?></p>
  </div>
  <div class="card">
    <p class="text-sm text-gray-500">New Leads</p>
    <p class="mt-1 text-3xl font-bold text-gray-900"><?= (int) $newLeads ?></p>
  </div>
  <div class="card">
    <p class="text-sm text-gray-500">Team Members</p>
    <p class="mt-1 text-3xl font-bold text-gray-900"><?= (int) $teamCount ?></p>
  </div>
  <div class="card">
    <p class="text-sm text-gray-500">Completed This Month</p>
    <p class="mt-1 text-3xl font-bold text-gray-900"><?= (int) $completedThisMonth ?></p>
  </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
  <div class="card">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-semibold text-gray-900">Recent Jobs</h2>
      <a href="/admin/jobs.php" class="text-sm text-primary-600 hover:underline extra-padding">
        View all
      </a>
    </div>
    <div class="space-y-3">
      <?php foreach ($recentJobs as $job): ?>
        <a href="/admin/job.php?id=<?= $job['id'] ?>" class="flex items-center justify-between rounded-lg hover:bg-gray-50 px-2 py-2">
          <div>
            <p class="text-sm font-medium text-gray-900"><?= e($job['customer_name']) ?></p>
            <p class="text-xs text-gray-500"><?= e($job['address']) ?></p>
          </div>
          <span class="badge <?= $statusColors[$job['status']] ?>"><?= e(str_replace('_', ' ', $job['status'])) ?></span>
        </a>
      <?php endforeach; ?>
      <?php if (!$recentJobs): ?><p class="text-sm text-gray-400">No jobs yet.</p><?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-semibold text-gray-900">Recent Leads</h2>
      <a href="/admin/leads.php" class="text-sm text-primary-600 hover:underline extra-padding">
        View all
      </a>
    </div>
    <div class="space-y-3">
      <?php foreach ($recentLeads as $lead): ?>
        <div class="flex items-center justify-between rounded-lg px-2 py-2">
          <div>
            <p class="text-sm font-medium text-gray-900"><?= e($lead['name']) ?></p>
            <p class="text-xs text-gray-500"><?= e($lead['phone']) ?></p>
          </div>
          <form method="post" action="/admin/leads.php">
            <?= csrf_field() ?>
            <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
            <input type="hidden" name="action" value="convert">
            <button type="submit" class="text-xs font-medium text-primary-600 hover:underline">Convert to Job</button>
          </form>
        </div>
      <?php endforeach; ?>
      <?php if (!$recentLeads): ?><p class="text-sm text-gray-400">No new leads.</p><?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../components/admin-footer.php'; ?>
