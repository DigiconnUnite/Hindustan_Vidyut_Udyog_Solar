<?php
/**
 * Shared layout for legal documents (terms, privacy).
 * Expects: $sections (heading => paragraphs[]), $anchorFor (callable), $lastUpdated (string).
 */
require_once __DIR__ . '/icon.php';
?>
<section class="mx-auto container px-6 py-16">
  <div class="grid gap-10 lg:grid-cols-[260px_1fr]">
    <aside class="hidden lg:block">
      <div class="sticky top-44">
        <p class="text-sm font-semibold text-gray-900 mb-4">On this page</p>
        <ul class="space-y-2 text-sm">
          <?php foreach (array_keys($sections) as $heading): ?>
            <li>
              <a href="#<?= e($anchorFor($heading)) ?>" class="text-gray-600 hover:text-primary-700"><?= e($heading) ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </aside>

    <div>
      <p class="inline-flex items-center gap-2 rounded-full border border-gray-900 bg-primary-50 px-3 py-1.5 text-xs font-medium text-primary-700">
        <?= icon('calendar', 'h-3.5 w-3.5') ?> Last updated <?= e($lastUpdated) ?>
      </p>

      <div class="mt-8 space-y-10">
        <?php $i = 1; foreach ($sections as $heading => $paragraphs): ?>
          <div id="<?= e($anchorFor($heading)) ?>" class="scroll-mt-44">
            <h2 class="text-xl font-bold text-gray-900">
              <span class="text-accent-500"><?= $i++ ?>.</span> <?= e($heading) ?>
            </h2>
            <?php foreach ($paragraphs as $paragraph): ?>
              <p class="mt-3 text-gray-600 leading-relaxed"><?= e($paragraph) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="mt-12 card bg-primary-50 border border-gray-900 ring-0 shadow-none">
        <h3 class="font-semibold text-gray-900">Questions about this document?</h3>
        <p class="mt-2 text-sm text-gray-600">
          Write to us at
          <a href="mailto:<?= e(setting('company_email', 'info@hvusolar.com')) ?>" class="font-medium text-primary-700 hover:text-accent-600"><?= e(setting('company_email', 'info@hvusolar.com')) ?></a>
          or call <a href="tel:<?= e(setting('company_phone', '+91 98765 43210')) ?>" class="font-medium text-primary-700 hover:text-accent-600"><?= e(setting('company_phone', '+91 98765 43210')) ?></a>.
        </p>
      </div>
    </div>
  </div>
</section>
