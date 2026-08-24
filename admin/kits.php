<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_admin();
$pageTitle = 'Solar Kits';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (($_POST['action'] ?? 'save') === 'delete') {
        db()->prepare('UPDATE solar_kits SET is_active = 0 WHERE id = ?')->execute([(int) $_POST['id']]);
        flash('success', 'Kit removed.');
        redirect('/admin/kits.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $kw = (float) ($_POST['system_kw'] ?? 0);
    $price = (float) ($_POST['price'] ?? 0);

    if ($name === '' || $kw <= 0 || $price <= 0) {
        flash('error', 'Name, system size and price are required.');
        redirect('/admin/kits.php');
    }

    // Slug is derived, not typed — avoids admins creating two kits that collide.
    $slug = trim(preg_replace('/-+/', '-', preg_replace('/[^a-z0-9]+/', '-', strtolower($name))), '-');

    $fields = [
        $name,
        $slug,
        $kw,
        in_array($_POST['kit_type'] ?? '', ['ongrid', 'offgrid', 'hybrid'], true) ? $_POST['kit_type'] : 'ongrid',
        $price,
        (float) ($_POST['subsidy'] ?? 0),
        (int) ($_POST['monthly_units'] ?? 0) ?: null,
        trim($_POST['suits'] ?? '') ?: null,
        trim($_POST['includes'] ?? '') ?: null,
        isset($_POST['is_featured']) ? 1 : 0,
        (int) ($_POST['sort_order'] ?? 0),
    ];

    if ($id) {
        db()->prepare('UPDATE solar_kits SET name=?, slug=?, system_kw=?, kit_type=?, price=?, subsidy=?, monthly_units=?, suits=?, includes=?, is_featured=?, sort_order=?, is_active=1 WHERE id=?')
            ->execute([...$fields, $id]);
        flash('success', 'Kit updated.');
    } else {
        db()->prepare('INSERT INTO solar_kits (name, slug, system_kw, kit_type, price, subsidy, monthly_units, suits, includes, is_featured, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
            ->execute($fields);
        flash('success', 'Kit added.');
    }
    redirect('/admin/kits.php');
}

$kits = db()->query('SELECT * FROM solar_kits ORDER BY is_active DESC, sort_order')->fetchAll();

require __DIR__ . '/../components/admin-header.php';
?>

<div class="flex items-center justify-end mb-6">
  <button onclick="openKitModal()" class="btn-primary text-sm">+ Add Kit</button>
</div>

<div class="card overflow-x-auto">
  <table class="w-full text-sm">
    <thead>
      <tr class="text-left text-gray-500 border-b border-gray-100">
        <th class="py-2 pr-4">Name</th>
        <th class="py-2 pr-4">Size</th>
        <th class="py-2 pr-4">Type</th>
        <th class="py-2 pr-4">Price</th>
        <th class="py-2 pr-4">Subsidy</th>
        <th class="py-2 pr-4">Status</th>
        <th class="py-2 pr-4">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($kits as $kit): ?>
        <tr class="border-b border-gray-50">
          <td class="py-3 pr-4 font-medium text-gray-900">
            <?= e($kit['name']) ?>
            <?php if ($kit['is_featured']): ?>
              <span class="badge ml-1 bg-accent-500/15 text-accent-600">Featured</span>
            <?php endif; ?>
          </td>
          <td class="py-3 pr-4 text-gray-600"><?= e(rtrim(rtrim(number_format((float) $kit['system_kw'], 1), '0'), '.')) ?> kW</td>
          <td class="py-3 pr-4 text-gray-600"><?= e(ucfirst($kit['kit_type'])) ?></td>
          <td class="py-3 pr-4 text-gray-600">₹<?= number_format((float) $kit['price']) ?></td>
          <td class="py-3 pr-4 text-gray-600">₹<?= number_format((float) $kit['subsidy']) ?></td>
          <td class="py-3 pr-4">
            <span class="badge <?= $kit['is_active'] ? 'bg-primary-50 text-primary-700' : 'bg-gray-100 text-gray-500' ?>">
              <?= $kit['is_active'] ? 'Active' : 'Removed' ?>
            </span>
          </td>
          <td class="py-3 pr-4 space-x-2 whitespace-nowrap">
            <button onclick='openKitModal(<?= json_encode($kit) ?>)' class="text-primary-600 hover:underline text-xs">Edit</button>
            <?php if ($kit['is_active']): ?>
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $kit['id'] ?>">
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

<div id="kit-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center px-4 z-50 overflow-y-auto py-8">
  <div class="card w-full max-w-lg">
    <h2 id="kit-modal-title" class="font-semibold text-gray-900 mb-4">Add Kit</h2>
    <form method="post" action="/admin/kits.php" class="space-y-3">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="kit-id" value="">
      <div>
        <label class="text-sm font-medium text-gray-700">Name *</label>
        <input type="text" name="name" id="kit-name" required class="input mt-1">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-medium text-gray-700">System size (kW) *</label>
          <input type="number" name="system_kw" id="kit-kw" step="0.5" min="0.5" required class="input mt-1">
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Type</label>
          <select name="kit_type" id="kit-type" class="input mt-1">
            <option value="ongrid">On-Grid</option>
            <option value="hybrid">Hybrid</option>
            <option value="offgrid">Off-Grid</option>
          </select>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-medium text-gray-700">Price (₹) *</label>
          <input type="number" name="price" id="kit-price" step="1" min="0" required class="input mt-1">
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Subsidy (₹)</label>
          <input type="number" name="subsidy" id="kit-subsidy" step="1" min="0" class="input mt-1" value="0">
        </div>
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Generates (units/month)</label>
        <input type="number" name="monthly_units" id="kit-units" min="0" class="input mt-1">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Suits (short line shown under the name)</label>
        <input type="text" name="suits" id="kit-suits" class="input mt-1" placeholder="3 BHK · bill ₹2,000–3,000">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Includes (one component per line)</label>
        <textarea name="includes" id="kit-includes" rows="6" class="input mt-1"></textarea>
      </div>
      <div class="grid grid-cols-2 gap-3 items-end">
        <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
          <input type="checkbox" name="is_featured" id="kit-featured" value="1" class="rounded border-gray-300 text-primary-600">
          Featured on homepage
        </label>
        <div>
          <label class="text-sm font-medium text-gray-700">Sort Order</label>
          <input type="number" name="sort_order" id="kit-sort" class="input mt-1" value="0">
        </div>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('kit-modal').classList.add('hidden')" class="btn-outline flex-1">Cancel</button>
        <button type="submit" class="btn-primary flex-1">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
function openKitModal(kit) {
  document.getElementById('kit-modal').classList.remove('hidden');
  var set = function (id, value) { document.getElementById(id).value = value; };
  if (kit) {
    document.getElementById('kit-modal-title').textContent = 'Edit Kit';
    set('kit-id', kit.id);
    set('kit-name', kit.name);
    set('kit-kw', kit.system_kw);
    set('kit-type', kit.kit_type);
    set('kit-price', kit.price);
    set('kit-subsidy', kit.subsidy);
    set('kit-units', kit.monthly_units || '');
    set('kit-suits', kit.suits || '');
    set('kit-includes', kit.includes || '');
    set('kit-sort', kit.sort_order);
    document.getElementById('kit-featured').checked = kit.is_featured == 1;
  } else {
    document.getElementById('kit-modal-title').textContent = 'Add Kit';
    ['kit-id', 'kit-name', 'kit-kw', 'kit-price', 'kit-units', 'kit-suits', 'kit-includes'].forEach(function (id) { set(id, ''); });
    set('kit-type', 'ongrid');
    set('kit-subsidy', '0');
    set('kit-sort', '0');
    document.getElementById('kit-featured').checked = false;
  }
}
</script>

<?php require __DIR__ . '/../components/admin-footer.php'; ?>
