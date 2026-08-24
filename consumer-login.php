<?php
require_once __DIR__ . '/config/helpers.php';
consultation_handle();
$pageTitle = 'Consumer Login — Track Your Solar Application | HVU Solar';
$metaDescription = 'Consumer portal for HVU Solar customers. Subsidy applications are tracked through the national PM Surya Ghar portal; our team handles the paperwork on your behalf.';
$bannerTitle = 'Consumer Login';
$bannerSubtitle = 'Track your rooftop solar application and subsidy status.';

require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="py-20">
  <div class="mx-auto container px-6 max-w-2xl">
    <div class="card p-8 text-center">
      <span class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-primary-50 text-primary-700"><?= icon('users', 'h-7 w-7') ?></span>
      <h2 class="mt-4 text-2xl font-bold text-gray-900">Consumer accounts are coming soon</h2>
      <p class="mt-3 text-gray-600 leading-relaxed">
        Rooftop subsidy applications are filed and tracked on the national
        <strong>PM Surya Ghar</strong> portal, and we file yours on your behalf. Until our own
        customer dashboard is live, use the national portal to check your application, or
        contact us and we will pull up your status.
      </p>

      <div class="mt-7 flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="https://pmsuryaghar.gov.in/consumerLogin" target="_blank" rel="noopener"
           class="inline-flex h-11 w-full sm:w-auto items-center justify-center gap-2 rounded-lg bg-primary-700 px-5 font-semibold text-white hover:bg-primary-800">
          <?= icon('log-in', 'h-4 w-4') ?> PM Surya Ghar Portal
        </a>
        <a href="/contact.php"
           class="inline-flex h-11 w-full sm:w-auto items-center justify-center gap-2 rounded-lg border border-primary-700 px-5 font-medium text-primary-700 hover:bg-primary-50">
          <?= icon('mail', 'h-4 w-4') ?> Ask Us for a Status Update
        </a>
      </div>

      <p class="mt-6 text-sm text-gray-500">
        Prefer to talk? Call
        <a href="tel:<?= e(setting('company_phone', '+91 98765 43210')) ?>" class="font-medium text-primary-700 hover:underline"><?= e(setting('company_phone', '+91 98765 43210')) ?></a>.
      </p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>
