<?php
require_once __DIR__ . '/config/helpers.php';
consultation_handle();
$pageTitle = 'FAQ — Hindustan Vidyut Udyog Solar';
$bannerTitle = 'Frequently Asked Questions';
$bannerSubtitle = 'Answers to the questions we hear most from homeowners.';

// category label => [question, answer][]; anchor ids are derived from the label
$faqs = [
    'Getting Started' => [
        ['Is the site survey really free?', 'Yes. The initial site survey, system design and quotation are completely free with no obligation to proceed. Our engineer visits your property, checks roof orientation, shading and available area, and gives you a generation estimate specific to your address.'],
        ['How do I know if my roof is suitable?', 'Most roofs are. The four things we check are orientation and tilt, shading from tanks or nearby structures, roof age and condition, and available unshaded area — roughly 100 square feet per kilowatt. The site survey covers all four.'],
        ['How long does the whole process take?', 'A typical residential project runs 3 to 6 weeks end to end. The physical installation is only 1 to 3 days; the rest is subsidy paperwork, DISCOM approval and net meter installation, which we handle on your behalf.'],
        ['What size system do I need?', 'It depends on your monthly consumption rather than your roof size. As a rough guide, 1 kW generates about 4 units per day. We size the system against your last 12 months of bills so you are not paying for capacity you will not use.'],
    ],
    'Cost & Savings' => [
        ['How much can I actually save?', 'Most homeowners see their electricity bill drop by 70-90%. Fixed charges and meter rent still apply, so bills rarely reach zero. Typical payback is 5-7 years, against a system life of 25 years.'],
        ['What subsidy am I eligible for?', 'Under PM Surya Ghar Muft Bijli Yojana, residential rooftop systems receive tiered central subsidy, highest for the first 2 kW with additional support for the third. The subsidy is credited to your bank account after installation and inspection, not deducted upfront.'],
        ['Do you offer financing or EMI?', 'Yes. We work with lending partners who offer collateral-free solar loans for residential systems. We can share current rates and tenures during your quotation.'],
        ['Are there hidden costs after installation?', 'No. Your quotation covers panels, inverter, mounting structure, cabling, protection equipment, installation and commissioning. The only recurring cost is optional annual maintenance, quoted separately and clearly.'],
    ],
    'Technical' => [
        ['Will my system work during a power cut?', 'A standard on-grid system shuts down during an outage for lineworker safety. If you need backup power, a hybrid system with battery storage keeps essential circuits running — we can quote both.'],
        ['What happens on cloudy days and during monsoon?', 'Generation drops but does not stop, since panels respond to daylight rather than direct sun. Rain also washes dust off the glass, which usually improves output once skies clear. Systems are sized on annual generation to account for seasonal swing.'],
        ['What is net metering?', 'When your panels generate more than you consume, the surplus flows to the grid and is recorded as an export credit. Those credits offset the units you draw at night, and you are billed only on net consumption.'],
        ['How long do panels last?', 'Panels carry a 25-year performance warranty and typically keep producing beyond it at gradually reduced output. Inverters have a shorter life, usually 10-15 years, and are the one component most owners replace once.'],
    ],
    'Service & Support' => [
        ['What maintenance does a solar system need?', 'Very little. Rinse the panels every few months to clear dust, watch for new shading, and check your inverter status light. An annual professional inspection covers wiring, mounting and panel condition.'],
        ['Do you offer maintenance contracts?', 'Yes. Our Annual Maintenance Contract covers scheduled cleaning, performance monitoring, electrical checks and priority support for all systems we install.'],
        ['What warranty do I get?', 'Panels carry a 25-year performance warranty from the manufacturer, inverters typically 5-10 years, and our installation workmanship is warranted separately. Exact terms are listed in your quotation.'],
        ['Who do I contact if something goes wrong?', 'Call or email our support team using the details below. Systems under AMC receive priority response, and most issues are diagnosed remotely through the monitoring app before any site visit.'],
    ],
];

$faqAnchor = fn(string $label): string => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $label));

require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="mx-auto container px-6 py-16">
  <div class="grid gap-10 lg:grid-cols-[220px_1fr]">
    <aside class="hidden lg:block">
      <p class="text-sm font-semibold text-gray-900 mb-4">Categories</p>
      <ul class="space-y-2 text-sm">
        <?php foreach (array_keys($faqs) as $category): ?>
          <li>
            <a href="#<?= e($faqAnchor($category)) ?>" class="text-gray-600 hover:text-primary-700"><?= e($category) ?></a>
          </li>
        <?php endforeach; ?>
      </ul>
    </aside>

    <div class="space-y-12">
      <?php foreach ($faqs as $category => $items): ?>
        <div id="<?= e($faqAnchor($category)) ?>" class="scroll-mt-44">
          <h2 class="text-2xl font-bold text-gray-900 mb-5"><?= e($category) ?></h2>
          <div class="space-y-3">
            <?php foreach ($items as [$question, $answer]): ?>
              <details class="card group p-0 overflow-hidden">
                <summary class="flex cursor-pointer items-center justify-between gap-4 px-5 py-4 font-medium text-gray-900 hover:text-primary-700">
                  <?= e($question) ?>
                  <span class="shrink-0 text-accent-500 transition-transform group-open:rotate-90"><?= icon('arrow-right', 'h-4 w-4') ?></span>
                </summary>
                <p class="border-t border-gray-100 px-5 py-4 text-sm leading-relaxed text-gray-600"><?= e($answer) ?></p>
              </details>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/components/consultation-cta.php'; ?>

<?php require __DIR__ . '/components/footer.php'; ?>
