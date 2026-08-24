<?php
require_once __DIR__ . '/config/helpers.php';
consultation_handle();
$pageTitle = 'About Us — 10+ Years of Solar Installation | HVU Solar';
$metaDescription = 'Hindustan Vidyut Udyog Solar has installed 1200+ rooftop systems across Delhi NCR. MNRE-registered, ALMM-compliant, and backed by a 5-year workmanship warranty.';
$bannerTitle = 'About Us';
$bannerSubtitle = 'A decade of powering homes with clean, reliable solar energy.';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="mx-auto container px-6 py-16 grid gap-10 md:grid-cols-2 items-center">
  <img src="https://placehold.co/600x288?text=Our+Story" alt="Our Story" class="h-72 w-full rounded-2xl object-cover bg-gray-100">
  <div>
    <h2 class="text-2xl font-bold text-gray-900">Our Story</h2>
    <p class="mt-4 text-gray-600">
      Hindustan Vidyut Udyog Solar started with a simple goal: make residential
      solar installation simple, transparent and reliable. Since then we have
      installed systems across hundreds of homes, backed by certified
      technicians and dependable after-sales support.
    </p>
  </div>
</section>

<section class="bg-gray-50 py-16">
  <div class="mx-auto container px-6">
    <h2 class="text-2xl font-bold text-gray-900 text-center mb-10">Our Values</h2>
    <div class="grid gap-6 md:grid-cols-4">
      <?php foreach ([
        ['Quality', 'Certified equipment and installation practices on every job.'],
        ['Transparency', 'Clear pricing and honest timelines, no surprises.'],
        ['Reliability', 'On-time installation and responsive support.'],
        ['Sustainability', 'Helping every home reduce its carbon footprint.'],
      ] as [$title, $desc]): ?>
        <div class="card text-center">
          <h3 class="font-semibold text-gray-900"><?= e($title) ?></h3>
          <p class="mt-2 text-sm text-gray-600"><?= e($desc) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/components/consultation-cta.php'; ?>

<?php require __DIR__ . '/components/footer.php'; ?>
