<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/solar-calc.php';
require_once __DIR__ . '/config/product-content.php';

// Kits live in their own table but are browsed as products — normalise them into the
// product row shape and they render through the same card, filters and sort.
$kits = array_map('kit_as_product', db()->query('SELECT * FROM solar_kits WHERE is_active = 1 ORDER BY sort_order')->fetchAll());
$catalogue = db()->query('SELECT * FROM products WHERE is_active = 1 ORDER BY sort_order')->fetchAll();

// Kits lead: a complete system is what most homeowners are actually shopping for.
$products = array_merge($kits, $catalogue);
$categories = array_values(array_unique(array_column($products, 'category')));
$brands = array_values(array_filter(array_unique(array_column($products, 'brand'))));

$rate = (float) setting('finance_rate', '9.5');
$tariff = (float) setting('default_tariff', '8.0');
$kitTypes = ['ongrid' => 'On-Grid', 'hybrid' => 'Hybrid', 'offgrid' => 'Off-Grid'];
// 'Kit' reads oddly next to Panel/Inverter/Battery; the data-category stays 'kit'.
$categoryLabels = ['kit' => 'Complete Kits'];

$pageTitle = 'Solar Kits, Panels, Inverters & Batteries — Prices & Specs | HVU Solar';
$metaDescription = 'Complete rooftop solar kits with the PM Surya Ghar subsidy applied, plus panels, inverters and battery storage with prices, wattage and specifications. Installed across Delhi NCR.';

$jsonLd = [[
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'itemListElement' => array_map(fn($i, $p) => [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'item' => array_filter([
            '@type' => 'Product',
            'name' => $p['name'],
            'description' => $p['description'],
            'brand' => $p['brand'] ? ['@type' => 'Brand', 'name' => $p['brand']] : null,
            'offers' => $p['price'] ? [
                '@type' => 'Offer',
                'price' => (string) (int) $p['price'],
                'priceCurrency' => 'INR',
                'availability' => $p['in_stock'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'url' => APP_URL . product_url($p),
            ] : null,
            'aggregateRating' => $p['rating'] && $p['review_count'] ? [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) $p['rating'],
                'reviewCount' => (string) $p['review_count'],
            ] : null,
        ]),
    ], array_keys($products), $products),
]];

