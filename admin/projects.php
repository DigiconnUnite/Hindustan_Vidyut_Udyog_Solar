<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_admin();
$pageTitle = 'Projects';

$uploadDir = __DIR__ . '/../storage/uploads/projects/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (($_POST['action'] ?? 'save') === 'delete') {
        db()->prepare('UPDATE projects SET is_active = 0 WHERE id = ?')->execute([(int) $_POST['id']]);
        flash('success', 'Project removed.');
        redirect('/admin/projects.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $removeImage = $id && isset($_POST['remove_image']);
    $title = trim($_POST['title'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $kw = (float) ($_POST['system_kw'] ?? 0);

    if ($title === '' || $location === '' || $kw <= 0) {
        flash('error', 'Title, location and system size are required.');
        redirect('/admin/projects.php');
    }

    $slug = trim(preg_replace('/-+/', '-', preg_replace('/[^a-z0-9]+/', '-', strtolower($title))), '-');

    // Same upload rules as products: images only, 2MB ceiling.
    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) && $_FILES['image']['size'] <= 2 * 1024 * 1024) {
            $filename = bin2hex(random_bytes(8)) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
            $imagePath = 'storage/uploads/projects/' . $filename;
        } else {
            flash('error', 'Image must be jpg/png/webp under 2MB.');
            redirect('/admin/projects.php');
        }
    }

    $segments = ['residential', 'commercial', 'industrial', 'institutional'];
    $fields = [
        $title,
        $slug,
        $location,
        $kw,
        in_array($_POST['segment'] ?? '', $segments, true) ? $_POST['segment'] : 'residential',
        trim($_POST['completed_on'] ?? '') ?: null,
        trim($_POST['summary'] ?? '') ?: null,
        (int) ($_POST['monthly_savings'] ?? 0) ?: null,
        (int) ($_POST['sort_order'] ?? 0),
    ];

    if ($id) {
        if ($imagePath) {
            db()->prepare('UPDATE projects SET title=?, slug=?, location=?, system_kw=?, segment=?, completed_on=?, summary=?, monthly_savings=?, sort_order=?, image_path=?, is_active=1 WHERE id=?')
                ->execute([...$fields, $imagePath, $id]);
        } elseif ($removeImage) {
            db()->prepare('UPDATE projects SET title=?, slug=?, location=?, system_kw=?, segment=?, completed_on=?, summary=?, monthly_savings=?, sort_order=?, image_path=NULL, is_active=1 WHERE id=?')
                ->execute([...$fields, $id]);
        } else {
            db()->prepare('UPDATE projects SET title=?, slug=?, location=?, system_kw=?, segment=?, completed_on=?, summary=?, monthly_savings=?, sort_order=?, is_active=1 WHERE id=?')
                ->execute([...$fields, $id]);
        }
        flash('success', 'Project updated.');
    } else {
        db()->prepare('INSERT INTO projects (title, slug, location, system_kw, segment, completed_on, summary, monthly_savings, sort_order, image_path) VALUES (?,?,?,?,?,?,?,?,?,?)')
            ->execute([...$fields, $imagePath]);
        flash('success', 'Project added.');
    }
    redirect('/admin/projects.php');
}

$projects = db()->query('SELECT * FROM projects ORDER BY is_active DESC, sort_order')->fetchAll();

require __DIR__ . '/../components/admin-header.php';
?>

<h1 class="mb-6 text-2xl font-semibold text-gray-900"><?= e($pageTitle) ?></h1>

<div class="flex items-center justify-end mb-6">
  <button onclick="openProjectModal()" class="btn-primary text-sm flex flex-1 justify-center items-center gap-2">
    + Add Project
  </button>
</div>

