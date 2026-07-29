<?php
require_once __DIR__ . '/config/helpers.php';
$pageTitle = 'Services — Hindustan Vidyut Udyog Solar';

$services = db()->query('SELECT * FROM services ORDER BY sort_order')->fetchAll();

require __DIR__ . '/components/header.php';
?>

<section class="bg-primary-50/60 py-16">
  <div class="mx-auto container px-6 text-center">
    <h1 class="text-4xl font-bold text-gray-900">Our Services</h1>
    <p class="mt-3 text-gray-600 max-w-2xl mx-auto">From first survey to lifetime support, we handle every step of your solar journey.</p>
  </div>
</section>

<section class="mx-auto container px-6 py-16 space-y-10">
  <?php foreach ($services as $i => $service): ?>
    <div class="grid gap-8 md:grid-cols-2 items-center <?= $i % 2 === 1 ? 'md:[&>*:first-child]:order-2' : '' ?>">
      <div class="h-56 rounded-2xl bg-gray-100 flex items-center justify-center text-primary-500"><?= icon($service['icon'] ?: 'sun', 'h-16 w-16') ?></div>
      <div>
        <h2 class="text-2xl font-bold text-gray-900"><?= e($service['title']) ?></h2>
        <p class="mt-3 text-gray-600"><?= e($service['description']) ?></p>
      </div>
    </div>
  <?php endforeach; ?>
</section>

<section class="bg-gray-50 py-16 text-center">
  <h2 class="text-3xl font-bold text-gray-900">Ready to get started?</h2>
  <a href="/contact.php" class="btn-primary mt-6 inline-flex">Get a Free Quote →</a>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>
