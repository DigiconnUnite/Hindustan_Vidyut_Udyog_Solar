<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_admin();
$pageTitle = 'Services';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete') {
        db()->prepare('DELETE FROM services WHERE id = ?')->execute([(int) $_POST['id']]);
        flash('success', 'Service removed.');
        redirect('/admin/services.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);

    if ($title === '') {
        flash('error', 'Title is required.');
        redirect('/admin/services.php');
    }

    if ($id) {
        db()->prepare('UPDATE services SET title=?, description=?, sort_order=? WHERE id=?')
            ->execute([$title, $description, $sortOrder, $id]);
        flash('success', 'Service updated.');
    } else {
        db()->prepare('INSERT INTO services (title, description, sort_order) VALUES (?,?,?)')
            ->execute([$title, $description, $sortOrder]);
        flash('success', 'Service added.');
    }
    redirect('/admin/services.php');
}

$services = db()->query('SELECT * FROM services ORDER BY sort_order')->fetchAll();

require __DIR__ . '/../components/admin-header.php';
?>

<div class="flex items-center justify-end mb-6">
  <button onclick="openServiceModal()" class="btn-primary text-sm">+ Add Service</button>
</div>

<div class="card overflow-x-auto">
  <table class="w-full text-sm">
    <thead>
      <tr class="text-left text-gray-500 border-b border-gray-100">
        <th class="py-2 pr-4">Title</th>
        <th class="py-2 pr-4">Description</th>
        <th class="py-2 pr-4">Order</th>
        <th class="py-2 pr-4">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($services as $service): ?>
        <tr class="border-b border-gray-50">
          <td class="py-3 pr-4 font-medium text-gray-900"><?= e($service['title']) ?></td>
          <td class="py-3 pr-4 text-gray-600 max-w-sm truncate"><?= e($service['description']) ?></td>
          <td class="py-3 pr-4 text-gray-500"><?= (int) $service['sort_order'] ?></td>
          <td class="py-3 pr-4 space-x-2 whitespace-nowrap">
            <button onclick='openServiceModal(<?= json_encode($service) ?>)' class="text-primary-600 hover:underline text-xs">Edit</button>
            <form method="post" class="inline" onsubmit="return confirm('Remove this service?')">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= $service['id'] ?>">
              <input type="hidden" name="action" value="delete">
              <button type="submit" class="text-xs text-red-600 hover:underline">Remove</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div id="service-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center px-4 z-50">
  <div class="card w-full max-w-md">
    <h2 id="service-modal-title" class="font-semibold text-gray-900 mb-4">Add Service</h2>
    <form method="post" action="/admin/services.php" class="space-y-3">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="service-id" value="">
      <div>
        <label class="text-sm font-medium text-gray-700">Title *</label>
        <input type="text" name="title" id="service-title" required class="input mt-1">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" id="service-description" rows="3" class="input mt-1"></textarea>
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Sort Order</label>
        <input type="number" name="sort_order" id="service-sort" class="input mt-1" value="0">
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('service-modal').classList.add('hidden')" class="btn-outline flex-1">Cancel</button>
        <button type="submit" class="btn-primary flex-1">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
function openServiceModal(service) {
  document.getElementById('service-modal').classList.remove('hidden');
  const title = document.getElementById('service-modal-title');
  if (service) {
    title.textContent = 'Edit Service';
    document.getElementById('service-id').value = service.id;
    document.getElementById('service-title').value = service.title;
    document.getElementById('service-description').value = service.description || '';
    document.getElementById('service-sort').value = service.sort_order;
  } else {
    title.textContent = 'Add Service';
    document.getElementById('service-id').value = '';
    document.getElementById('service-title').value = '';
    document.getElementById('service-description').value = '';
    document.getElementById('service-sort').value = '0';
  }
}
</script>

<?php require __DIR__ . '/../components/admin-footer.php'; ?>
