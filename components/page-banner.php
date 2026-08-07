<?php
/**
 * Breadcrumb page banner for inner pages.
 * Expects: $bannerTitle (string), optional $bannerSubtitle (string), optional $bannerExtra (raw HTML), optional $bannerImage (string path).
 */
$bannerImage ??= '/assets/images/page-banner.png';
?>
<section class="relative overflow-hidden bg-cover bg-center py-24 md:py-32" style="background-image:url('<?= e($bannerImage) ?>')">
  <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-transparent to-transparent"></div>
  <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/10 to-transparent"></div>

  <div class="relative mx-auto container px-6">
    <?php if (!empty($bannerExtra)): ?>
      <?= $bannerExtra ?>
    <?php endif; ?>
    <h1 class="mt-2 text-4xl md:text-5xl font-extrabold text-white max-w-2xl leading-tight"><?= e($bannerTitle) ?></h1>
    <?php if (!empty($bannerSubtitle)): ?>
      <p class="mt-4 text-lg text-white/90 max-w-xl"><?= e($bannerSubtitle) ?></p>
    <?php endif; ?>
    <nav class="mt-5 flex items-center gap-2 text-sm text-white/80">
      <a href="/index.php" class="hover:text-white">Home</a>
      <span>/</span>
      <span class="text-white font-medium"><?= e($bannerTitle) ?></span>
    </nav>
  </div>
</section>
