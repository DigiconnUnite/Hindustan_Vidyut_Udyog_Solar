<?php
require_once __DIR__ . '/config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $phone === '') {
        flash('error', 'Name and phone are required.');
        redirect('/contact.php');
    }

    $stmt = db()->prepare(
        'INSERT INTO leads (name, phone, email, address, message) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$name, $phone, $email ?: null, $address ?: null, $message ?: null]);

    flash('success', 'Thanks! We received your request and will contact you shortly.');
    redirect('/contact.php');
}

$pageTitle = 'Support & Contact — Hindustan Vidyut Udyog Solar';
$prefillProduct = trim($_GET['product'] ?? '');
$prefillSubject = trim($_GET['subject'] ?? '');
$bannerTitle = 'Support & Contact';
$bannerSubtitle = 'Have a question or want a free quote? Reach out below.';

require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="mx-auto container px-6 py-16 grid gap-10 md:grid-cols-2">
  <div class="card">
    <?php require __DIR__ . '/components/flash-message.php'; ?>
    <form method="post" action="/contact.php" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="text-sm font-medium text-gray-700">Name *</label>
        <input type="text" name="name" required class="input mt-1">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Phone *</label>
        <input type="tel" name="phone" required class="input mt-1">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" class="input mt-1">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Address</label>
        <input type="text" name="address" class="input mt-1">
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Message</label>
        <textarea name="message" rows="4" class="input mt-1"><?php
          if ($prefillProduct) {
              echo e('Interested in: ' . $prefillProduct);
          } elseif ($prefillSubject === 'pm-surya-ghar') {
              echo e('I would like to check my eligibility for the PM Surya Ghar subsidy.');
          }
        ?></textarea>
      </div>
      <button type="submit" class="btn-primary w-full justify-between">Send Request <span class="btn-icon"><?= icon('send', 'h-4 w-4') ?></span></button>
    </form>
  </div>

  <div class="space-y-6">
    <div class="card">
      <h3 class="font-semibold text-gray-900">Contact Details</h3>
      <ul class="mt-3 space-y-2 text-sm text-gray-600">
        <li class="flex items-center gap-2"><?= icon('phone', 'h-4 w-4 text-primary-600') ?> <?= e(setting('company_phone', '+91 98765 43210')) ?></li>
        <li class="flex items-center gap-2"><?= icon('mail', 'h-4 w-4 text-primary-600') ?> <?= e(setting('company_email', 'info@hvusolar.com')) ?></li>
        <li class="flex items-center gap-2"><?= icon('map-pin', 'h-4 w-4 text-primary-600') ?> <?= e(setting('company_address', 'India')) ?></li>
      </ul>
    </div>
    <div class="card">
      <h3 class="font-semibold text-gray-900 mb-3">FAQ</h3>
      <div class="space-y-3 text-sm">
        <details class="border-b border-gray-100 pb-2">
          <summary class="font-medium cursor-pointer text-gray-800">How long does installation take?</summary>
          <p class="mt-2 text-gray-600">Most residential installations are completed within 3-5 days after approval.</p>
        </details>
        <details class="border-b border-gray-100 pb-2">
          <summary class="font-medium cursor-pointer text-gray-800">Do you offer maintenance plans?</summary>
          <p class="mt-2 text-gray-600">Yes, we offer annual maintenance contracts (AMC) for all installed systems.</p>
        </details>
        <details>
          <summary class="font-medium cursor-pointer text-gray-800">Is the site survey free?</summary>
          <p class="mt-2 text-gray-600">Yes, the initial site survey and quote are completely free of charge.</p>
        </details>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>
