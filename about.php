<?php
require_once __DIR__ . '/config/helpers.php';
$pageTitle = 'About Us — Hindustan Vidyut Udyog Solar';
require __DIR__ . '/components/header.php';
?>

<section class="bg-primary-50/60 py-16">
  <div class="mx-auto container px-6 text-center">
    <h1 class="text-4xl font-bold text-gray-900">About Hindustan Vidyut Udyog Solar</h1>
    <p class="mt-3 text-gray-600 max-w-2xl mx-auto">A decade of powering homes with clean, reliable solar energy.</p>
  </div>
</section>

<section class="mx-auto container px-6 py-16 grid gap-10 md:grid-cols-2 items-center">
  <div class="h-72 rounded-2xl bg-gray-100"></div>
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

<section class="mx-auto container px-6 py-16 text-center">
  <h2 class="text-3xl font-bold text-gray-900">Ready to go solar?</h2>
  <a href="/contact.php" class="btn-primary mt-6 inline-flex">Get a Free Quote →</a>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>