<!-- ================= DESKTOP VIEW (Table Format) ================= -->
<div class="card overflow-x-auto hidden md:block">
  <table class="w-full text-sm">
    <thead>
      <tr class="text-left text-gray-500 border-b border-gray-100">
        <th class="py-2 pr-4">Image</th>
        <th class="py-2 pr-4">Title</th>
        <th class="py-2 pr-4">Location</th>
        <th class="py-2 pr-4">Size</th>
        <th class="py-2 pr-4">Segment</th>
        <th class="py-2 pr-4">Status</th>
        <th class="py-2 pr-4">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($projects as $p): ?>
        <tr class="border-b border-gray-50">
          <td class="py-3 pr-4">
            <?php if ($p['image_path']): ?>
              <img src="/<?= e($p['image_path']) ?>" alt="" class="h-9 w-12 rounded object-cover">
            <?php else: ?>
              <span class="text-xs text-gray-400">No Image</span>
            <?php endif; ?>
          </td>
          <td class="py-3 pr-4 font-medium text-gray-900"><?= e($p['title']) ?></td>
          <td class="py-3 pr-4 text-gray-600"><?= e($p['location']) ?></td>
          <td class="py-3 pr-4 text-gray-600"><?= e(rtrim(rtrim(number_format((float) $p['system_kw'], 1), '0'), '.')) ?> kW</td>
          <td class="py-3 pr-4 text-gray-600"><?= e(ucfirst($p['segment'])) ?></td>
          <td class="py-3 pr-4">
            <span class="badge <?= $p['is_active'] ? 'bg-primary-50 text-primary-700' : 'bg-gray-100 text-gray-500' ?>">
              <?= $p['is_active'] ? 'Active' : 'Removed' ?>
            </span>
          </td>
          <td class="py-3 pr-4 space-x-2 whitespace-nowrap">
            <button onclick='openProjectModal(<?= json_encode($p) ?>)' class="text-primary-600 hover:underline text-xs">Edit</button>
            <?php if ($p['is_active']): ?>
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="text-xs text-red-600 hover:underline">Remove</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ================= MOBILE & TABLET VIEW (Card Layout) ================= -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:hidden">
  <?php foreach ($projects as $p): ?>
    <div class="card p-4 flex flex-col justify-between space-y-3 border border-gray-100 rounded-lg shadow-sm bg-white">
      <div>
        <div class="flex items-start justify-between gap-2 mb-2">
          <div class="flex min-w-0 items-start gap-3">
            <?php if ($p['image_path']): ?>
              <img src="/<?= e($p['image_path']) ?>" alt="" class="h-12 w-12 shrink-0 rounded object-cover">
            <?php else: ?>
              <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded bg-gray-100 text-center text-[10px] text-gray-400">No Image</div>
            <?php endif; ?>
            <h3 class="font-medium text-gray-900 text-base leading-snug"><?= e($p['title']) ?></h3>
          </div>
          <span class="badge shrink-0 <?= $p['is_active'] ? 'bg-primary-50 text-primary-700' : 'bg-gray-100 text-gray-500' ?>">
            <?= $p['is_active'] ? 'Active' : 'Removed' ?>
          </span>
        </div>
        
        <p class="text-xs text-gray-500 mb-3"><?= e($p['location']) ?></p>

        <div class="grid grid-cols-2 gap-2 text-xs p-2.5 rounded-md">
          <div>
            <span class="text-gray-400 block">Size:</span>
            <span class="font-medium text-gray-700"><?= e(rtrim(rtrim(number_format((float) $p['system_kw'], 1), '0'), '.')) ?> kW</span>
          </div>
          <div>
            <span class="text-gray-400 block">Segment:</span>
            <span class="font-medium text-gray-700"><?= e(ucfirst($p['segment'])) ?></span>
          </div>
        </div>
      </div>

      <div class="pt-2 border-t border-gray-100 flex items-center justify-end space-x-3 gap-4">
        <button onclick='openProjectModal(<?= json_encode($p) ?>)' class="text-primary-600 hover:underline text-xs font-medium">Edit</button>
        <?php if ($p['is_active']): ?>
          <form method="post" class="flex">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="text-xs text-red-600 hover:underline font-medium">Remove</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- ================= MODAL COMPONENT (Unchanged) ================= -->
