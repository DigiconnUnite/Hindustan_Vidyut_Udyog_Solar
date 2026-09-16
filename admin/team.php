<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_admin();
$pageTitle = 'Team';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();
  $action = $_POST['action'] ?? 'create';

  if ($action === 'deactivate') {
    $id = (int) $_POST['id'];
    db()->prepare('UPDATE users SET is_active = 0 WHERE id = ?')->execute([$id]);
    flash('success', 'Team member deactivated.');
    redirect('/admin/team.php');
  }

  if ($action === 'activate') {
    $id = (int) $_POST['id'];
    db()->prepare('UPDATE users SET is_active = 1 WHERE id = ?')->execute([$id]);
    flash('success', 'Team member reactivated.');
    redirect('/admin/team.php');
  }

  // create or update
  $id = (int) ($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $role = in_array($_POST['role'] ?? '', ['admin', 'staff'], true) ? $_POST['role'] : 'staff';
  $password = $_POST['password'] ?? '';

  if ($name === '' || $email === '') {
    flash('error', 'Name and email are required.');
    redirect('/admin/team.php');
  }

  if ($id) {
    if ($password !== '') {
      db()->prepare('UPDATE users SET name = ?, email = ?, phone = ?, role = ?, password_hash = ? WHERE id = ?')
        ->execute([$name, $email, $phone ?: null, $role, password_hash($password, PASSWORD_DEFAULT), $id]);
    } else {
      db()->prepare('UPDATE users SET name = ?, email = ?, phone = ?, role = ? WHERE id = ?')
        ->execute([$name, $email, $phone ?: null, $role, $id]);
    }
    flash('success', 'Team member updated.');
  } else {
    if ($password === '') {
      flash('error', 'Password is required for new team members.');
      redirect('/admin/team.php');
    }
    db()->prepare('INSERT INTO users (name, email, password_hash, role, phone) VALUES (?, ?, ?, ?, ?)')
      ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role, $phone ?: null]);
    flash('success', 'Team member added.');
  }
  redirect('/admin/team.php');
}

$team = db()->query('SELECT * FROM users ORDER BY is_active DESC, name')->fetchAll();

require __DIR__ . '/../components/admin-header.php';
?>

<h1 class="mb-6 text-2xl font-semibold text-gray-900"><?= e($pageTitle) ?></h1>

<div class="flex items-center justify-end mb-6">
  <button onclick="openTeamModal()" class="btn-primary text-sm flex items-center justify-center flex-1 text-center">+ Add Team Member</button>
</div>

