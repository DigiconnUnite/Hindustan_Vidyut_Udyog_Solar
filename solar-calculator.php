<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/solar-calc.php';

// The estimate itself is computed client-side (assets/js/solar-calc.js) so the
// numbers move as the slider moves. This POST handler only captures the lead
// once the visitor asks for a real quote against the figures they were shown.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $back = '/solar-calculator.php#quote';
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $kw = (float) ($_POST['system_kw'] ?? 0);
    $bill = (int) ($_POST['monthly_bill'] ?? 0);

    if ($name === '' || $phone === '') {
        flash('error', 'Name and phone are required.');
        redirect($back);
    }

    $message = sprintf(
        "Calculator estimate\nMonthly bill: %s\nSuggested system: %s kW\nEstimated subsidy: %s",
        inr($bill),
        $kw,
        inr(solar_subsidy($kw))
    );

    db()->prepare('INSERT INTO leads (name, phone, email, address, message, source, system_kw, monthly_bill) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([$name, $phone, $email ?: null, $pincode !== '' ? 'PIN ' . $pincode : null, $message, 'calculator', $kw ?: null, $bill ?: null]);

    flash('success', 'Thanks! Our team will call you with a detailed quote shortly.');
    redirect($back);
}

$tariff = (float) setting('default_tariff', '8.0');
$financeRate = (float) setting('finance_rate', '9.5');
// Server-rendered default so the page shows real numbers before JS runs.
$initial = solar_estimate(3000.0, $tariff);

$pageTitle = 'Solar Calculator — Estimate Savings, Subsidy & Payback | HVU Solar';
$metaDescription = 'Free solar calculator for Indian homes. Enter your electricity bill to see system size, cost, PM Surya Ghar subsidy, monthly savings and payback period in seconds.';
$jsonLd = [[
    '@context' => 'https://schema.org',
    '@type' => 'WebApplication',
    'name' => 'HVU Solar Savings Calculator',
    'applicationCategory' => 'FinanceApplication',
    'operatingSystem' => 'Web',
    'url' => APP_URL . '/solar-calculator.php',
    'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'INR'],
    'description' => $metaDescription,
]];

