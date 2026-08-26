<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/product-content.php';
require_once __DIR__ . '/config/solar-calc.php';
lead_handle(['source' => 'contact']);

// ?ids=1,2,3 — cap at four so the table stays readable, and ignore junk.
$ids = array_slice(
    array_values(array_unique(array_filter(
        array_map('intval', explode(',', (string) ($_GET['ids'] ?? ''))),
        fn($id) => $id > 0
    ))),
    0,
    4
);

$products = [];
if ($ids) {
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT * FROM products WHERE id IN ($in) AND is_active = 1");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
}

if (count($products) < 2) {
    // Nothing meaningful to compare — send them back to pick again.
    redirect('/products.php');
}

// Union of every spec label across the selected products, so a spec one product
// has and another lacks still gets a row (with a dash) rather than misaligning.
$specsByProduct = [];
$allLabels = [];
foreach ($products as $p) {
    $rows = [];
    foreach (product_specs($p['specs']) as [$label, $value]) {
        $rows[$label] = $value;
        $allLabels[$label] = true;
    }
    $specsByProduct[$p['id']] = $rows;
}
$allLabels = array_keys($allLabels);

$pageTitle = 'Compare Solar Products — Specs & Prices Side by Side | HVU Solar';
$metaDescription = 'Compare solar panels, inverters and batteries side by side — price, wattage, warranty and full specifications.';

$bannerTitle = 'Compare Products';
$bannerSubtitle = 'Specifications side by side, so the difference is obvious.';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="mx-auto container px-6 py-16">
  <a href="/products.php" class="inline-flex items-center gap-2 text-sm font-medium text-primary-700 hover:text-primary-600">
    <?= icon('arrow-left', 'h-4 w-4') ?> Back to all products
  </a>

  <!-- Wide tables must scroll inside their own container, never the page body. -->
  <div class="mt-6 overflow-x-auto">
    <table class="w-full min-w-[640px] border-collapse text-sm">
      <caption class="sr-only">Side-by-side comparison of selected solar products</caption>
      <thead>
        <tr>
          <th scope="col" class="w-40 border-b border-gray-200 p-4 text-left align-bottom text-xs font-semibold uppercase tracking-wide text-gray-500">
            Product
          </th>
          <?php foreach ($products as $p): ?>
            <th scope="col" class="border-b border-gray-200 p-4 text-left align-bottom">
              <div class="h-32 w-full overflow-hidden rounded-xl bg-gray-100">
                <img src="<?= $p['image_path'] ? '/' . e($p['image_path']) : 'https://placehold.co/300x200?text=' . urlencode($p['name']) ?>"
                     alt="<?= e($p['name']) ?>" loading="lazy" class="h-full w-full object-cover">
              </div>
              <?php if ($p['brand']): ?>
                <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-accent-600"><?= e($p['brand']) ?></p>
              <?php endif; ?>
              <a href="/product-details.php?id=<?= (int) $p['id'] ?>" class="mt-1 block font-bold text-gray-900 hover:text-primary-700">
                <?= e($p['name']) ?>
              </a>
            </th>
          <?php endforeach; ?>
        </tr>
      </thead>

      <tbody>
        <?php
        // Fixed attribute rows first — these are the ones buyers actually decide on.
        $rows = [
            ['Price', fn($p) => $p['price'] ? '<span class="text-lg font-extrabold text-gray-900">' . e(inr((int) $p['price'])) . '</span>' : '—'],
            ['MRP', fn($p) => $p['mrp'] ? '<span class="text-gray-400 line-through">' . e(inr((int) $p['mrp'])) . '</span>' : '—'],
            ['Category', fn($p) => e(ucfirst($p['category']))],
            ['Rating', fn($p) => $p['rating']
                ? '<span class="text-accent-500">' . str_repeat('★', (int) round($p['rating'])) . '</span> <span class="font-medium">' . e((string) $p['rating']) . '</span> <span class="text-gray-400">(' . (int) $p['review_count'] . ')</span>'
                : '—'],
            ['Rating (W)', fn($p) => $p['wattage'] ? number_format((int) $p['wattage']) . 'W' : '—'],
            ['Warranty', fn($p) => $p['warranty_years'] ? (int) $p['warranty_years'] . ' years' : '—'],
            ['Availability', fn($p) => $p['in_stock']
                ? '<span class="badge bg-primary-50 text-primary-700">In stock</span>'
                : '<span class="badge bg-gray-100 text-gray-600">Out of stock</span>'],
        ];
        foreach ($rows as $i => [$label, $render]): ?>
          <tr class="<?= $i % 2 ? 'bg-gray-50/60' : '' ?>">
            <th scope="row" class="p-4 text-left font-medium text-gray-600"><?= e($label) ?></th>
            <?php foreach ($products as $p): ?>
              <td class="p-4 text-gray-900"><?= $render($p) ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>

        <?php if ($allLabels): ?>
          <tr>
            <th scope="row" colspan="<?= count($products) + 1 ?>" class="bg-primary-50 p-4 text-left text-xs font-semibold uppercase tracking-wide text-primary-700">
              Specifications
            </th>
          </tr>
          <?php foreach ($allLabels as $i => $label): ?>
            <tr class="<?= $i % 2 ? 'bg-gray-50/60' : '' ?>">
              <th scope="row" class="p-4 text-left font-medium text-gray-600"><?= e($label) ?></th>
              <?php foreach ($products as $p): ?>
                <td class="p-4 text-gray-900"><?= e($specsByProduct[$p['id']][$label] ?? '—') ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>

        <tr>
          <td class="p-4"></td>
          <?php foreach ($products as $p): ?>
            <td class="p-4">
              <a href="/product-details.php?id=<?= (int) $p['id'] ?>#enquiry" class="btn-primary bg-accent-500 text-ink hover:bg-accent-400 text-sm">
                Enquire <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
              </a>
            </td>
          <?php endforeach; ?>
        </tr>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/components/consultation-cta.php'; ?>
<?php require __DIR__ . '/components/footer.php'; ?>
