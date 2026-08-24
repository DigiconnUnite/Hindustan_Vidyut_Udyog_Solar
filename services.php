<?php
require_once __DIR__ . '/config/helpers.php';
consultation_handle();
$pageTitle = 'Solar Services — Survey, Installation, AMC & Upgrades | HVU Solar';
$metaDescription = 'Free site survey, turnkey rooftop installation, annual maintenance contracts and system upgrades. Certified technicians across Gurgaon and Delhi NCR.';

$services = db()->query('SELECT * FROM services ORDER BY sort_order')->fetchAll();

$bannerTitle = 'Our Services';
$bannerSubtitle = 'From first survey to lifetime support, we handle every step of your solar journey.';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="mx-auto container px-6 py-16 space-y-10">
  <?php foreach ($services as $i => $service): ?>
    <div class="grid gap-8 md:grid-cols-2 items-center <?= $i % 2 === 1 ? 'md:[&>*:first-child]:order-2' : '' ?>">
      <img src="https://placehold.co/600x224?text=<?= urlencode($service['title']) ?>" alt="<?= e($service['title']) ?>" class="h-56 w-full rounded-2xl object-cover bg-gray-100">
      <div>
        <h2 class="text-2xl font-bold text-gray-900"><?= e($service['title']) ?></h2>
        <p class="mt-3 text-gray-600"><?= e($service['description']) ?></p>
      </div>
    </div>
  <?php endforeach; ?>
</section>

<?php require __DIR__ . '/components/consultation-cta.php'; ?>

<?php require __DIR__ . '/components/footer.php'; ?>
