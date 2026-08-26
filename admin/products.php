<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_admin();
$pageTitle = 'Products';

$uploadDir = __DIR__ . '/../storage/uploads/products/';

// Kits and catalogue products are both managed here. They still live in separate
// tables (a kit is a system, a product is a component), so every write branches on
// `kind` — but the admin only ever shows one Products screen.
$isKit = ($_POST['kind'] ?? $_GET['kind'] ?? '') === 'kit';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? 'save';
    $table = $isKit ? 'solar_kits' : 'products';
    $noun = $isKit ? 'Kit' : 'Product';

    if ($action === 'delete') {
        db()->prepare("UPDATE {$table} SET is_active = 0 WHERE id = ?")->execute([(int) $_POST['id']]);
        flash('success', $noun . ' removed.');
        redirect('/admin/products.php');
    }

    $id = (int) ($_POST['id'] ?? 0);

    // Shared by both kinds: the image upload kits never had until now.
    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) && $_FILES['image']['size'] <= 2 * 1024 * 1024) {
            $filename = bin2hex(random_bytes(8)) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
            $imagePath = 'storage/uploads/products/' . $filename;
        } else {
            flash('error', 'Image must be jpg/png/webp under 2MB.');
            redirect('/admin/products.php');
        }
    }

    if ($isKit) {
        $name = trim($_POST['name'] ?? '');
        $kw = (float) ($_POST['system_kw'] ?? 0);
        $price = (float) ($_POST['price'] ?? 0);

        if ($name === '' || $kw <= 0 || $price <= 0) {
            flash('error', 'Name, system size and price are required for a kit.');
            redirect('/admin/products.php');
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
            // Only overwrite image_path when a new file came through, or an edit that
            // leaves the file input empty would blank the existing image.
            $imageSql = $imagePath ? ', image_path=?' : '';
            $imageArg = $imagePath ? [$imagePath] : [];
            db()->prepare('UPDATE solar_kits SET name=?, slug=?, system_kw=?, kit_type=?, price=?, subsidy=?, monthly_units=?, suits=?, includes=?, is_featured=?, sort_order=?, is_active=1' . $imageSql . ' WHERE id=?')
                ->execute([...$fields, ...$imageArg, $id]);
            flash('success', 'Kit updated.');
        } else {
            db()->prepare('INSERT INTO solar_kits (name, slug, system_kw, kit_type, price, subsidy, monthly_units, suits, includes, is_featured, sort_order, image_path) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([...$fields, $imagePath]);
            flash('success', 'Kit added.');
        }
        redirect('/admin/products.php');
    }

    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $specs = trim($_POST['specs'] ?? '');
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);
    $brand = trim($_POST['brand'] ?? '');
    // Empty price means "not published" rather than free, so keep it NULL.
    $numeric = fn(string $key) => trim($_POST[$key] ?? '') !== '' ? $_POST[$key] : null;
    $price = ($v = $numeric('price')) !== null ? (float) $v : null;
    $mrp = ($v = $numeric('mrp')) !== null ? (float) $v : null;
    $wattage = ($v = $numeric('wattage')) !== null ? (int) $v : null;
    $warranty = ($v = $numeric('warranty_years')) !== null ? (int) $v : null;
    $inStock = isset($_POST['in_stock']) ? 1 : 0;

    if ($name === '' || $category === '') {
        flash('error', 'Name and category are required.');
        redirect('/admin/products.php');
    }

    $shared = [$name, $category, $description, $specs, $sortOrder, $brand, $price, $mrp, $wattage, $warranty, $inStock];

    if ($id) {
        // Only overwrite image_path when a new file actually came through, or an
        // edit that leaves the file input empty would blank the existing image.
        if ($imagePath) {
            db()->prepare('UPDATE products SET name=?, category=?, description=?, specs=?, sort_order=?, brand=?, price=?, mrp=?, wattage=?, warranty_years=?, in_stock=?, image_path=? WHERE id=?')
                ->execute([...$shared, $imagePath, $id]);
        } else {
            db()->prepare('UPDATE products SET name=?, category=?, description=?, specs=?, sort_order=?, brand=?, price=?, mrp=?, wattage=?, warranty_years=?, in_stock=? WHERE id=?')
                ->execute([...$shared, $id]);
        }
        flash('success', 'Product updated.');
    } else {
        db()->prepare('INSERT INTO products (name, category, description, specs, sort_order, brand, price, mrp, wattage, warranty_years, in_stock, image_path) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([...$shared, $imagePath]);
        flash('success', 'Product added.');
    }
    redirect('/admin/products.php');
}