<!-- Desktop Table View (>= md) -->
<div class="card overflow-x-auto hidden md:block">
  <table class="w-full text-sm">
    <thead>
      <tr class="text-left text-gray-500 border-b border-gray-100">
        <th class="py-2 pr-4">Name</th>
        <th class="py-2 pr-4">Role</th>
        <th class="py-2 pr-4">Phone</th>
        <th class="py-2 pr-4">Status</th>
        <th class="py-2 pr-4">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($team as $member): ?>
        <tr class="border-b border-gray-50">
          <td class="py-3 pr-4 font-medium text-gray-900"><?= e($member['name']) ?></td>
          <td class="py-3 pr-4"><span class="badge bg-primary-50 text-primary-700"><?= e(ucfirst($member['role'])) ?></span></td>
          <td class="py-3 pr-4 text-gray-600"><?= e($member['phone'] ?: '—') ?></td>
          <td class="py-3 pr-4">
            <span class="badge <?= $member['is_active'] ? 'bg-primary-50 text-primary-700' : 'bg-gray-100 text-gray-500' ?>">
              <?= $member['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
          </td>
          <td class="py-3 pr-4 space-x-2 whitespace-nowrap">
            <button onclick='openTeamModal(<?= json_encode($member) ?>)' class="text-primary-600 hover:underline text-xs">Edit</button>
            <form method="post" class="inline">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= $member['id'] ?>">
              <input type="hidden" name="action" value="<?= $member['is_active'] ? 'deactivate' : 'activate' ?>">
              <button type="submit" class="text-xs hover:underline <?= $member['is_active'] ? 'text-red-600' : 'text-primary-600' ?>">
                <?= $member['is_active'] ? 'Deactivate' : 'Activate' ?>
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Mobile & Tablet Card View (< md) -->
<div class="block md:hidden space-y-3">
  <?php foreach ($team as $member): ?>
    <div class="card p-4 border border-gray-100 rounded-lg bg-white space-y-3">
      <!-- Name & Badges Header -->
      <div class="flex items-start justify-between gap-2">
        <div>
          <div class="header-res">
            <h3 class="font-medium text-gray-900 text-base"><?= e($member['name']) ?></h3>
            <span class="badge bg-primary-50 text-primary-700"><?= e(ucfirst($member['role'])) ?></span>
          </div>
          <p class="text-xs text-gray-500 mt-0.5"><?= e($member['phone'] ?: '—') ?></p>
        </div>
        <div class="flex flex-col items-end gap-1 shrink-0">
          <span class="badge <?= $member['is_active'] ? ' text-primary-700' : 'bg-gray-100 text-gray-500' ?>">
            <i class="fa-regular fa-circle-dot"></i>
            <?= $member['is_active'] ? 'Active' : 'Inactive' ?>
          </span>
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="border-t border-gray-100 pt-2.5 flex items-center justify-end gap-3">
        <button onclick='openTeamModal(<?= json_encode($member) ?>)' class="text-primary-600 hover:underline text-xs font-medium">Edit</button>
        <form method="post" class="inline">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= $member['id'] ?>">
          <input type="hidden" name="action" value="<?= $member['is_active'] ? 'deactivate' : 'activate' ?>">
          <button type="submit" class="text-xs hover:underline font-medium <?= $member['is_active'] ? 'text-red-600' : 'text-primary-600' ?>">
            <?= $member['is_active'] ? 'Deactivate' : 'Activate' ?>
          </button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div id="team-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center px-4 z-50">
  <div class="card w-full max-w-md">
    <h2 id="team-modal-title" class="font-semibold text-gray-900 mb-4">Add Team Member</h2>
    <form method="post" action="/admin/team.php" class="space-y-3">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="team-id" value="">
      <div>
        <label class="text-sm font-medium text-gray-700">Name *</label>
        <input type="text" name="name" id="team-name" required class="input mt-1">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Email *</label>
        <input type="email" name="email" id="team-email" required class="input mt-1">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Phone</label>
        <input type="text" name="phone" id="team-phone" class="input mt-1">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Role</label>
        <select name="role" id="team-role" class="input mt-1">
          <option value="staff">Staff</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Password <span id="team-password-hint" class="text-gray-400 font-normal"></span></label>
        <input type="password" name="password" id="team-password" class="input mt-1">
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('team-modal').classList.add('hidden')" class="btn-outline flex-1 flex items-center justify-center gap-2">
          Cancel
        </button>
        <button type="submit" class="btn-primary flex-1 flex items-center justify-center gap-2">
          Save
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openTeamModal(member) {
    document.getElementById('team-modal').classList.remove('hidden');
    const title = document.getElementById('team-modal-title');
    const hint = document.getElementById('team-password-hint');
    if (member) {
      title.textContent = 'Edit Team Member';
      document.getElementById('team-id').value = member.id;
      document.getElementById('team-name').value = member.name;
      document.getElementById('team-email').value = member.email;
      document.getElementById('team-phone').value = member.phone || '';
      document.getElementById('team-role').value = member.role;
      hint.textContent = '(leave blank to keep current)';
    } else {
      title.textContent = 'Add Team Member';
      document.getElementById('team-id').value = '';
      document.getElementById('team-name').value = '';
      document.getElementById('team-email').value = '';
      document.getElementById('team-phone').value = '';
      document.getElementById('team-role').value = 'staff';
      hint.textContent = '';
    }
    document.getElementById('team-password').value = '';
  }
</script>

<?php require __DIR__ . '/../components/admin-footer.php'; ?>