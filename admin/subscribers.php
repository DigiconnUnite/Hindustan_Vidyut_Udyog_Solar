<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_admin();
$pageTitle = 'Subscribers';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    db()->prepare('DELETE FROM newsletter_subscribers WHERE id = ?')->execute([(int) $_POST['id']]);
    flash('success', 'Subscriber removed.');
    redirect('/admin/subscribers.php');
}

// CSV export for whatever mailer the team actually uses.
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="subscribers-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Email', 'Subscribed on']);
    foreach (db()->query('SELECT email, created_at FROM newsletter_subscribers ORDER BY created_at DESC') as $row) {
        fputcsv($out, [$row['email'], $row['created_at']]);
    }
    fclose($out);
    exit;
}

$subscribers = db()->query('SELECT * FROM newsletter_subscribers ORDER BY created_at DESC')->fetchAll();

require __DIR__ . '/../components/admin-header.php';
?>

<h1 class="mb-6 text-2xl font-semibold text-gray-900"><?= e($pageTitle) ?></h1>

<div class="flex items-center justify-between mb-6">
  <p class="text-sm text-gray-600"><?= count($subscribers) ?> subscriber<?= count($subscribers) === 1 ? '' : 's' ?></p>
  <?php if ($subscribers): ?>
    <a href="/admin/subscribers.php?export=csv" class="btn-outline text-sm">Export CSV</a>
  <?php endif; ?>
</div>

<div class="card overflow-x-auto">
  <?php if (!$subscribers): ?>
    <p class="py-8 text-center text-gray-500">No subscribers yet.</p>
  <?php else: ?>
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-gray-500 border-b border-gray-100">
          <th class="py-2 pr-4">Email</th>
          <th class="py-2 pr-4">Subscribed</th>
          <th class="py-2 pr-4">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($subscribers as $s): ?>
          <tr class="border-b border-gray-50">
            <td class="py-3 pr-4 font-medium text-gray-900"><?= e($s['email']) ?></td>
            <td class="py-3 pr-4 text-gray-600"><?= e(date('d M Y', strtotime($s['created_at']))) ?></td>
            <td class="py-3 pr-4">
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                <button type="submit" class="text-xs text-red-600 hover:underline">Remove</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../components/admin-footer.php'; ?>
