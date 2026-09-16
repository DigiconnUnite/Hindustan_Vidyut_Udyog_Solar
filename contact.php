<?php
require_once __DIR__ . '/config/helpers.php';

$prefillProduct = trim($_GET['product'] ?? '');
$prefillSubject = trim($_GET['subject'] ?? '');

// Legacy kit links pass a slug; resolve it to the real name rather than trusting
// (or echoing) the query string. An unknown slug simply names nothing.
$prefillKit = trim($_GET['kit'] ?? '');
if ($prefillProduct === '' && $prefillKit !== '') {
    $kitStmt = db()->prepare('SELECT name FROM solar_kits WHERE slug = ? AND is_active = 1');
    $kitStmt->execute([$prefillKit]);
    $prefillProduct = (string) ($kitStmt->fetchColumn() ?: '');
}

// Carried into the lead's message, since leads has no subject/product column. Derived from
// the query string rather than the POST body so it cannot be forged.
$contactPrepend = '';
if ($prefillProduct !== '') {
    $contactPrepend = 'Interested in: ' . $prefillProduct;
} elseif ($prefillSubject === 'pm-surya-ghar') {
    $contactPrepend = 'Subject: PM Surya Ghar Subsidy';
}

lead_handle([
    'source' => 'contact',
    'back' => '/contact.php#enquiry',
    'prepend' => $contactPrepend,
]);

$pageTitle = 'Contact Us — Free Solar Site Survey in Gurgaon | HVU Solar';
$metaDescription = 'Book a free rooftop survey or raise a grievance. Call, WhatsApp or send us a message — our Gurgaon team responds the same working day.';
$bannerTitle = 'Support & Contact';
$bannerSubtitle = 'Have a question or want a free quote? Reach out below.';

require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="mx-auto container px-6 py-16">
  <div class="grid gap-8 overflow-hidden lg:grid-cols-[1fr_400px] items-start">

      <div class="rounded-3xl bg-gray-50 p-6 md:p-10 border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-900">Send Us a Message</h2>
        <p class="mt-2 text-sm text-gray-600 max-w-xl">
          Tell us a little about your property and what you need. Our team responds
          to every enquiry, usually within one working day.
        </p>

        <div class="mt-8">
          <?php
          $leadLight = true;
          $leadEyebrow = '';
          $leadButton = 'Send Message';
          $leadMessageLabel = 'Message';
          $leadMessagePlaceholder = 'Roof size, monthly bill, installation address, or anything else we should know';
          $leadAction = '/contact.php#enquiry';
          require __DIR__ . '/components/lead-form.php';
          ?>
        </div>
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
