<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/solar-calc.php';

$rate = (float) setting('finance_rate', '9.5');
$maxMonths = (int) setting('finance_max_months', '84');
$defaultAmount = 150000;
$defaultMonths = 60;

// A finance enquiry carries the loan figures the visitor was shown. They come from hidden
// inputs, so clamp before they reach solar_emi() — a zero tenure would divide by zero.
$amount = min(10000000, max(10000, (int) ($_POST['amount'] ?? $defaultAmount)));
$months = min($maxMonths, max(6, (int) ($_POST['months'] ?? $defaultMonths)));

lead_handle([
    'source' => 'financing',
    'back' => '/financing.php#apply',
    'prepend' => sprintf(
        "Financing enquiry\nAmount: %s\nTenure: %d months\nIndicative EMI: %s at %s%% p.a.",
        inr($amount),
        $months,
        inr(solar_emi($amount, $rate, $months)),
        $rate
    ),
    'success' => 'Thanks! Our finance desk will call you to walk through the options.',
]);

$pageTitle = 'Solar Loans & EMI — Finance Your Rooftop System | HVU Solar';
$metaDescription = 'Finance your rooftop solar with EMIs from ₹' . number_format(solar_emi($defaultAmount, $rate, $defaultMonths)) . '/month. Collateral-free solar loans, PM Surya Ghar subsidy adjusted, tenures up to ' . $maxMonths . ' months.';

$faqs = [
    ['Do I need collateral for a solar loan?', 'For residential rooftop systems up to 10 kW, most partner lenders offer collateral-free loans against income proof alone. Larger commercial systems may need the asset itself as security.'],
    ['Is the PM Surya Ghar subsidy adjusted in the loan?', 'Yes. We finance the amount after subsidy, so you borrow only what you actually pay. The subsidy is credited directly to your bank account once the system is commissioned and inspected.'],
    ['What interest rate applies?', 'Residential solar loans under the PM Surya Ghar scheme are available from around ' . $rate . '% per annum for eligible applicants. Your final rate depends on the lender, your credit profile and the tenure you choose.'],
    ['How long does approval take?', 'Most applications are decided within 3–5 working days once documents are in. We coordinate the paperwork alongside the site survey, so financing rarely delays the installation.'],
    ['What documents do I need?', 'PAN and Aadhaar, the last six months of bank statements, latest salary slips or ITR for the self-employed, and a recent electricity bill for the property.'],
    ['Can I prepay the loan?', 'Yes. Most partner lenders allow prepayment after the first 6–12 EMIs, usually with no penalty on floating-rate loans. Confirm the terms with the lender before signing.'],
];

$jsonLd = [[
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(fn($f) => [
        '@type' => 'Question',
        'name' => $f[0],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
    ], $faqs),
]];

