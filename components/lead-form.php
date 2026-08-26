<?php
// The site's single lead-capture form. Every public enquiry — contact, product, calculator,
// financing, careers — posts through this and is handled by lead_handle() in config/helpers.php,
// which the embedding page MUST call before any output.
//
// Optional variables set before the require:
//   $leadEyebrow  — pill label above the fields      (default 'Get a Free Quote')
//   $leadTitle    — heading above the pill           (default none)
//   $leadNote     — reassurance line under the buttons
//   $leadButton   — submit label                     (default 'Send Enquiry')
//   $leadMessageLabel / $leadMessagePlaceholder
//   $leadHidden   — ['system_kw' => 3.0, …] rendered as hidden inputs
//   $leadRole     — ['name' => 'role', 'label' => 'Role', 'options' => [...]] for careers
//   $leadAction   — form action (default: current URL + #enquiry)
//   $leadLight    — true for the light card used on white sections (default: dark card)
$leadEyebrow = $leadEyebrow ?? 'Get a Free Quote';
$leadTitle = $leadTitle ?? '';
$leadNote = $leadNote ?? 'No spam. Your details are used only to prepare your quote.';
$leadButton = $leadButton ?? 'Send Enquiry';
$leadMessageLabel = $leadMessageLabel ?? 'Message';
$leadMessagePlaceholder = $leadMessagePlaceholder ?? 'Roof size, monthly bill, or anything else we should know';
$leadHidden = $leadHidden ?? [];
$leadRole = $leadRole ?? null;
$leadAnchor = $leadAnchor ?? 'enquiry';
$leadAction = $leadAction ?? (e($_SERVER['REQUEST_URI']) . '#' . $leadAnchor);
$leadLight = $leadLight ?? false;

// Repopulate after a validation bounce so nobody retypes a form they already filled in.
$leadOld = $_SESSION['lead_old'] ?? [];
unset($_SESSION['lead_old']);
$old = static fn(string $k): string => e($leadOld[$k] ?? '');

$phoneNumber = setting('company_phone', '+91 98765 43210');
$waDigits = preg_replace('/\D/', '', setting('company_whatsapp', $phoneNumber));

// Field chrome differs between the dark card and the light one; the structure does not.
$labelClass = $leadLight ? 'text-sm font-medium text-primary-700' : 'text-sm font-medium text-gray-300';
$fieldClass = $leadLight
    ? 'input mt-1'
    : 'input mt-1 border-transparent bg-white text-gray-900 placeholder:text-gray-400';