// Kits first, matching the public grid where a complete system leads.
$kits = db()->query('SELECT * FROM solar_kits ORDER BY is_active DESC, sort_order')->fetchAll();
$catalogue = db()->query('SELECT * FROM products ORDER BY is_active DESC, sort_order')->fetchAll();

require __DIR__ . '/../components/admin-header.php';
?>

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
  <p class="text-sm text-gray-500">
    <?= count($kits) ?> kit<?= count($kits) === 1 ? '' : 's' ?>
    and <?= count($catalogue) ?> component<?= count($catalogue) === 1 ? '' : 's' ?>.
    Kits are complete systems; components are the individual parts.
  </p>
  <div class="flex gap-2">
    <button onclick="openItemModal('kit')" class="btn-primary text-sm">+ Add Kit</button>
    <button onclick="openItemModal('product')" class="btn-outline text-sm">+ Add Component</button>
  </div>
</div>

<div class="card overflow-x-auto">
  <table class="w-full text-sm">
    <thead>
      <tr class="text-left text-gray-500 border-b border-gray-100">
        <th class="py-2 pr-4">Name</th>
        <th class="py-2 pr-4">Type</th>
        <th class="py-2 pr-4">Size / Category</th>
        <th class="py-2 pr-4">Price</th>
        <th class="py-2 pr-4">Subsidy</th>
        <th class="py-2 pr-4">Image</th>
        <th class="py-2 pr-4">Status</th>
        <th class="py-2 pr-4">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      // [row, kind] — one loop over both tables so the screen reads as a single catalogue.
      $rows = [];
      foreach ($kits as $r) { $rows[] = [$r, 'kit']; }
      foreach ($catalogue as $r) { $rows[] = [$r, 'product']; }
      ?>
      <?php foreach ($rows as [$row, $kind]): $rowIsKit = $kind === 'kit'; ?>
        <tr class="border-b border-gray-50">
          <td class="py-3 pr-4 font-medium text-gray-900">
            <?= e($row['name']) ?>
            <?php if ($rowIsKit && $row['is_featured']): ?>
              <span class="badge ml-1 bg-accent-500/15 text-accent-600">Featured</span>
            <?php endif; ?>
          </td>
          <td class="py-3 pr-4">
            <span class="badge <?= $rowIsKit ? 'bg-accent-500/15 text-accent-600' : 'bg-gray-100 text-gray-600' ?>">
              <?= $rowIsKit ? 'Kit' : 'Component' ?>
            </span>
          </td>
          <td class="py-3 pr-4 text-gray-600">
            <?= $rowIsKit
                ? e(rtrim(rtrim(number_format((float) $row['system_kw'], 1), '0'), '.') . ' kW · ' . ucfirst($row['kit_type']))
                : e(ucfirst($row['category'])) ?>
          </td>
          <td class="py-3 pr-4 text-gray-600"><?= $row['price'] ? '₹' . number_format((float) $row['price']) : '—' ?></td>
          <td class="py-3 pr-4 text-gray-600"><?= $rowIsKit ? '₹' . number_format((float) $row['subsidy']) : '—' ?></td>
          <td class="py-3 pr-4">
            <?php if ($row['image_path']): ?>
              <img src="/<?= e($row['image_path']) ?>" alt="" class="h-9 w-12 rounded object-cover">
            <?php else: ?>
              <span class="text-xs text-gray-400">None</span>
            <?php endif; ?>
          </td>
          <td class="py-3 pr-4">
            <span class="badge <?= $row['is_active'] ? 'bg-primary-50 text-primary-700' : 'bg-gray-100 text-gray-500' ?>">
              <?= $row['is_active'] ? 'Active' : 'Removed' ?>
            </span>
          </td>
          <td class="py-3 pr-4 space-x-2 whitespace-nowrap">
            <button onclick='openItemModal(<?= json_encode($kind) ?>, <?= json_encode($row) ?>)' class="text-primary-600 hover:underline text-xs">Edit</button>
            <?php if ($row['is_active']): ?>
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                <input type="hidden" name="kind" value="<?= e($kind) ?>">
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