$bannerTitle = 'Products';
$bannerSubtitle = 'Complete solar kits, panels, inverters and battery storage for every home.';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="mx-auto container px-6 py-16">

  <!-- Controls -->
  <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
    <div class="flex flex-wrap gap-2 rounded-full bg-gray-100 border border-gray-900 p-1.5 w-fit" id="category-filters">
      <button class="filter-btn badge px-5 py-2 font-semibold transition-colors bg-accent-500 text-ink" data-category="all">All</button>
      <?php foreach ($categories as $category): ?>
        <button class="filter-btn badge px-5 py-2 font-semibold transition-colors text-gray-600 hover:text-gray-900" data-category="<?= e($category) ?>">
          <?= e($categoryLabels[$category] ?? ucfirst($category)) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <div class="flex flex-wrap items-center gap-3">
      <?php if ($kits): ?>
        <?php // Only meaningful while kits are on screen; the JS shows/hides it with the category. ?>
        <label for="type-filter" class="sr-only">Kit type</label>
        <select id="type-filter" class="input w-auto py-2 hidden">
          <option value="all">All kit types</option>
          <?php foreach ($kitTypes as $slug => $label): ?>
            <option value="<?= e($slug) ?>"><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>

      <?php if ($brands): ?>
        <label for="brand-filter" class="sr-only">Brand</label>
        <select id="brand-filter" class="input w-auto py-2">
          <option value="all">All brands</option>
          <?php foreach ($brands as $brand): ?>
            <option value="<?= e($brand) ?>"><?= e($brand) ?></option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>

      <label for="sort-by" class="sr-only">Sort by</label>
      <select id="sort-by" class="input w-auto py-2">
        <option value="default">Sort: Featured</option>
        <option value="price-asc">Price: low to high</option>
        <option value="price-desc">Price: high to low</option>
        <option value="rating">Highest rated</option>
        <option value="name">Name A–Z</option>
      </select>
    </div>
  </div>

  <p id="result-count" class="mt-5 text-sm text-gray-500"><?= count($products) ?> products</p>

  <div class="mt-6 grid gap-8 md:grid-cols-2 lg:grid-cols-3" id="product-grid">
    <?php foreach ($products as $product):
        $kit = $product['_kit'] ?? null;
        $detailUrl = product_url($product);
        $discount = ($product['mrp'] && $product['price'] && $product['mrp'] > $product['price'])
            ? (int) round(100 - ($product['price'] / $product['mrp'] * 100))
            : 0;
        $kw = $kit ? rtrim(rtrim(number_format((float) $kit['system_kw'], 1), '0'), '.') : '';
        $kitEmi = $kit ? solar_emi((int) $product['price'], $rate, 60) : 0;
        $kitYears = $kit ? solar_payback_years((int) $product['price'], (int) $kit['monthly_units'] * 12 * $tariff) : null;
    ?>
      <article class="product-card card group overflow-hidden p-3 flex flex-col border border-gray-900 shadow-none hover:bg-primary-50 transition-colors <?= $kit && $kit['is_featured'] ? 'ring-2 ring-accent-500' : '' ?>"
               data-category="<?= e($product['category']) ?>"
               data-brand="<?= e((string) $product['brand']) ?>"
               data-price="<?= (int) $product['price'] ?>"
               data-rating="<?= e((string) ($product['rating'] ?? 0)) ?>"
               data-type="<?= e($kit ? $kit['kit_type'] : '') ?>"
               data-name="<?= e($product['name']) ?>">
        <a href="<?= e($detailUrl) ?>" class="relative h-52 rounded-2xl bg-gray-100 overflow-hidden block">
          <img src="<?= $product['image_path'] ? '/' . e($product['image_path']) : 'https://placehold.co/400x160?text=' . urlencode($kit ? $kw . ' kW Kit' : $product['name']) ?>"
               loading="lazy" class="h-full w-full object-cover" alt="<?= e($product['name']) ?>">
          <span class="absolute top-3 left-3 badge bg-white/95 backdrop-blur font-semibold text-primary-700 shadow-sm">
            <?= e($kit ? $kw . ' kW' : ucfirst($product['category'])) ?>
          </span>
          <?php if ($kit): ?>
            <span class="absolute top-3 right-3 badge bg-ink/85 backdrop-blur font-medium text-white">
              <?= e($kitTypes[$kit['kit_type']] ?? $kit['kit_type']) ?>
            </span>
          <?php elseif ($discount > 0): ?>
            <span class="absolute top-3 right-3 badge bg-accent-500 font-bold text-ink shadow-sm"><?= $discount ?>% off</span>
          <?php endif; ?>
          <?php if ($kit && $kit['is_featured']): ?>
            <span class="absolute bottom-3 left-3 badge bg-accent-500 font-bold text-ink shadow-sm">Most popular</span>
          <?php elseif (!$kit && !$product['in_stock']): ?>
            <span class="absolute bottom-3 left-3 badge bg-ink/85 backdrop-blur font-medium text-white">Out of stock</span>
          <?php endif; ?>
        </a>

        <div class="p-4 pb-2 flex flex-col flex-1">
          <?php if ($product['brand']): ?>
            <p class="text-xs font-semibold uppercase tracking-wide text-accent-600"><?= e($product['brand']) ?></p>
          <?php endif; ?>

          <h3 class="mt-1 font-bold text-gray-900 text-lg leading-snug group-hover:text-primary-700 transition-colors">
            <a href="<?= e($detailUrl) ?>"><?= e($product['name']) ?></a>
          </h3>

          <?php if ($product['rating']): ?>
            <p class="mt-1.5 flex items-center gap-1.5 text-sm">
              <span class="text-accent-500" aria-hidden="true"><?= str_repeat('★', (int) round($product['rating'])) ?></span>
              <span class="font-medium text-gray-700"><?= e((string) $product['rating']) ?></span>
              <span class="text-gray-400">(<?= (int) $product['review_count'] ?>)</span>
            </p>
          <?php endif; ?>

          <p class="mt-2 text-sm text-gray-600 flex-1"><?= e($product['description']) ?></p>

          <?php if ($product['price']): ?>
            <p class="mt-4 flex items-baseline gap-2">
              <span class="text-2xl font-extrabold text-gray-900"><?= e(inr((int) $product['price'])) ?></span>
              <?php if ($discount > 0): ?>
                <span class="text-sm text-gray-400 line-through"><?= e(inr((int) $product['mrp'])) ?></span>
              <?php endif; ?>
            </p>
            <?php if ($kit): ?>
              <p class="mt-1 text-xs text-gray-500">
                <?php if ($kit['subsidy'] > 0): ?>
                  after <?= e(inr((int) $kit['subsidy'])) ?> subsidy ·
                <?php else: ?>
                  Subsidy not applicable ·
                <?php endif; ?>
                <span class="font-semibold text-gray-900"><?= e(inr($kitEmi)) ?>/mo</span> for 60 months
              </p>
            <?php endif; ?>
          <?php endif; ?>

          <!-- Key specs at a glance -->
          <dl class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-xs">
            <?php if ($kit): ?>
              <div><dt class="text-gray-500">Generates</dt><dd class="font-semibold text-gray-900">~<?= number_format((int) $kit['monthly_units']) ?> units/mo</dd></div>
              <div><dt class="text-gray-500">Pays back in</dt><dd class="font-semibold text-gray-900"><?= $kitYears === null ? '—' : e($kitYears . ' yrs') ?></dd></div>
            <?php else: ?>
              <?php if ($product['wattage']): ?>
                <div><dt class="text-gray-500">Rating</dt><dd class="font-semibold text-gray-900"><?= number_format((int) $product['wattage']) ?>W</dd></div>
              <?php endif; ?>
              <?php if ($product['warranty_years']): ?>
                <div><dt class="text-gray-500">Warranty</dt><dd class="font-semibold text-gray-900"><?= (int) $product['warranty_years'] ?> years</dd></div>
              <?php endif; ?>
            <?php endif; ?>
          </dl>

          <div class="mt-5 flex items-center justify-between gap-3 border-t border-gray-100 pt-4">
            <?php // compare.php looks rows up by integer product id, so kits sit this one out. ?>
            <?php if (!$kit): ?>
              <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-medium text-gray-600">
                <input type="checkbox" class="compare-box rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                       value="<?= (int) $product['id'] ?>" data-name="<?= e($product['name']) ?>">
                Compare
              </label>
            <?php else: ?>
              <span class="text-xs font-medium text-gray-500">Complete system</span>
            <?php endif; ?>
            <a href="<?= e($detailUrl) ?>" class="btn-outline text-sm py-1">
              Details <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
            </a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <p id="empty-state" class="hidden py-16 text-center text-gray-500">
    No products match those filters. <button type="button" id="reset-filters" class="font-semibold text-primary-700 hover:text-primary-600">Clear them</button>.
  </p>