?>
<div id="<?= e($leadAnchor) ?>" class="scroll-mt-28 <?= $leadLight
    ? 'card bg-white shadow-xl'
    : 'rounded-3xl bg-ink p-6 ring-1 ring-white/10 shadow-xl' ?>">

  <?php require __DIR__ . '/flash-message.php'; ?>

  <?php if ($leadTitle !== ''): ?>
    <h3 class="text-2xl font-bold <?= $leadLight ? 'text-gray-900' : 'text-white' ?>">
      <?= e($leadTitle) ?>
    </h3>
  <?php endif; ?>

  <?php if ($leadEyebrow !== ''): ?>
    <span class="inline-flex rounded-full border border-accent-400/50 px-4 py-1.5 text-sm font-medium text-accent-400 <?= $leadTitle !== '' ? 'mt-4' : '' ?>">
      <?= e($leadEyebrow) ?>
    </span>
  <?php endif; ?>

  <form method="post" action="<?= $leadAction ?>" class="mt-5 space-y-4">
    <?= csrf_field() ?>
    <?php // Marks this POST as a lead so lead_handle() ignores the newsletter form's post. ?>
    <input type="hidden" name="form" value="lead">
    <?php // ['name' => 'value'] or ['name' => ['id' => 'lead-kw', 'value' => '3']] when JS
          // already targets a specific id (solar-calc.js, financing.php). ?>
    <?php foreach ($leadHidden as $hk => $hv): ?>
      <?php $hId = is_array($hv) ? ($hv['id'] ?? 'lead-' . $hk) : 'lead-' . $hk; ?>
      <?php $hVal = is_array($hv) ? ($hv['value'] ?? '') : $hv; ?>
      <input type="hidden" name="<?= e($hk) ?>" id="<?= e($hId) ?>" value="<?= e((string) $hVal) ?>">
    <?php endforeach; ?>

    <div>
      <label for="lead-name" class="<?= $labelClass ?>">Your Name <span class="text-accent-500">*</span></label>
      <input type="text" name="name" id="lead-name" required autocomplete="name"
             maxlength="100" value="<?= $old('name') ?>"
             placeholder="e.g. Jason Samuel" class="<?= $fieldClass ?>">
    </div>

    <div>
      <label for="lead-phone" class="<?= $labelClass ?>">Phone <span class="text-accent-500">*</span></label>
      <input type="tel" name="phone" id="lead-phone" required autocomplete="tel" inputmode="tel"
             pattern="[0-9+ ()-]{10,20}" maxlength="20" value="<?= $old('phone') ?>"
             placeholder="e.g. +91 98765 43210" class="<?= $fieldClass ?>">
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label for="lead-email" class="<?= $labelClass ?>">Email</label>
        <input type="email" name="email" id="lead-email" autocomplete="email"
               maxlength="150" value="<?= $old('email') ?>"
               placeholder="you@example.com" class="<?= $fieldClass ?>">
      </div>
      <div>
        <label for="lead-pincode" class="<?= $labelClass ?>">PIN Code</label>
        <input type="text" name="pincode" id="lead-pincode" inputmode="numeric"
               pattern="[0-9]{6}" maxlength="6" value="<?= $old('pincode') ?>"
               placeholder="e.g. 226001" class="<?= $fieldClass ?>">
      </div>
    </div>

    <?php if ($leadRole): ?>
      <div>
        <label for="lead-role" class="<?= $labelClass ?>"><?= e($leadRole['label'] ?? 'Role') ?></label>
        <select name="<?= e($leadRole['name'] ?? 'role') ?>" id="lead-role" class="<?= $fieldClass ?>">
          <?php foreach ($leadRole['options'] as $value => $label): ?>
            <option value="<?= e((string) $value) ?>"><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>

    <div>
      <label for="lead-message" class="<?= $labelClass ?>"><?= e($leadMessageLabel) ?></label>
      <textarea name="message" id="lead-message" rows="3" maxlength="2000"
                placeholder="<?= e($leadMessagePlaceholder) ?>"
                class="<?= $fieldClass ?>"><?= $old('message') ?></textarea>
    </div>

    <button type="submit" class="btn-primary w-full justify-between bg-accent-500 text-ink hover:bg-accent-400">
      <?= e($leadButton) ?>
      <span class="btn-icon bg-white text-accent-600"><?= icon('send', 'h-4 w-4') ?></span>
    </button>
  </form>

  <?php // Direct channels, for anyone who would rather not fill in a form at all. ?>
  <div class="mt-6 flex items-center gap-3" aria-hidden="true">
    <span class="h-px flex-1 <?= $leadLight ? 'bg-gray-200' : 'bg-white/15' ?>"></span>
    <span class="text-xs <?= $leadLight ? 'text-gray-500' : 'text-gray-400' ?>">or reach us directly</span>
    <span class="h-px flex-1 <?= $leadLight ? 'bg-gray-200' : 'bg-white/15' ?>"></span>
  </div>

  <div class="mt-4 grid gap-3 sm:grid-cols-2">
    <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phoneNumber)) ?>"
       class="inline-flex items-center justify-center gap-2 rounded-full px-5 py-3 text-sm font-semibold transition <?= $leadLight
         ? 'border border-gray-300 text-gray-900 hover:bg-gray-50'
         : 'border border-white/20 text-white hover:bg-white/10' ?>">
      <?= icon('phone', 'h-4 w-4') ?> Call Now
    </a>
    <a href="https://wa.me/<?= e($waDigits) ?>" target="_blank" rel="noopener noreferrer"
       class="inline-flex items-center justify-center gap-2 rounded-full bg-[#25D366] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1eb457]">
      <?= icon('whatsapp', 'h-4 w-4') ?> WhatsApp
    </a>
  </div>

  <?php if ($leadNote !== ''): ?>
    <p class="mt-4 text-center text-xs <?= $leadLight ? 'text-gray-500' : 'text-gray-400' ?>">
      <?= e($leadNote) ?>
    </p>
  <?php endif; ?>
</div>
