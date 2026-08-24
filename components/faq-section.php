<?php
/**
 * Accordion FAQ section, shared by the homepage, financing and PM Surya Ghar pages.
 *
 * Expects:
 *   $faqItems    [question, answer][]  required
 *   $faqTitle    string                optional heading
 *   $faqIntro    string                optional sub-heading
 *   $faqEyebrow  string                optional pill above the heading
 *   $faqFooter   raw HTML              optional block under the list (CTA, link)
 *
 * The caller owns the FAQPage JSON-LD — this only renders markup, so a page with
 * two FAQ blocks doesn't emit two competing schema graphs.
 */
$faqTitle ??= 'Frequently Asked Questions';
$faqIntro ??= '';
$faqEyebrow ??= '';
?>
<section class="mx-auto container px-6 py-20">
  <div class="mx-auto max-w-2xl text-center">
    <?php if ($faqEyebrow !== ''): ?>
      <span class="inline-flex rounded-full border border-gray-900 px-4 py-1.5 text-sm font-medium text-primary-700">
        <?= e($faqEyebrow) ?>
      </span>
    <?php endif; ?>
    <h2 class="mt-5 text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight"><?= e($faqTitle) ?></h2>
    <?php if ($faqIntro !== ''): ?>
      <p class="mt-4 text-gray-600"><?= e($faqIntro) ?></p>
    <?php endif; ?>
  </div>

  <?php
  // Split into two independent columns rather than one grid: in a grid, opening a
  // panel grows its row and shoves the neighbouring answer down. Separate columns
  // let each side expand on its own. Odd counts put the extra item on the left.
  $faqHalf = (int) ceil(count($faqItems) / 2);
  $faqColumns = [array_slice($faqItems, 0, $faqHalf), array_slice($faqItems, $faqHalf)];
  ?>
  <div class="mx-auto mt-12 grid max-w-6xl gap-x-6 gap-y-3 md:grid-cols-2 items-start">
    <?php foreach ($faqColumns as $column): ?>
      <?php if (!$column) { continue; } ?>
      <div class="space-y-3">
        <?php foreach ($column as [$question, $answer]): ?>
          <details class="faq-item group rounded-2xl border border-gray-900 bg-white px-6 open:bg-primary-50/60 transition-colors">
            <summary class="flex cursor-pointer list-none items-start justify-between gap-4 py-5 font-semibold text-gray-900 group-hover:text-primary-700">
              <span><?= e($question) ?></span>
              <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-accent-500 text-ink transition-transform duration-300 group-open:rotate-90">
                <?= icon('arrow-right', 'h-4 w-4') ?>
              </span>
            </summary>
            <p class="pb-5 pr-12 text-sm leading-relaxed text-gray-600"><?= $answer ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($faqFooter)): ?>
    <div class="mx-auto mt-10 max-w-6xl text-center"><?= $faqFooter ?></div>
  <?php endif; ?>
</section>

<script>
// One panel open per column. Scoping to the parent column matters now the list is
// two columns: closing across both would shut a panel the visitor is reading on
// the other side of the page, which looks like a bug.
document.querySelectorAll('.faq-item').forEach(function (item) {
  item.addEventListener('toggle', function () {
    if (!item.open) return;
    item.parentElement.querySelectorAll('.faq-item[open]').forEach(function (other) {
      if (other !== item) other.open = false;
    });
  });
});
</script>
