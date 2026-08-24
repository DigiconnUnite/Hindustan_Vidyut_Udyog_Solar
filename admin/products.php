<?php
require_once __DIR__ . '/../config/auth.php';
$user = require_admin();
$pageTitle = 'Products';

$uploadDir = __DIR__ . '/../storage/uploads/products/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete') {
        db()->prepare('UPDATE products SET is_active = 0 WHERE id = ?')->execute([(int) $_POST['id']]);
        flash('success', 'Product removed.');
        redirect('/admin/products.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
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

    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed, true) && $_FILES['image']['size'] <= 2 * 1024 * 1024) {
            $filename = bin2hex(random_bytes(8)) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
            $imagePath = 'storage/uploads/products/' . $filename;
        } else {
            flash('error', 'Image must be jpg/png/webp under 2MB.');
            redirect('/admin/products.php');
        }
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

$products = db()->query('SELECT * FROM products ORDER BY is_active DESC, sort_order')->fetchAll();

require __DIR__ . '/../components/admin-header.php';
?>

<div class="flex items-center justify-end mb-6">
  <button onclick="openProductModal()" class="btn-primary text-sm">+ Add Product</button>
</div>

<div class="card overflow-x-auto">
  <table class="w-full text-sm">
    <thead>
      <tr class="text-left text-gray-500 border-b border-gray-100">
        <th class="py-2 pr-4">Name</th>
        <th class="py-2 pr-4">Category</th>
        <th class="py-2 pr-4">Price</th>
        <th class="py-2 pr-4">Status</th>
        <th class="py-2 pr-4">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $product): ?>
        <tr class="border-b border-gray-50">
          <td class="py-3 pr-4 font-medium text-gray-900"><?= e($product['name']) ?></td>
          <td class="py-3 pr-4 text-gray-600"><?= e(ucfirst($product['category'])) ?></td>
          <td class="py-3 pr-4 text-gray-600"><?= $product['price'] ? '₹' . number_format((float) $product['price']) : '—' ?></td>
          <td class="py-3 pr-4">
            <span class="badge <?= $product['is_active'] ? 'bg-primary-50 text-primary-700' : 'bg-gray-100 text-gray-500' ?>">
              <?= $product['is_active'] ? 'Active' : 'Removed' ?>
            </span>
          </td>
          <td class="py-3 pr-4 space-x-2 whitespace-nowrap">
            <button onclick='openProductModal(<?= json_encode($product) ?>)' class="text-primary-600 hover:underline text-xs">Edit</button>
            <?php if ($product['is_active']): ?>
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $product['id'] ?>">
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
  <div class="card w-full max-w-md">
    <h2 id="product-modal-title" class="font-semibold text-gray-900 mb-4">Add Product</h2>
    <form method="post" action="/admin/products.php" enctype="multipart/form-data" class="space-y-3">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="product-id" value="">
      <div>
        <label class="text-sm font-medium text-gray-700">Name *</label>
        <input type="text" name="name" id="product-name" required class="input mt-1">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Category *</label>
        <select name="category" id="product-category" class="input mt-1">
          <option value="panel">Panel</option>
          <option value="inverter">Inverter</option>
          <option value="battery">Battery</option>
        </select>
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" id="product-description" rows="2" class="input mt-1"></textarea>
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Specs</label>
        <input type="text" name="specs" id="product-specs" class="input mt-1">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Brand</label>
        <input type="text" name="brand" id="product-brand" class="input mt-1">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-medium text-gray-700">Price (₹)</label>
          <input type="number" name="price" id="product-price" step="0.01" min="0" class="input mt-1">
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">MRP (₹)</label>
          <input type="number" name="mrp" id="product-mrp" step="0.01" min="0" class="input mt-1">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-medium text-gray-700">Wattage (W)</label>
          <input type="number" name="wattage" id="product-wattage" min="0" class="input mt-1">
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Warranty (years)</label>
          <input type="number" name="warranty_years" id="product-warranty" min="0" max="50" class="input mt-1">
        </div>
      </div>
      <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
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
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('product-modal').classList.add('hidden')" class="btn-outline flex-1">Cancel</button>
        <button type="submit" class="btn-primary flex-1">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
function openProductModal(product) {
  document.getElementById('product-modal').classList.remove('hidden');
  const title = document.getElementById('product-modal-title');
  if (product) {
    title.textContent = 'Edit Product';
    document.getElementById('product-id').value = product.id;
    document.getElementById('product-name').value = product.name;
    document.getElementById('product-category').value = product.category;
    document.getElementById('product-description').value = product.description || '';
    document.getElementById('product-specs').value = product.specs || '';
    document.getElementById('product-sort').value = product.sort_order;
    document.getElementById('product-brand').value = product.brand || '';
    document.getElementById('product-price').value = product.price || '';
    document.getElementById('product-mrp').value = product.mrp || '';
    document.getElementById('product-wattage').value = product.wattage || '';
    document.getElementById('product-warranty').value = product.warranty_years || '';
    document.getElementById('product-stock').checked = product.in_stock != 0;
  } else {
    title.textContent = 'Add Product';
    document.getElementById('product-id').value = '';
    document.getElementById('product-name').value = '';
    document.getElementById('product-description').value = '';
    document.getElementById('product-specs').value = '';
    document.getElementById('product-sort').value = '0';
    document.getElementById('product-brand').value = '';
    document.getElementById('product-price').value = '';
    document.getElementById('product-mrp').value = '';
    document.getElementById('product-wattage').value = '';
    document.getElementById('product-warranty').value = '';
    document.getElementById('product-stock').checked = true;
  }
}
</script>

<?php require __DIR__ . '/../components/admin-footer.php'; ?>