$bannerTitle = 'Solar Calculator';
$bannerSubtitle = 'See your system size, subsidy, savings and payback in under a minute.';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="mx-auto container px-6 py-16">
  <div class="grid gap-8 lg:grid-cols-[minmax(0,380px)_1fr] items-start">

    <!-- Inputs -->
    <form id="calc-form" class="card border border-gray-900 shadow-none lg:sticky lg:top-40" novalidate>
      <h2 class="text-xl font-bold text-gray-900">Your electricity use</h2>
      <p class="mt-1 text-sm text-gray-500">Move the slider or type your bill — results update instantly.</p>

      <div class="mt-6">
        <div class="flex items-baseline justify-between">
          <label for="bill" class="text-sm font-medium text-primary-700">Average monthly bill</label>
          <output id="bill-out" for="bill" class="text-lg font-extrabold text-primary-700">₹3,000</output>
        </div>
        <input type="range" id="bill" name="bill" min="500" max="50000" step="500" value="3000"
               class="mt-3 w-full accent-primary-500">
        <div class="mt-1 flex justify-between text-xs text-gray-400">
          <span>₹500</span><span>₹50,000</span>
        </div>
      </div>

      <details class="mt-6 group">
        <summary class="cursor-pointer text-sm font-medium text-primary-700 hover:text-primary-600">
          Fine-tune the estimate
        </summary>
        <div class="mt-4 space-y-4">
          <div>
            <label for="units" class="text-sm font-medium text-primary-700">Monthly units (kWh)</label>
            <input type="number" id="units" min="0" step="10" placeholder="Leave blank to use the bill" class="input mt-1">
            <p class="mt-1 text-xs text-gray-500">On your bill as “units consumed”. More accurate than the amount.</p>
          </div>
          <div>
            <label for="tariff" class="text-sm font-medium text-primary-700">Tariff (₹ per unit)</label>
            <input type="number" id="tariff" min="1" max="30" step="0.5" value="<?= e((string) $tariff) ?>" class="input mt-1">
          </div>
          <div>
            <label for="roof" class="text-sm font-medium text-primary-700">Shadow-free roof area (sq ft)</label>
            <input type="number" id="roof" min="0" step="10" placeholder="Optional" class="input mt-1">
            <p class="mt-1 text-xs text-gray-500">Roughly 80 sq ft per kW. We cap the system if the roof is the limit.</p>
          </div>
        </div>
      </details>

      <div class="mt-6 rounded-xl bg-primary-50 p-4 text-sm text-gray-700">
        <p class="font-semibold text-primary-700">Estimates, not a quote.</p>
        <p class="mt-1 text-xs leading-relaxed">Actual output depends on roof pitch, shading and your DISCOM tariff. A free site survey gives you exact numbers.</p>
      </div>
    </form>

    <!-- Results -->
    <div class="space-y-6">
      <div id="roof-note" class="hidden rounded-xl border border-accent-400 bg-accent-500/10 px-4 py-3 text-sm text-gray-800">
        Your roof area is the limiting factor here — the system is sized to fit the space, not the full bill.
      </div>

      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <?php
        $tiles = [
            ['out-kw',      'System size',      number_format($initial['system_kw'], 1) . ' kW', 'sun'],
            ['out-net',     'Cost after subsidy', inr($initial['net_cost']),                     'package'],
            ['out-savings', 'Saved per month',  inr($initial['monthly_savings']),                'arrow-up'],
            ['out-payback', 'Pays for itself in', $initial['payback_years'] . ' yrs',            'calendar'],
        ];
        foreach ($tiles as [$id, $label, $value, $ico]): ?>
          <div class="card border border-gray-900 shadow-none">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-700"><?= icon($ico, 'h-5 w-5') ?></span>
            <p class="mt-3 text-xs font-medium uppercase tracking-wide text-gray-500"><?= e($label) ?></p>
            <p id="<?= e($id) ?>" class="mt-1 text-2xl font-extrabold text-gray-900"><?= e($value) ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Cost breakdown -->
      <div class="card border border-gray-900 shadow-none">
        <h3 class="text-lg font-bold text-gray-900">What it costs</h3>
        <dl class="mt-4 divide-y divide-gray-100 text-sm">
          <div class="flex items-center justify-between py-3">
            <dt class="text-gray-600">System cost (turnkey, installed)</dt>
            <dd id="out-gross" class="font-semibold text-gray-900"><?= e(inr($initial['gross_cost'])) ?></dd>
          </div>
          <div class="flex items-center justify-between py-3">
            <dt class="text-gray-600">
              PM Surya Ghar subsidy
              <span class="badge ml-1 bg-primary-50 text-primary-700">Central</span>
            </dt>
            <dd id="out-subsidy" class="font-semibold text-primary-700">− <?= e(inr($initial['subsidy'])) ?></dd>
          </div>
          <div class="flex items-center justify-between py-3">
            <dt class="font-semibold text-gray-900">You pay</dt>
            <dd id="out-net-2" class="text-lg font-extrabold text-gray-900"><?= e(inr($initial['net_cost'])) ?></dd>
          </div>
          <div class="flex items-center justify-between py-3">
            <dt class="text-gray-600">Or on finance, from</dt>
            <dd class="font-semibold text-gray-900">
              <span id="out-emi"><?= e(inr(solar_emi($initial['net_cost'], $financeRate, 60))) ?></span>
              <span class="text-xs font-normal text-gray-500">/ month · 60 mo</span>
            </dd>
          </div>
        </dl>
        <a href="/financing.php" class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-primary-700 hover:text-primary-600">
          See financing options <?= icon('arrow-right', 'h-4 w-4') ?>
        </a>
      </div>

      <!-- System + impact -->
      <div class="grid gap-6 md:grid-cols-2">
        <div class="card border border-gray-900 shadow-none">
          <h3 class="text-lg font-bold text-gray-900">What gets installed</h3>
          <dl class="mt-4 space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-gray-600">Panels (545W each)</dt><dd id="out-panels" class="font-semibold text-gray-900"><?= (int) $initial['panels'] ?></dd></div>
            <div class="flex justify-between"><dt class="text-gray-600">Roof area needed</dt><dd id="out-area" class="font-semibold text-gray-900"><?= (int) $initial['area_sqft'] ?> sq ft</dd></div>
            <div class="flex justify-between"><dt class="text-gray-600">Generation per year</dt><dd id="out-annual" class="font-semibold text-gray-900"><?= number_format($initial['annual_units']) ?> units</dd></div>
            <div class="flex justify-between"><dt class="text-gray-600">Savings over 25 years</dt><dd id="out-lifetime" class="font-semibold text-primary-700"><?= e(inr($initial['lifetime_savings'])) ?></dd></div>
          </dl>
        </div>

        <div class="card border border-gray-900 shadow-none bg-primary-50">
          <h3 class="text-lg font-bold text-gray-900">Your climate impact</h3>
          <p class="mt-4 text-4xl font-extrabold text-primary-700"><span id="out-co2"><?= e((string) $initial['co2_tonnes_year']) ?></span> t</p>
          <p class="text-sm text-gray-600">CO₂ avoided every year</p>
          <p class="mt-4 text-sm text-gray-700">
            Equivalent to planting <span id="out-trees" class="font-semibold text-primary-700"><?= (int) $initial['trees_equivalent'] ?></span> trees a year.
          </p>
        </div>
      </div>

      <!-- Lead capture -->
      <div id="quote" class="card border border-gray-900 shadow-none scroll-mt-40">
        <?php require __DIR__ . '/components/flash-message.php'; ?>
        <h3 class="text-xl font-bold text-gray-900">Get this quoted properly</h3>
        <p class="mt-1 text-sm text-gray-600">We'll survey your roof free of charge and confirm these numbers for your actual site.</p>
        <form method="post" action="/solar-calculator.php#quote" class="mt-5 grid gap-4 sm:grid-cols-2">
          <?= csrf_field() ?>
          <input type="hidden" name="system_kw" id="lead-kw" value="<?= e((string) $initial['system_kw']) ?>">
          <input type="hidden" name="monthly_bill" id="lead-bill" value="3000">
          <div>
            <label for="q-name" class="text-sm font-medium text-primary-700">Your name *</label>
            <input type="text" id="q-name" name="name" required class="input mt-1">
          </div>
          <div>
            <label for="q-phone" class="text-sm font-medium text-primary-700">Phone *</label>
            <input type="tel" id="q-phone" name="phone" required pattern="[0-9+ ]{10,15}" class="input mt-1">
          </div>
          <div>
            <label for="q-email" class="text-sm font-medium text-primary-700">Email</label>
            <input type="email" id="q-email" name="email" class="input mt-1">
          </div>
          <div>
            <label for="q-pin" class="text-sm font-medium text-primary-700">PIN code</label>
            <input type="text" id="q-pin" name="pincode" inputmode="numeric" pattern="[0-9]{6}" class="input mt-1">
          </div>
          <div class="sm:col-span-2">
            <button type="submit" class="btn-primary bg-accent-500 text-ink hover:bg-accent-400">
              Send me a detailed quote <span class="btn-icon"><?= icon('send', 'h-4 w-4') ?></span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<script src="/assets/js/solar-calc.js" defer></script>

<?php require __DIR__ . '/components/footer.php'; ?>
