<?php
require_once __DIR__ . '/config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $phone === '') {
        flash('error', 'Name and phone are required.');
        redirect('/contact.php');
    }

    // no subject column on leads; keep it with the message so admin still sees it
    if ($subject !== '') {
        $message = "Subject: $subject\n\n$message";
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

<section class="mx-auto container px-6 py-16">
  <div class="grid gap-8 overflow-hidden lg:grid-cols-[1fr_400px] items-start">

      <div class="rounded-3xl bg-gray-50 p-6 md:p-10">
        <h2 class="text-2xl font-bold text-gray-900">Send Us a Message</h2>
        <p class="mt-2 text-sm text-gray-600 max-w-xl">
          Tell us a little about your property and what you need. Our team responds
          to every enquiry, usually within one working day.
        </p>

        <?php require __DIR__ . '/components/flash-message.php'; ?>

        <form method="post" action="/contact.php" class="mt-8 space-y-5">
          <?= csrf_field() ?>

          <div class="grid gap-5 md:grid-cols-2">
            <div>
              <label for="name" class="text-sm font-medium text-primary-700">Your Name <span class="text-red-500">*</span></label>
              <input id="name" type="text" name="name" required placeholder="e.g. Jason Samuel" class="input mt-1 bg-white py-2.5">
            </div>
            <div>
              <label for="email" class="text-sm font-medium text-primary-700">Your Email</label>
              <input id="email" type="email" name="email" placeholder="e.g. hola@dominantsite.com" class="input mt-1 bg-white py-2.5">
            </div>
            <div>
              <label for="phone" class="text-sm font-medium text-primary-700">Phone <span class="text-red-500">*</span></label>
              <input id="phone" type="tel" name="phone" required placeholder="e.g. +91 98765 43210" class="input mt-1 bg-white py-2.5">
            </div>
            <div>
              <label for="subject" class="text-sm font-medium text-primary-700">Subject</label>
              <input id="subject" type="text" name="subject" placeholder="e.g. Solar Installation" class="input mt-1 bg-white py-2.5"
                     value="<?= e($prefillProduct ?: ($prefillSubject === 'pm-surya-ghar' ? 'PM Surya Ghar Subsidy' : '')) ?>">
            </div>
          </div>

          <div>
            <label for="address" class="text-sm font-medium text-primary-700">Installation Address</label>
            <input id="address" type="text" name="address" placeholder="Where would the system be installed?" class="input mt-1 bg-white py-2.5">
          </div>

          <div>
            <label for="message" class="text-sm font-medium text-primary-700">Message</label>
            <textarea id="message" name="message" rows="5" placeholder="Write your message here..." class="input mt-1 bg-white"><?php
              if ($prefillProduct) {
                  echo e('Interested in: ' . $prefillProduct);
              } elseif ($prefillSubject === 'pm-surya-ghar') {
                  echo e('I would like to check my eligibility for the PM Surya Ghar subsidy.');
              }
            ?></textarea>
          </div>

          <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-accent-500 py-1.5 pl-5 pr-1.5 font-semibold text-white hover:bg-accent-600">
            Send Message
            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-white text-accent-600"><?= icon('send', 'h-4 w-4') ?></span>
          </button>
        </form>
      </div>

      <aside class="rounded-3xl h-full relative overflow-hidden bg-ink p-8 flex flex-col">
        <span class="inline-flex items-center self-start rounded-full border border-accent-400 px-4 py-1.5 text-sm font-medium text-accent-400">
          Get in Touch
        </span>

        <h3 class="mt-6 text-3xl font-extrabold text-white leading-tight">
          Let's Power Progress<br>with Solar
        </h3>
        <p class="mt-4 text-gray-300 leading-relaxed">
          Every message brings us closer to a cleaner, brighter tomorrow.
          Reach out — we're ready to listen.
        </p>

        <ul class="mt-8 space-y-5 text-sm">
          <li class="flex items-start gap-3">
            <span class="mt-0.5 shrink-0 text-accent-400"><?= icon('map-pin', 'h-5 w-5') ?></span>
            <span class="font-semibold text-white"><?= e(setting('company_address', 'India')) ?></span>
          </li>
          <li class="flex items-center gap-3">
            <span class="shrink-0 text-accent-400"><?= icon('phone', 'h-5 w-5') ?></span>
            <a href="tel:<?= e(setting('company_phone', '+91 98765 43210')) ?>" class="font-semibold text-white hover:text-accent-400"><?= e(setting('company_phone', '+91 98765 43210')) ?></a>
          </li>
          <li class="flex items-center gap-3">
            <span class="shrink-0 text-accent-400"><?= icon('mail', 'h-5 w-5') ?></span>
            <a href="mailto:<?= e(setting('company_email', 'info@hvusolar.com')) ?>" class="font-semibold text-white hover:text-accent-400"><?= e(setting('company_email', 'info@hvusolar.com')) ?></a>
          </li>
        </ul>

        <img src="/assets/images/solar-svg-1.webp" alt="" class="mt-auto absolute bottom-0 right-0 pt-8 w-full max-w-[280px] self-center">
      </aside>

  </div>
</section>

<section class="mx-auto container px-6 pb-16">
  <div class="flex items-center gap-2 mb-4">
    <span class="text-accent-500"><?= icon('map-pin', 'h-5 w-5') ?></span>
    <h2 class="text-2xl font-bold text-gray-900">Find Us</h2>
  </div>
  <div class="aspect-video md:aspect-[3/1] w-full overflow-hidden rounded-3xl bg-gray-100">
    <iframe
      src="https://www.google.com/maps?q=<?= urlencode(setting('company_address', 'India')) ?>&output=embed"
      class="h-full w-full border-0"
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade"
      title="Our location on Google Maps"
      allowfullscreen></iframe>
  </div>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>
