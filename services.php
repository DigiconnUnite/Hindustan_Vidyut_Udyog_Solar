<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/service-content.php';
$pageTitle = 'Solar Services — Survey, Installation, AMC & Upgrades | HVU Solar';
$metaDescription = 'Free site survey, turnkey rooftop installation, annual maintenance contracts and system upgrades. Certified technicians across Gurgaon and Delhi NCR.';

$services = db()->query('SELECT * FROM services ORDER BY sort_order')->fetchAll();

// Service images keyed by the `icon` column; falls back to a placeholder.
$serviceImages = [
  'clipboard' => '/assets/images/service/Solar_Site_Survey.png',
  'wrench'    => '/assets/images/service/Solar_Installation.png',
  'shield'    => '/assets/images/service/Solar_Maintenance_AMC.png',
  'arrow-up'  => '/assets/images/service/Solar_System_Upgrade.png',
];

$bannerTitle = 'Our Services';
$bannerSubtitle = 'From first survey to lifetime support, we handle every step of your solar journey.';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="mx-auto container px-6 py-16 space-y-10">
  <?php foreach ($services as $i => $service): ?>
    <div class="grid gap-8 md:grid-cols-5 items-center <?= $i % 2 === 1 ? 'md:[&>*:first-child]:order-2' : '' ?>">
      <?php $img = $serviceImages[$service['icon']] ?? 'https://placehold.co/600x450?text=' . urlencode($service['title']); ?>
      <img src="<?= e($img) ?>" alt="<?= e($service['title']) ?>" loading="lazy" class="md:col-span-2 aspect-[4/3] w-full rounded-2xl object-cover bg-gray-100">
      <div class="md:col-span-3">
        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight"><?= e($service['title']) ?></h2>
        <p class="mt-4 text-lg text-gray-700"><?= e($service['description']) ?></p>
        <?php if ($extra = service_content($service['icon'])): ?>
          <p class="mt-4 text-gray-600"><?= e($extra['body']) ?></p>
          <ul class="mt-6 space-y-2.5">
            <?php foreach ($extra['points'] as $point): ?>
              <li class="flex gap-3 text-gray-700">
                <span class="mt-0.5 shrink-0 text-primary-600"><?= icon('check', 'h-5 w-5') ?></span>
                <span><?= e($point) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</section>

<?php require __DIR__ . '/components/closing-cta.php'; ?>

<?php require __DIR__ . '/components/footer.php'; ?>