<div id="product-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center px-4 z-50 overflow-y-auto py-8">
  <div class="card w-full max-w-lg">
    <h2 id="product-modal-title" class="font-semibold text-gray-900 mb-4">Add Product</h2>
    <form method="post" action="/admin/products.php" enctype="multipart/form-data" class="space-y-3">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="product-id" value="">
      <input type="hidden" name="kind" id="product-kind" value="product">

      <div>
        <label class="text-sm font-medium text-gray-700">Name *</label>
        <input type="text" name="name" id="product-name" required class="input mt-1">
      </div>

      <!-- Kit-only ------------------------------------------------------- -->
      <div class="kit-field grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-medium text-gray-700">System size (kW) *</label>
          <input type="number" name="system_kw" id="kit-kw" step="0.5" min="0.5" class="input mt-1">
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Kit type</label>
          <select name="kit_type" id="kit-type" class="input mt-1">
            <option value="ongrid">On-Grid</option>
            <option value="hybrid">Hybrid</option>
            <option value="offgrid">Off-Grid</option>
          </select>
        </div>
      </div>
      <div class="kit-field">
        <label class="text-sm font-medium text-gray-700">Suits (short line shown under the name)</label>
        <input type="text" name="suits" id="kit-suits" class="input mt-1" placeholder="3 BHK · bill ₹2,000–3,000">
      </div>
      <div class="kit-field">
        <label class="text-sm font-medium text-gray-700">Includes (one component per line)</label>
        <textarea name="includes" id="kit-includes" rows="6" class="input mt-1"></textarea>
      </div>
      <div class="kit-field grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-medium text-gray-700">Subsidy (₹)</label>
          <input type="number" name="subsidy" id="kit-subsidy" step="1" min="0" class="input mt-1" value="0">
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Generates (units/month)</label>
          <input type="number" name="monthly_units" id="kit-units" min="0" class="input mt-1">
        </div>
      </div>
      <label class="kit-field flex items-center gap-2 text-sm font-medium text-gray-700">
        <input type="checkbox" name="is_featured" id="kit-featured" value="1" class="rounded border-gray-300 text-primary-600">
        Featured on homepage
      </label>

      <!-- Component-only ------------------------------------------------- -->
      <div class="product-field">
        <label class="text-sm font-medium text-gray-700">Category *</label>
        <select name="category" id="product-category" class="input mt-1">
          <option value="panel">Panel</option>
          <option value="inverter">Inverter</option>
          <option value="battery">Battery</option>
        </select>
      </div>
      <div class="product-field">
        <label class="text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" id="product-description" rows="2" class="input mt-1"></textarea>
      </div>
      <div class="product-field">
        <label class="text-sm font-medium text-gray-700">Specs (pipe separated, e.g. 540W | 21% efficiency)</label>
        <input type="text" name="specs" id="product-specs" class="input mt-1">
      </div>
      <div class="product-field">
        <label class="text-sm font-medium text-gray-700">Brand</label>
        <input type="text" name="brand" id="product-brand" class="input mt-1">
      </div>

      <!-- Both ----------------------------------------------------------- -->
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-medium text-gray-700"><span id="price-label">Price (₹)</span></label>
          <input type="number" name="price" id="product-price" step="0.01" min="0" class="input mt-1">
        </div>
        <div class="product-field">
          <label class="text-sm font-medium text-gray-700">MRP (₹)</label>
          <input type="number" name="mrp" id="product-mrp" step="0.01" min="0" class="input mt-1">
        </div>
      </div>
      <div class="product-field grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-medium text-gray-700">Wattage (W)</label>
          <input type="number" name="wattage" id="product-wattage" min="0" class="input mt-1">
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Warranty (years)</label>
          <input type="number" name="warranty_years" id="product-warranty" min="0" max="50" class="input mt-1">
        </div>
      </div>
      <label class="product-field flex items-center gap-2 text-sm font-medium text-gray-700">
        <input type="checkbox" name="in_stock" id="product-stock" value="1" checked class="rounded border-gray-300 text-primary-600">
        In stock
      </label>
      <div>
        <label class="text-sm font-medium text-gray-700">Sort Order</label>
        <input type="number" name="sort_order" id="product-sort" class="input mt-1" value="0">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Image (jpg/png/webp, max 2MB)</label>
        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="input mt-1">
        <p id="image-hint" class="mt-1 text-xs text-gray-500">Leave empty to keep the current image.</p>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('product-modal').classList.add('hidden')" class="btn-outline flex-1">Cancel</button>
        <button type="submit" class="btn-primary flex-1">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