$bannerTitle = 'Financing & EMI';
$bannerSubtitle = 'Go solar now, pay monthly — often for less than the bill it replaces.';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="mx-auto container px-6 py-16">
  <div class="grid gap-10 lg:grid-cols-2 items-start">

    <!-- EMI calculator -->
    <div class="card border border-gray-900 shadow-none">
      <h2 class="text-2xl font-bold text-gray-900">EMI calculator</h2>
      <p class="mt-1 text-sm text-gray-600">Indicative only — your lender sets the final rate.</p>

      <form id="emi-form" class="mt-6 space-y-6" novalidate>
        <div>
          <div class="flex items-baseline justify-between">
            <label for="amount" class="text-sm font-medium text-primary-700">Loan amount</label>
            <output id="amount-out" class="text-lg font-extrabold text-primary-700"><?= e(inr($defaultAmount)) ?></output>
          </div>
          <input type="range" id="amount" min="25000" max="1000000" step="5000" value="<?= $defaultAmount ?>" class="mt-3 w-full accent-primary-500">
          <div class="mt-1 flex justify-between text-xs text-gray-400"><span>₹25,000</span><span>₹10,00,000</span></div>
        </div>

        <div>
          <div class="flex items-baseline justify-between">
            <label for="months" class="text-sm font-medium text-primary-700">Tenure</label>
            <output id="months-out" class="text-lg font-extrabold text-primary-700"><?= $defaultMonths ?> months</output>
          </div>
          <input type="range" id="months" min="12" max="<?= $maxMonths ?>" step="6" value="<?= $defaultMonths ?>" class="mt-3 w-full accent-primary-500">
          <div class="mt-1 flex justify-between text-xs text-gray-400"><span>12 mo</span><span><?= $maxMonths ?> mo</span></div>
        </div>

        <div>
          <div class="flex items-baseline justify-between">
            <label for="rate" class="text-sm font-medium text-primary-700">Interest rate (% p.a.)</label>
            <output id="rate-out" class="text-lg font-extrabold text-primary-700"><?= e((string) $rate) ?>%</output>
          </div>
          <input type="range" id="rate" min="6" max="18" step="0.25" value="<?= e((string) $rate) ?>" class="mt-3 w-full accent-primary-500">
          <div class="mt-1 flex justify-between text-xs text-gray-400"><span>6%</span><span>18%</span></div>
        </div>
      </form>

      <div class="mt-8 rounded-2xl bg-primary-50 p-6 text-center">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Your monthly EMI</p>
        <p id="emi-out" class="mt-1 text-4xl font-extrabold text-primary-700"><?= e(inr(solar_emi($defaultAmount, $rate, $defaultMonths))) ?></p>
      </div>

      <dl class="mt-6 divide-y divide-gray-100 text-sm">
        <div class="flex justify-between py-3">
          <dt class="text-gray-600">Total interest</dt>
          <dd id="interest-out" class="font-semibold text-gray-900"><?= e(inr(solar_emi($defaultAmount, $rate, $defaultMonths) * $defaultMonths - $defaultAmount)) ?></dd>
        </div>
        <div class="flex justify-between py-3">
          <dt class="text-gray-600">Total repayment</dt>
          <dd id="total-out" class="font-semibold text-gray-900"><?= e(inr(solar_emi($defaultAmount, $rate, $defaultMonths) * $defaultMonths)) ?></dd>
        </div>
      </dl>

      <p class="mt-4 text-xs leading-relaxed text-gray-500">
        Not a loan offer. Approval, rate and tenure are decided by the lender based on your credit profile.
      </p>
    </div>

    <!-- Why finance + how it works -->
    <div class="space-y-8">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Why finance a solar system?</h2>
        <p class="mt-3 text-gray-600 leading-relaxed">
          A rooftop system pays for itself in four to six years and then keeps producing for another twenty.
          Financing simply moves that upfront cost into monthly instalments — and for most households the
          EMI lands close to, or below, the electricity bill the system replaces.
        </p>
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <?php
        $benefits = [
            ['Zero to low down payment', 'Start with as little as 10–20% of the post-subsidy cost.', 'package'],
            ['Collateral-free', 'Residential systems up to 10 kW usually need no security.', 'shield'],
            ['Subsidy adjusted first', 'You borrow only the amount left after PM Surya Ghar.', 'sun'],
            ['Tenures up to 7 years', 'Stretch the EMI to fit whatever your budget allows.', 'calendar'],
        ];
        foreach ($benefits as [$title, $body, $ico]): ?>
          <div class="card border border-gray-900 shadow-none">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-700"><?= icon($ico, 'h-5 w-5') ?></span>
            <h3 class="mt-3 font-semibold text-gray-900"><?= e($title) ?></h3>
            <p class="mt-1 text-sm text-gray-600"><?= e($body) ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <div>
        <h3 class="text-xl font-bold text-gray-900">How it works</h3>
        <ol class="mt-4 space-y-4">
          <?php
          $steps = [
              ['Free site survey', 'We measure your roof and size the system, so the loan amount is based on a real quote.'],
              ['Choose a tenure', 'Pick the EMI that fits your budget. We share the post-subsidy amount to finance.'],
              ['Submit documents', 'PAN, Aadhaar, bank statements and income proof. We help assemble the file.'],
              ['Approval in 3–5 days', 'The lender sanctions the loan and disburses directly against the installation.'],
              ['Installation & subsidy', 'We install and commission; the subsidy credits to your account after inspection.'],
          ];
          foreach ($steps as $i => [$title, $body]): ?>
            <li class="flex gap-4">
              <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-accent-500 text-sm font-bold text-ink"><?= $i + 1 ?></span>
              <div>
                <h4 class="font-semibold text-gray-900"><?= e($title) ?></h4>
                <p class="mt-0.5 text-sm text-gray-600"><?= e($body) ?></p>
              </div>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Apply -->
