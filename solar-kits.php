<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/solar-calc.php';
consultation_handle();

$kits = db()->query('SELECT * FROM solar_kits WHERE is_active = 1 ORDER BY sort_order')->fetchAll();
$rate = (float) setting('finance_rate', '9.5');

$kitTypes = ['ongrid' => 'On-Grid', 'hybrid' => 'Hybrid', 'offgrid' => 'Off-Grid'];

$pageTitle = 'Solar Kits — Complete Rooftop Systems from 1 kW to 10 kW | HVU Solar';
$metaDescription = 'Ready-to-install rooftop solar kits with panels, inverter, structure, cabling and net-metering paperwork included. Transparent pricing with PM Surya Ghar subsidy applied.';

// Product schema per kit — these are the pages with real prices, so they are
// what deserves rich results.
$jsonLd = [[
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'itemListElement' => array_map(fn($i, $k) => [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'item' => [
            '@type' => 'Product',
            'name' => $k['name'],
            'description' => $k['suits'],
            'brand' => ['@type' => 'Brand', 'name' => 'Hindustan Vidyut Udyog Solar'],
            'offers' => [
                '@type' => 'Offer',
                'price' => (string) (int) ($k['price'] - $k['subsidy']),
                'priceCurrency' => 'INR',
                'availability' => 'https://schema.org/InStock',
                'url' => APP_URL . '/solar-kits.php#' . $k['slug'],
            ],
        ],
    ], array_keys($kits), $kits),
]];