<div id="project-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center px-4 z-50 overflow-y-auto py-8">
  <div class="card w-full max-w-lg">
    <h2 id="project-modal-title" class="font-semibold text-gray-900 mb-4">Add Project</h2>
    <form method="post" action="/admin/projects.php" enctype="multipart/form-data" class="space-y-3">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="project-id" value="">
      <div>
        <label class="text-sm font-medium text-gray-700">Title *</label>
        <input type="text" name="title" id="project-title" required class="input mt-1">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Location *</label>
        <input type="text" name="location" id="project-location" required class="input mt-1" placeholder="Sector 57, Gurgaon">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-medium text-gray-700">System size (kW) *</label>
          <input type="number" name="system_kw" id="project-kw" step="0.5" min="0.5" required class="input mt-1">
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Segment</label>
          <select name="segment" id="project-segment" class="input mt-1">
            <option value="residential">Residential</option>
            <option value="commercial">Commercial</option>
            <option value="industrial">Industrial</option>
            <option value="institutional">Institutional</option>
          </select>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-medium text-gray-700">Completed on</label>
          <input type="date" name="completed_on" id="project-date" class="input mt-1">
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Monthly savings (₹)</label>
          <input type="number" name="monthly_savings" id="project-savings" min="0" class="input mt-1">
        </div>
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Summary</label>
        <textarea name="summary" id="project-summary" rows="3" class="input mt-1"></textarea>
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Sort Order</label>
        <input type="number" name="sort_order" id="project-sort" class="input mt-1" value="0">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Photo (jpg/png/webp, max 2MB)</label>
        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="input mt-1">
        <div id="project-image-current" class="hidden mt-2 rounded border border-gray-200 p-2 img-current-wrapper">
          <img id="project-image-preview" src="" alt="Current photo" class="h-20 w-20 rounded object-cover">
          <label class="mt-2 flex items-center gap-2 text-xs text-red-600">
            <input type="checkbox" name="remove_image" id="project-image-remove" value="1" class="rounded border-gray-300">
            Remove current photo
          </label>
        </div>
        <p id="project-image-hint" class="hidden mt-1 text-xs text-gray-500">Leave empty to keep the current photo.</p>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('project-modal').classList.add('hidden')" class="btn-outline flex flex-1 items-center justify-center">Cancel</button>
        <button type="submit" class="btn-primary flex flex-1 items-center justify-center">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
function openProjectModal(project) {
  document.getElementById('project-modal').classList.remove('hidden');
  var set = function (id, value) { document.getElementById(id).value = value; };
  var currentImage = project && project.image_path;
  document.getElementById('project-image-current').classList.toggle('hidden', !currentImage);
  document.getElementById('project-image-hint').classList.toggle('hidden', !currentImage);
  document.getElementById('project-image-remove').checked = false;
  document.getElementById('project-image-preview').src = currentImage ? '/' + currentImage : '';
  if (project) {
    document.getElementById('project-modal-title').textContent = 'Edit Project';
    set('project-id', project.id);
    set('project-title', project.title);
    set('project-location', project.location);
    set('project-kw', project.system_kw);
    set('project-segment', project.segment);
    set('project-date', project.completed_on || '');
    set('project-savings', project.monthly_savings || '');
    set('project-summary', project.summary || '');
    set('project-sort', project.sort_order);
  } else {
    document.getElementById('project-modal-title').textContent = 'Add Project';
    ['project-id', 'project-title', 'project-location', 'project-kw', 'project-date', 'project-savings', 'project-summary'].forEach(function (id) { set(id, ''); });
    set('project-segment', 'residential');
    set('project-sort', '0');
  }
}
</script>

<?php require __DIR__ . '/../components/admin-footer.php'; ?>
