<?php
require_once __DIR__ . '/config/helpers.php';
lead_handle(['source' => 'contact']);

$certs = db()->query('SELECT * FROM certifications ORDER BY sort_order')->fetchAll();

$pageTitle = 'Certifications & Approvals — ALMM, MNRE, BIS | HVU Solar';
$metaDescription = 'ALMM-listed modules, MNRE registration, BIS-certified components and DISCOM empanelment — the approvals that make our installations subsidy-eligible.';

$bannerTitle = 'Certifications';
$bannerSubtitle = 'The approvals behind every system we install.';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="mx-auto container px-6 py-16">
  <div class="mx-auto max-w-2xl text-center">
    <h2 class="text-3xl font-bold text-gray-900">Why certification matters</h2>
    <p class="mt-3 text-gray-600 leading-relaxed">
      PM Surya Ghar subsidy is only paid on systems built from ALMM-listed modules and installed by a
      registered vendor. Uncertified hardware is cheaper up front and disqualifies your subsidy claim
      entirely — which is why we do not sell it.
    </p>
  </div>

  <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($certs as $c): ?>
      <div class="card border border-gray-900 shadow-none flex flex-col">
        <?php if ($c['logo_path'] && is_file(__DIR__ . '/' . $c['logo_path'])): ?>
          <img src="/<?= e($c['logo_path']) ?>" alt="<?= e($c['name']) ?>" loading="lazy" class="h-12 w-auto object-contain self-start">
        <?php else: ?>
          <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary-50 text-primary-700"><?= icon('shield', 'h-6 w-6') ?></span>
        <?php endif; ?>
        <h3 class="mt-4 text-lg font-bold text-gray-900"><?= e($c['name']) ?></h3>
        <?php if ($c['issuer']): ?>
          <p class="mt-0.5 text-xs font-medium uppercase tracking-wide text-accent-600"><?= e($c['issuer']) ?></p>
        <?php endif; ?>
        <p class="mt-3 text-sm leading-relaxed text-gray-600 flex-1"><?= e($c['description']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Warranty summary -->
  <div class="mt-16 card border border-gray-900 shadow-none">
    <h2 class="text-2xl font-bold text-gray-900">What we warrant</h2>
    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      <?php
      $warranties = [
          ['25 years', 'Module performance', 'Panels hold at least 80% of rated output at year 25, per the manufacturer.'],
          ['12 years', 'Module product', 'Manufacturing defects in the panels themselves.'],
          ['5–10 years', 'Inverter', 'Depends on the model; extendable on most units.'],
          ['5 years', 'Workmanship', 'Our own labour — mounting, wiring and waterproofing.'],
      ];
      foreach ($warranties as [$term, $title, $body]): ?>
        <div class="rounded-2xl bg-primary-50 p-5">
          <p class="text-2xl font-extrabold text-primary-700"><?= e($term) ?></p>
          <p class="mt-1 font-semibold text-gray-900"><?= e($title) ?></p>
          <p class="mt-2 text-sm text-gray-600"><?= e($body) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/components/consultation-cta.php'; ?>
<?php require __DIR__ . '/components/footer.php'; ?>
