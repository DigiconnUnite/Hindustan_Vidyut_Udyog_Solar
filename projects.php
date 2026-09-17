<?php
require_once __DIR__ . '/config/helpers.php';
lead_handle(['source' => 'contact']);

$projects = db()->query('SELECT * FROM projects WHERE is_active = 1 ORDER BY sort_order')->fetchAll();

$segments = ['residential' => 'Residential', 'commercial' => 'Commercial', 'industrial' => 'Industrial', 'institutional' => 'Institutional'];

$totalKw = array_sum(array_column($projects, 'system_kw'));

$pageTitle = 'Completed Solar Projects in Gurgaon & Delhi NCR | HVU Solar';
$metaDescription = 'Rooftop solar we have installed across Gurgaon, Delhi NCR and Faridabad — system sizes, locations and the monthly savings each one delivers.';

$bannerTitle = 'Our Projects';
$bannerSubtitle = 'Systems we have designed, installed and commissioned across Delhi NCR.';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="mx-auto container px-6 py-16">
  <!-- Headline numbers -->
  <!-- <div class="grid gap-6 sm:grid-cols-3 mb-14">
    <?php
    $stats = [
      [number_format($totalKw, 0) . ' kW', 'Installed capacity shown here', 'sun'],
      [setting('stat_systems_installed', '1200+'), 'Systems commissioned', 'package'],
      [setting('stat_years_experience', '10+'), 'Years in the field', 'calendar'],
    ];
    foreach ($stats as [$value, $label, $ico]): ?>
      <div class="card border border-gray-900 shadow-none text-center">
        <span class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary-50 text-primary-700"><?= icon($ico, 'h-6 w-6') ?></span>
        <p class="mt-4 text-3xl font-extrabold text-gray-900"><?= e($value) ?></p>
        <p class="mt-1 text-sm text-gray-600"><?= e($label) ?></p>
      </div>
    <?php endforeach; ?>
  </div> -->

  <!-- Segment filter -->
  <div class="project-filter-buttons flex flex-wrap gap-2 justify-center rounded-full bg-gray-100 border border-gray-900 p-1.5 w-fit mx-auto">
    <button
      class="proj-filter badge px-5 py-2 font-semibold transition-colors bg-accent-500 text-ink"
      data-segment="all">
      All
    </button>

    <?php foreach ($segments as $slug => $label): ?>
      <button
        class="proj-filter badge px-5 py-2 font-semibold transition-colors text-gray-600 hover:text-gray-900"
        data-segment="<?= e($slug) ?>">
        <?= e($label) ?>
      </button>
    <?php endforeach; ?>
  </div>


  <!-- Mobile Dropdown -->
  <div class="project-filter-dropdown">
    <select id="project-segment-select" class="project-filter-select">
      <option value="all">All</option>

      <?php foreach ($segments as $slug => $label): ?>
        <option value="<?= e($slug) ?>">
          <?= e($label) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="mt-10 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($projects as $p): ?>
      <article class="proj-card card group overflow-hidden p-3 flex flex-col border border-gray-900 shadow-none" data-segment="<?= e($p['segment']) ?>">
        <div class="relative h-52 rounded-2xl bg-gray-100 overflow-hidden">
          <img src="<?= $p['image_path'] ? '/' . e($p['image_path']) : 'https://placehold.co/600x400?text=' . urlencode($p['system_kw'] . ' kW') ?>"
            alt="<?= e($p['title']) ?>" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
          <span class="absolute top-3 left-3 badge bg-white/95 backdrop-blur text-primary-700 font-semibold shadow-sm">
            <?= e(rtrim(rtrim(number_format((float) $p['system_kw'], 1), '0'), '.')) ?> kW
          </span>
          <span class="absolute top-3 right-3 badge bg-ink/85 backdrop-blur text-white font-medium">
            <?= e($segments[$p['segment']] ?? $p['segment']) ?>
          </span>
        </div>

        <div class="p-4 pb-2 flex flex-col flex-1">
          <h3 class="font-bold text-gray-900 text-lg leading-snug"><?= e($p['title']) ?></h3>
          <p class="mt-1.5 inline-flex items-center gap-1.5 text-sm text-gray-500">
            <?= icon('map-pin', 'h-4 w-4') ?><?= e($p['location']) ?>
          </p>
          <p class="mt-3 text-sm text-gray-600 flex-1"><?= e($p['summary']) ?></p>

          <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4 text-sm">
            <?php if ($p['monthly_savings']): ?>
              <span class="text-gray-500">Saves <span class="font-semibold text-primary-700">₹<?= number_format((int) $p['monthly_savings']) ?></span>/mo</span>
            <?php else: ?>
              <span></span>
            <?php endif; ?>
            <?php if ($p['completed_on']): ?>
              <span class="text-xs text-gray-400"><?= e(date('M Y', strtotime($p['completed_on']))) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/components/consultation-cta.php'; ?>

<script>
  document.querySelectorAll('.proj-filter').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var seg = btn.dataset.segment;
      document.querySelectorAll('.proj-filter').forEach(function(b) {
        var on = b.dataset.segment === seg;
        b.classList.toggle('bg-accent-500', on);
        b.classList.toggle('text-ink', on);
        b.classList.toggle('text-gray-600', !on);
      });
      document.querySelectorAll('.proj-card').forEach(function(card) {
        card.style.display = (seg === 'all' || card.dataset.segment === seg) ? '' : 'none';
      });
    });
  });
</script>

<script>
  const projectSegmentSelect = document.getElementById('project-segment-select');

  if (projectSegmentSelect) {
    projectSegmentSelect.addEventListener('change', function() {
      const segment = this.value;

      const filterButton = document.querySelector(
        `.proj-filter[data-segment="${segment}"]`
      );

      if (filterButton) {
        filterButton.click();
      }
    });
  }
</script>

<?php require __DIR__ . '/components/closing-cta.php'; ?>

<?php require __DIR__ . '/components/footer.php'; ?>