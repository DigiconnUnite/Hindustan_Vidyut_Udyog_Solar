<?php
require_once __DIR__ . '/icon.php';
// Closing CTA over the bottom artwork. The scene is bottom-anchored — houses and
// trees along the bottom edge, open sky in the middle — so the copy sits centred in
// that empty sky and the deep bottom padding keeps the houses clear of it.
//
// Copy is overridable per page; the defaults are the home page's.
$ctaEyebrow ??= 'Powered by the Sun, Built for You';
$ctaHeading ??= 'Turn your rooftop into a power plant';
$ctaBody ??= 'Free site survey, PM Surya Ghar subsidy handled end to end, and a system sized to your
        actual bill — not your roof size. Most homes cut their electricity bill by 70–90%.';
?>
<section class="relative isolate overflow-hidden pt-20 pb-[clamp(9rem,28vw,24rem)]">
  <div class="absolute inset-0 -z-10 bg-[url('/assets/images/bg/bottom-bg.png')] bg-[length:100%_auto] bg-bottom bg-no-repeat"
       role="presentation"></div>

  <!-- Fades the artwork's hard top edge into the page above it. -->
  <div class="absolute inset-x-0 top-0 -z-10 h-32 bg-gradient-to-b from-white to-transparent"></div>

  <div class="mx-auto container px-6">
    <div class="mx-auto max-w-2xl text-center">
      <span class="inline-flex rounded-full border border-gray-900 bg-white/70 px-4 py-1.5 text-sm font-medium text-primary-700 backdrop-blur">
        <?= e($ctaEyebrow) ?>
      </span>
      <h2 class="mt-6 text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight">
        <?= e($ctaHeading) ?>
      </h2>
      <p class="mt-4 text-gray-700">
        <?= e($ctaBody) ?>
      </p>
      <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
        <a href="/contact.php" class="btn-primary bg-accent-500 text-ink hover:bg-accent-400 extra-padding">
          Book a Free Site Survey
          <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
        </a>
        <a href="/solar-calculator.php" class="btn-outline bg-white/70 backdrop-blur extra-padding">
          Calculate My Savings
          <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
        </a>
      </div>
    </div>
  </div>
</section>