// One modal for both kinds. Kits and components share a name, price, sort order and
// image; everything else is shown or hidden by `kind`.
function openItemModal(kind, row) {
  var isKit = kind === 'kit';
  var set = function (id, value) { document.getElementById(id).value = value; };

  // A hidden field must never be `required` — the browser blocks submission on an
  // invalid control it cannot scroll into view, with no visible error.
  document.querySelectorAll('.kit-field').forEach(function (el) { el.classList.toggle('hidden', !isKit); });
  document.querySelectorAll('.product-field').forEach(function (el) { el.classList.toggle('hidden', isKit); });
  document.getElementById('kit-kw').required = isKit;
  document.getElementById('product-category').required = !isKit;
  document.getElementById('product-price').required = isKit;

  set('product-kind', kind);
  document.getElementById('price-label').textContent = isKit ? 'Price before subsidy (₹) *' : 'Price (₹)';
  document.getElementById('product-modal-title').textContent =
    (row ? 'Edit ' : 'Add ') + (isKit ? 'Kit' : 'Component');
  document.getElementById('image-hint').classList.toggle('hidden', !row);

  row = row || {};
  set('product-id', row.id || '');
  set('product-name', row.name || '');
  set('product-sort', row.sort_order || '0');
  set('product-price', row.price || '');

  // Kit fields
  set('kit-kw', row.system_kw || '');
  set('kit-type', row.kit_type || 'ongrid');
  set('kit-subsidy', row.subsidy || '0');
  set('kit-units', row.monthly_units || '');
  set('kit-suits', row.suits || '');
  set('kit-includes', row.includes || '');
  document.getElementById('kit-featured').checked = row.is_featured == 1;

  // Component fields
  set('product-category', row.category || 'panel');
  set('product-description', row.description || '');
  set('product-specs', row.specs || '');
  set('product-brand', row.brand || '');
  set('product-mrp', row.mrp || '');
  set('product-wattage', row.wattage || '');
  set('product-warranty', row.warranty_years || '');
  document.getElementById('product-stock').checked = row.in_stock === undefined || row.in_stock != 0;

  document.getElementById('product-modal').classList.remove('hidden');
}
</script>

<?php require __DIR__ . '/../components/admin-footer.php'; ?>
