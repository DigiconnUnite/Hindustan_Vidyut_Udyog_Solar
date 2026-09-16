<?php
require_once __DIR__ . '/config/helpers.php';
lead_handle(['source' => 'contact']);
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

<section class="bg-gray-50 py-16 sm:py-20">
  <div class="container mx-auto px-5 sm:px-6">

    <!-- Section Heading -->
    <div class="mx-auto mb-10 max-w-2xl text-center sm:mb-12">
      <span class="mb-3 inline-block text-xs font-semibold uppercase tracking-[0.18em] text-[#ff7a32]">
        What We Stand For
      </span>

      <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
        Our Values
      </h2>

      <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-gray-600 sm:text-base">
        The principles that guide how we work, deliver, and build lasting relationships with our customers.
      </p>
    </div>

    <!-- Values -->
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <?php foreach (
        [
          ['01', 'Quality', 'Certified equipment and installation practices on every job.'],
          ['02', 'Transparency', 'Clear pricing and honest timelines, no surprises.'],
          ['03', 'Reliability', 'On-time installation and responsive support.'],
          ['04', 'Sustainability', 'Helping every home reduce its carbon footprint.'],
        ] as [$number, $title, $desc]
      ): ?>

        <div class="group rounded-2xl border border-gray-200 bg-white p-6 text-center shadow-sm transition duration-300 hover:border-[#ff7a32]/40 hover:shadow-md">

          <!-- Number -->
          <div class="mx-auto mb-5 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-sm font-bold text-gray-500 transition duration-300 group-hover:bg-[#ff7a32] group-hover:text-white">
            <?= e($number) ?>
          </div>

          <h3 class="text-lg font-semibold text-gray-900">
            <?= e($title) ?>
          </h3>

          <p class="mt-2 text-sm leading-6 text-gray-600">
            <?= e($desc) ?>
          </p>

        </div>

      <?php endforeach; ?>
    </div>

  </div>
</section>

<?php require __DIR__ . '/components/consultation-cta.php'; ?>

<?php require __DIR__ . '/components/closing-cta.php'; ?>

<?php require __DIR__ . '/components/footer.php'; ?>