<section id="apply" class="bg-primary-50 py-16 scroll-mt-40">
  <div class="mx-auto container px-6">
    <div class="mx-auto max-w-2xl">
      <?php
      // The hidden amount/months inputs keep their ids — the EMI slider script at the
      // bottom of this page writes the shown figures into them before submit.
      $leadLight = true;
      $leadEyebrow = '';
      $leadTitle = 'Talk to our finance desk';
      $leadAnchor = 'apply-form';
      $leadButton = 'Request a callback';
      $leadMessageLabel = 'Anything we should know?';
      $leadMessagePlaceholder = 'Existing loans, preferred lender, or questions about the paperwork';
      $leadAction = '/financing.php#apply';
      $leadHidden = [
          'amount' => ['id' => 'lead-amount', 'value' => (string) $defaultAmount],
          'months' => ['id' => 'lead-months', 'value' => (string) $defaultMonths],
      ];
      require __DIR__ . '/components/lead-form.php';
      ?>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="mx-auto container px-6 py-16">
  <h2 class="text-3xl font-bold text-gray-900 text-center">Financing questions</h2>
  <div class="mx-auto mt-10 max-w-3xl divide-y divide-gray-200">
    <?php foreach ($faqs as [$q, $a]): ?>
      <details class="group py-5">
        <summary class="flex cursor-pointer items-center justify-between gap-4 font-semibold text-gray-900 hover:text-primary-700">
          <?= e($q) ?>
          <span class="shrink-0 text-primary-700 transition-transform group-open:rotate-90"><?= icon('arrow-right', 'h-5 w-5') ?></span>
        </summary>
        <p class="mt-3 text-sm leading-relaxed text-gray-600"><?= e($a) ?></p>
      </details>
    <?php endforeach; ?>
  </div>
</section>

<script>
(function () {
  // Mirrors solar_emi() in config/solar-calc.php.
  var inr = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 });
  var el = function (id) { return document.getElementById(id); };

  function emi(principal, annualRate, months) {
    if (principal <= 0 || months <= 0) return 0;
    var r = annualRate / 12 / 100;
    if (r <= 0) return Math.round(principal / months);
    var f = Math.pow(1 + r, months);
    return Math.round(principal * r * f / (f - 1));
  }

  function render() {
    var amount = Number(el('amount').value);
    var months = Number(el('months').value);
    var rate = Number(el('rate').value);
    var monthly = emi(amount, rate, months);

    el('amount-out').textContent = inr.format(amount);
    el('months-out').textContent = months + ' months';
    el('rate-out').textContent = rate + '%';
    el('emi-out').textContent = inr.format(monthly);
    el('total-out').textContent = inr.format(monthly * months);
    el('interest-out').textContent = inr.format(monthly * months - amount);

    el('lead-amount').value = amount;
    el('lead-months').value = months;
  }

  el('emi-form').addEventListener('input', render);
  render();
})();
</script>

<?php require __DIR__ . '/components/footer.php'; ?>
