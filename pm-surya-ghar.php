<?php
require_once __DIR__ . '/config/helpers.php';
$pageTitle = 'PM Surya Ghar Muft Bijli Yojana — Hindustan Vidyut Udyog Solar';
$bannerTitle = 'PM Surya Ghar Muft Bijli Yojana';
$bannerSubtitle = 'Get up to ₹78,000 central government subsidy on your rooftop solar system — we handle the paperwork.';
$bannerImage = '/assets/images/pm-surya-ghar-banner.png';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="mx-auto container px-6 py-16">
  <div class="max-w-3xl">
    <h2 class="text-3xl font-bold text-gray-900">What is PM Surya Ghar Yojana?</h2>
    <p class="mt-4 text-gray-600">
      PM Surya Ghar: Muft Bijli Yojana is the central government's scheme to bring rooftop
      solar to Indian homes. Eligible households get a direct subsidy paid into their bank
      account, cutting the upfront cost of a solar system and working toward free electricity
      for those who generate as much as they consume.
    </p>
  </div>
</section>

<section class="bg-primary-50/60 py-16">
  <div class="mx-auto container px-6">
    <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Subsidy Amount by System Size</h2>
    <p class="text-center text-gray-600 mb-10">Central subsidy is paid directly to your bank account (DBT).</p>
    <div class="grid gap-6 sm:grid-cols-3">
      <?php foreach ([
        ['1 kW', '₹30,000', 'Suits a small home with modest daytime usage.'],
        ['2 kW', '₹60,000', 'Common choice for an average household.'],
        ['3 kW & above', '₹78,000', 'Subsidy is capped here — larger systems don\'t get more.'],
      ] as [$size, $amount, $note]): ?>
        <div class="card text-center border border-gray-900 shadow-none">
          <p class="text-sm font-semibold text-primary-700"><?= e($size) ?></p>
          <p class="mt-2 text-3xl font-extrabold text-gray-900"><?= e($amount) ?></p>
          <p class="mt-3 text-sm text-gray-600"><?= e($note) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="mt-8 text-center text-sm text-gray-500">
      The scheme also targets up to 300 free electricity units/month for eligible households.
      Some states add their own top-up subsidy on top of the central amount.
    </p>
  </div>
</section>

<section class="mx-auto container px-6 py-16">
  <h2 class="text-2xl font-bold text-gray-900 text-center mb-10">Am I Eligible?</h2>
  <div class="grid gap-6 md:grid-cols-4">
    <?php foreach ([
      ['Indian Citizen', 'Applicant must be an Indian citizen applying in their own name.'],
      ['Own the Roof', 'You own the property or have authorization to install on the roof.'],
      ['Valid Electricity Connection', 'An active connection with your DISCOM in the applicant\'s name.'],
      ['No Existing Solar', 'No rooftop solar system already installed on the property.'],
    ] as [$title, $desc]): ?>
      <div class="card text-center">
        <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-accent-500/10 text-accent-600"><?= icon('shield', 'h-5 w-5') ?></span>
        <h3 class="mt-4 font-semibold text-gray-900"><?= e($title) ?></h3>
        <p class="mt-2 text-sm text-gray-600"><?= e($desc) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="bg-gray-50 py-16">
  <div class="mx-auto container px-6">
    <h2 class="text-2xl font-bold text-gray-900 text-center mb-10">Documents You'll Need</h2>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      <?php foreach ([
        'Aadhaar Card',
        'Latest Electricity Bill',
        'Proof of Roof / Property Ownership',
        'Bank Passbook or Cancelled Cheque',
      ] as $doc): ?>
        <div class="card flex items-center gap-3">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600"><?= icon('clipboard', 'h-5 w-5') ?></span>
          <p class="font-medium text-gray-900 text-sm"><?= e($doc) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="mx-auto container px-6 py-16">
  <h2 class="text-2xl font-bold text-gray-900 text-center mb-10">How It Works</h2>
  <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-5">
    <?php foreach ([
      'Register on the official government portal with your details and electricity bill.',
      'Your DISCOM reviews the application and approves technical feasibility.',
      'HVU Solar installs your system as your registered vendor.',
      'Net meter is installed and the system is inspected.',
      'Subsidy is disbursed directly to your bank account.',
    ] as $i => $step): ?>
      <div class="card border border-gray-900 shadow-none">
        <span class="badge bg-accent-500 text-ink font-bold text-sm h-8 w-8 justify-center px-0"><?= sprintf('%02d', $i + 1) ?></span>
        <p class="mt-4 text-sm text-gray-600"><?= e($step) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
  <p class="mt-6 text-center text-sm text-gray-500">
    HVU Solar is with you at every step — from checking eligibility to handling the DISCOM
    paperwork so you don't have to.
  </p>
</section>

<section class="mx-3 my-4 rounded-3xl bg-ink py-16 text-center">
  <h2 class="text-3xl font-bold text-white">Check Your Subsidy Eligibility</h2>
  <p class="mt-3 text-gray-300 max-w-xl mx-auto">
    Talk to our team — we'll check your eligibility and handle the government paperwork for you.
  </p>
  <a href="/contact.php?subject=pm-surya-ghar" class="btn-primary mt-6 bg-white text-gray-900 hover:bg-gray-100">
    Get a Free Quote <span class="btn-icon bg-accent-500 text-ink"><?= icon('arrow-right', 'h-4 w-4') ?></span>
  </a>
  <p class="mt-6 text-xs text-gray-400 max-w-xl mx-auto">
    Subsidy amounts and eligibility are set by the government under the PM Surya Ghar Muft
    Bijli Yojana and may change. HVU Solar assists with the application; final approval is
    granted by your DISCOM and the official government portal.
  </p>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>