</section>

<!-- Compare tray: only appears once something is ticked -->
<div id="compare-tray" class="fixed inset-x-0 bottom-0 z-40 hidden border-t border-gray-200 bg-white shadow-[0_-4px_20px_rgba(0,0,0,0.08)]">
  <div class="mx-auto container flex flex-wrap items-center gap-4 px-6 py-4">
    <span class="text-sm font-semibold text-gray-900">Comparing</span>
    <ul id="compare-list" class="flex flex-1 flex-wrap gap-2 text-sm"></ul>
    <a id="compare-go" href="#" class="btn-primary bg-accent-500 text-ink hover:bg-accent-400 text-sm">
      Compare <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
    </a>
    <button type="button" id="compare-clear" class="text-sm font-medium text-gray-500 hover:text-gray-900">Clear</button>
  </div>
</div>

<script>
(function () {
  var grid = document.getElementById('product-grid');
  var cards = Array.prototype.slice.call(grid.querySelectorAll('.product-card'));
  var brandSel = document.getElementById('brand-filter');
  var typeSel = document.getElementById('type-filter');
  var sortSel = document.getElementById('sort-by');
  var count = document.getElementById('result-count');
  var empty = document.getElementById('empty-state');
  var category = 'all';

  function apply() {
    var brand = brandSel ? brandSel.value : 'all';
    // Kit type only narrows kits, and only while the kit category is in view.
    var showTypes = category === 'kit';
    if (typeSel) {
      typeSel.classList.toggle('hidden', !showTypes);
      if (!showTypes) typeSel.value = 'all';
    }
    var type = (typeSel && showTypes) ? typeSel.value : 'all';
    var visible = 0;

    cards.forEach(function (card) {
      var ok = (category === 'all' || card.dataset.category === category)
            && (brand === 'all' || card.dataset.brand === brand)
            && (type === 'all' || card.dataset.type === type);
      card.style.display = ok ? '' : 'none';
      if (ok) visible++;
    });

    var noun = category === 'kit' ? ' kit' : ' product';
    count.textContent = visible + noun + (visible === 1 ? '' : 's');
    empty.classList.toggle('hidden', visible > 0);

    sort();
  }

  function sort() {
    var mode = sortSel.value;
    if (mode === 'default') return;

    var sorted = cards.slice().sort(function (a, b) {
      switch (mode) {
        case 'price-asc':  return (+a.dataset.price || Infinity) - (+b.dataset.price || Infinity);
        case 'price-desc': return (+b.dataset.price || 0) - (+a.dataset.price || 0);
        case 'rating':     return (+b.dataset.rating || 0) - (+a.dataset.rating || 0);
        case 'name':       return a.dataset.name.localeCompare(b.dataset.name);
        default:           return 0;
      }
    });
    sorted.forEach(function (card) { grid.appendChild(card); });
  }

  document.querySelectorAll('.filter-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      category = btn.dataset.category;
      document.querySelectorAll('.filter-btn').forEach(function (b) {
        var on = b.dataset.category === category;
        b.classList.toggle('bg-accent-500', on);
        b.classList.toggle('text-ink', on);
        b.classList.toggle('text-gray-600', !on);
      });
      apply();
    });
  });

  if (brandSel) brandSel.addEventListener('change', apply);
  if (typeSel) typeSel.addEventListener('change', apply);
  sortSel.addEventListener('change', apply);

  document.getElementById('reset-filters').addEventListener('click', function () {
    category = 'all';
    if (brandSel) brandSel.value = 'all';
    if (typeSel) typeSel.value = 'all';
    sortSel.value = 'default';
    document.querySelector('.filter-btn[data-category="all"]').click();
  });

  // --- compare tray -------------------------------------------------------
  var tray = document.getElementById('compare-tray');
  var list = document.getElementById('compare-list');
  var go = document.getElementById('compare-go');

  function refreshTray() {
    var picked = Array.prototype.slice.call(document.querySelectorAll('.compare-box:checked'));
    tray.classList.toggle('hidden', picked.length === 0);
    list.innerHTML = '';
    picked.forEach(function (box) {
      var li = document.createElement('li');
      li.className = 'badge bg-primary-50 text-primary-700';
      li.textContent = box.dataset.name;
      list.appendChild(li);
    });
    go.href = '/compare.php?ids=' + picked.map(function (b) { return b.value; }).join(',');
    // A comparison of one is just the product page — keep the button honest.
    go.classList.toggle('pointer-events-none', picked.length < 2);
    go.classList.toggle('opacity-50', picked.length < 2);
  }

  document.querySelectorAll('.compare-box').forEach(function (box) {
    box.addEventListener('change', function () {
      // Four columns is the most that stays readable on a laptop.
      if (document.querySelectorAll('.compare-box:checked').length > 4) {
        box.checked = false;
        return;
      }
      refreshTray();
    });
  });

  document.getElementById('compare-clear').addEventListener('click', function () {
    document.querySelectorAll('.compare-box:checked').forEach(function (b) { b.checked = false; });
    refreshTray();
  });

  // Deep link: /products.php?category=kit&type=hybrid — unknown values fall back to "all".
  var params = new URLSearchParams(location.search);
  var wanted = params.get('category');
  var wantedType = params.get('type');
  var target = wanted && document.querySelector('.filter-btn[data-category="' + CSS.escape(wanted) + '"]');
  // Set the type before the click, since clicking runs apply() immediately.
  if (typeSel && wantedType && typeSel.querySelector('option[value="' + CSS.escape(wantedType) + '"]')) {
    typeSel.value = wantedType;
  }
  if (target) target.click(); else apply();
})();
</script>

<?php require __DIR__ . '/components/footer.php'; ?>
