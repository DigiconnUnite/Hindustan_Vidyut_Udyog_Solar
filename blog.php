<?php
require_once __DIR__ . '/config/helpers.php';
$pageTitle = 'Solar Blog — Subsidy, Savings & Rooftop Guides | HVU Solar';
$metaDescription = 'Practical guides on PM Surya Ghar subsidy, net metering, system sizing and what rooftop solar actually saves an Indian household.';

$posts = require __DIR__ . '/data/blog-posts.php';
usort($posts, fn($a, $b) => strcmp($b['published_at'], $a['published_at']));

$bannerTitle = 'Blog';
$bannerSubtitle = 'Tips, guides and updates from the HVU Solar team.';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>
</section>

<section class="mx-auto container px-6 py-16">
  <div class="grid gap-8 md:grid-cols-3 custom-blog-card-wrapper">
    <?php foreach ($posts as $post): ?>
      <a href="/blog-details.php?slug=<?= urlencode($post['slug']) ?>" class="card shimmer group overflow-hidden p-3 flex flex-col border border-gray-900 shadow-none hover:bg-primary-50 transition-colors">
        <div class="relative aspect-video rounded-2xl bg-gray-100 overflow-hidden">
          <img src="<?= e($post['image']) ?>" class="h-full w-full object-cover" alt="<?= e($post['title']) ?>">
          <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full bg-white/95 backdrop-blur px-3 py-1.5 text-xs font-semibold text-primary-700 shadow-sm">
            <?= icon('calendar', 'h-3.5 w-3.5') ?>
            <?= e(date('M j, Y', strtotime($post['published_at']))) ?>
          </span>
        </div>
        <div class="p-4 pb-2 flex flex-col flex-1 custom-blog-content">
          <h3 class="font-bold text-gray-900 text-lg leading-snug group-hover:text-primary-700 transition-colors"><?= e($post['title']) ?></h3>
          <p class="mt-2 text-sm text-gray-600 flex-1"><?= e($post['excerpt']) ?></p>
          <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4 custom-blog-footer">
            <span class="text-xs font-medium text-gray-500"><?= e($post['author']) ?></span>
            <span class="btn-outline text-sm py-1 custom-blog-read-more" style="padding-left: 10px;">
              Read More
              <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
            </span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>
