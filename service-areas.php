<?php
require_once __DIR__ . '/config/helpers.php';
lead_handle(['source' => 'contact']);

$areas = db()->query('SELECT * FROM service_areas ORDER BY sort_order')->fetchAll();

// PIN lookup is served from the same rows the page renders, so the check and the
// list can never disagree. Small dataset — no need for an endpoint.
$pinIndex = [];
foreach ($areas as $a) {
    foreach (array_filter(array_map('trim', explode(',', (string) $a['pincodes']))) as $pin) {
        $pinIndex[$pin] = $a['city'];
    }
}

$pageTitle = 'Service Areas — Solar Installation Across Delhi NCR | HVU Solar';
$metaDescription = 'We install and service rooftop solar across Gurgaon, Delhi, Faridabad, Noida, Manesar, Rewari, Sonipat and Ghaziabad. Check whether we cover your PIN code.';

$bannerTitle = 'Service Areas';
$bannerSubtitle = 'Where our survey and installation teams operate.';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="mx-auto container px-6 py-16">
  <!-- PIN checker -->
  <div class="mx-auto max-w-xl card border border-gray-900 shadow-none text-center">
    <h2 class="text-2xl font-bold text-gray-900">Do we cover your area?</h2>
    <p class="mt-1 text-sm text-gray-600">Enter your 6-digit PIN code.</p>
    <div class="mt-5 flex gap-2">
      <label for="pin-input" class="sr-only">PIN code</label>
      <input type="text" id="pin-input" inputmode="numeric" maxlength="6" placeholder="e.g. 122004" class="input flex-1 text-center text-lg tracking-widest">
      <button type="button" id="pin-check" class="btn-primary bg-accent-500 text-ink hover:bg-accent-400 shrink-0">
        Check <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
      </button>
    </div>
    <p id="pin-result" class="mt-4 text-sm" role="status" aria-live="polite"></p>
  </div>

  <!-- Areas -->
  <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
    <?php foreach ($areas as $a): ?>
      <div class="card border border-gray-900 shadow-none">
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-700"><?= icon('map-pin', 'h-5 w-5') ?></span>
        <h3 class="mt-3 text-lg font-bold text-gray-900"><?= e($a['city']) ?></h3>
        <p class="text-sm text-gray-500"><?= e($a['state']) ?></p>
        <?php if ($a['pincodes']): ?>
          <p class="mt-3 text-xs leading-relaxed text-gray-600"><?= e($a['pincodes']) ?></p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <p class="mt-10 text-center text-sm text-gray-500">
    Outside these areas? <a href="/contact.php" class="font-semibold text-primary-700 hover:text-primary-600">Get in touch anyway</a> — we take on projects across Haryana and Delhi NCR.
  </p>
</section>

<?php require __DIR__ . '/components/consultation-cta.php'; ?>

<script>
(function () {
  var pins = <?= json_encode($pinIndex, JSON_UNESCAPED_SLASHES) ?>;
  var input = document.getElementById('pin-input');
  var result = document.getElementById('pin-result');

  function check() {
    var pin = input.value.trim();
    if (!/^\d{6}$/.test(pin)) {
      result.className = 'mt-4 text-sm text-gray-500';
      result.textContent = 'Please enter a valid 6-digit PIN code.';
      return;
    }
    if (pins[pin]) {
      result.className = 'mt-4 text-sm font-semibold text-primary-700';
      result.textContent = 'Yes — we install in ' + pins[pin] + '. Book a free site survey.';
    } else {
      result.className = 'mt-4 text-sm text-gray-700';
      result.textContent = 'Not on our standard list, but we often travel further. Contact us and we will confirm.';
    }
  }

  document.getElementById('pin-check').addEventListener('click', check);
  input.addEventListener('keydown', function (e) { if (e.key === 'Enter') check(); });
})();
</script>

<?php require __DIR__ . '/components/footer.php'; ?>