$bannerTitle = 'Solar Kits';
$bannerSubtitle = 'Everything you need in one package — panels, inverter, structure and paperwork.';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="mx-auto container px-6 py-16">
  <div class="mx-auto max-w-2xl text-center">
    <h2 class="text-3xl font-bold text-gray-900">Pick a kit by your monthly bill</h2>
    <p class="mt-3 text-gray-600">
      Every kit is a complete, installed system — not a box of parts. Prices below already have the
      PM Surya Ghar subsidy deducted where the scheme applies.
    </p>
    <a href="/solar-calculator.php" class="btn-outline mt-6">
      Not sure which size? Use the calculator
      <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
    </a>
  </div>

  <!-- Type filter -->
  <div class="mt-12 flex flex-wrap gap-2 justify-center rounded-full bg-gray-100 border border-gray-900 p-1.5 w-fit mx-auto" id="kit-filters">
    <button class="kit-filter badge px-5 py-2 font-semibold transition-colors bg-accent-500 text-ink" data-type="all">All</button>
    <?php foreach ($kitTypes as $slug => $label): ?>
      <button class="kit-filter badge px-5 py-2 font-semibold transition-colors text-gray-600 hover:text-gray-900" data-type="<?= e($slug) ?>">
        <?= e($label) ?>
      </button>
    <?php endforeach; ?>
  </div>

  <div class="mt-10 grid gap-8 md:grid-cols-2 lg:grid-cols-3" id="kit-grid">
    <?php foreach ($kits as $kit):
        $net = (int) ($kit['price'] - $kit['subsidy']);
        $emi = solar_emi($net, $rate, 60);
        // Payback on the units this kit actually generates, valued at the default tariff.
        $kitYears = solar_payback_years($net, (int) $kit['monthly_units'] * 12 * (float) setting('default_tariff', '8.0'));
        $kitPayback = $kitYears === null ? '—' : $kitYears . ' yrs';
    ?>
      <article id="<?= e($kit['slug']) ?>" class="kit-card card group flex flex-col overflow-hidden p-3 border border-gray-900 shadow-none relative <?= $kit['is_featured'] ? 'ring-2 ring-accent-500' : '' ?>" data-type="<?= e($kit['kit_type']) ?>">
        <!-- Same image treatment as the product cards, so the two grids read as one system. -->
        <div class="relative h-52 rounded-2xl bg-gray-100 overflow-hidden">
          <img src="<?= $kit['image_path'] ? '/' . e($kit['image_path']) : 'https://placehold.co/600x400?text=' . urlencode(rtrim(rtrim(number_format((float) $kit['system_kw'], 1), '0'), '.') . ' kW Kit') ?>"
               alt="<?= e($kit['name']) ?>" loading="lazy"
               class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
          <span class="absolute top-3 left-3 badge bg-white/95 backdrop-blur font-semibold text-primary-700 shadow-sm">
            <?= e(rtrim(rtrim(number_format((float) $kit['system_kw'], 1), '0'), '.')) ?> kW
          </span>
          <span class="absolute top-3 right-3 badge bg-ink/85 backdrop-blur font-medium text-white">
            <?= e($kitTypes[$kit['kit_type']] ?? $kit['kit_type']) ?>
          </span>
          <?php if ($kit['is_featured']): ?>
            <span class="absolute bottom-3 left-3 badge bg-accent-500 font-bold text-ink shadow-sm">Most popular</span>
          <?php endif; ?>
        </div>

        <div class="p-4 pb-2 flex flex-col flex-1">
          <h3 class="text-xl font-bold text-gray-900 group-hover:text-primary-700 transition-colors"><?= e($kit['name']) ?></h3>
          <p class="mt-1 text-sm text-gray-500"><?= e($kit['suits']) ?></p>

          <div class="mt-5 border-y border-gray-100 py-5">
            <p class="text-3xl font-extrabold text-gray-900"><?= e(inr($net)) ?></p>
            <?php if ($kit['subsidy'] > 0): ?>
              <p class="mt-1 text-sm text-gray-500">
                <span class="line-through"><?= e(inr((int) $kit['price'])) ?></span>
                <span class="ml-1.5 font-semibold text-primary-700">after <?= e(inr((int) $kit['subsidy'])) ?> subsidy</span>
              </p>
            <?php else: ?>
              <p class="mt-1 text-sm text-gray-500">Subsidy not applicable to commercial systems</p>
            <?php endif; ?>
            <p class="mt-2 text-sm text-gray-600">or <span class="font-semibold text-gray-900"><?= e(inr($emi)) ?>/month</span> for 60 months</p>
          </div>

          <dl class="mt-5 grid grid-cols-2 gap-4 text-sm">
            <div>
              <dt class="text-xs uppercase tracking-wide text-gray-500">Generates</dt>
              <dd class="font-semibold text-gray-900">~<?= number_format((int) $kit['monthly_units']) ?> units/mo</dd>
            </div>
            <div>
              <dt class="text-xs uppercase tracking-wide text-gray-500">Pays back in</dt>
              <dd class="font-semibold text-gray-900"><?= e($kitPayback) ?></dd>
            </div>
          </dl>

        <h4 class="mt-6 text-sm font-semibold text-gray-900">What's included</h4>
        <ul class="mt-3 space-y-2 text-sm text-gray-600 flex-1">
          <?php foreach (array_filter(array_map('trim', explode("\n", (string) $kit['includes']))) as $item): ?>
            <li class="flex items-start gap-2">
              <span class="mt-1 text-primary-600"><?= icon('shield', 'h-3.5 w-3.5') ?></span>
              <span><?= e($item) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>

        <a href="/contact.php?kit=<?= e($kit['slug']) ?>" class="btn-primary mt-6 w-full justify-between bg-accent-500 text-ink hover:bg-accent-400">
          Get this kit quoted
          <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
        </a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <p class="mt-10 text-center text-sm text-gray-500">
    Prices are indicative for standard Delhi-NCR rooftops and include installation.
    Final pricing is confirmed after a free site survey.
  </p>
</section>

<?php require __DIR__ . '/components/consultation-cta.php'; ?>

<script>
document.querySelectorAll('.kit-filter').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var type = btn.dataset.type;
    document.querySelectorAll('.kit-filter').forEach(function (b) {
      var on = b.dataset.type === type;
      b.classList.toggle('bg-accent-500', on);
      b.classList.toggle('text-ink', on);
      b.classList.toggle('text-gray-600', !on);
    });
    document.querySelectorAll('.kit-card').forEach(function (card) {
      card.style.display = (type === 'all' || card.dataset.type === type) ? '' : 'none';
    });
  });
});
</script>

<?php require __DIR__ . '/components/footer.php'; ?>
