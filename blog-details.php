<?php
require_once __DIR__ . '/config/helpers.php';

$posts = require __DIR__ . '/data/blog-posts.php';
$slug = $_GET['slug'] ?? '';
$post = null;
foreach ($posts as $p) {
    if ($p['slug'] === $slug) {
        $post = $p;
        break;
    }
}

if (!$post) {
    redirect('/blog.php');
}

$pageTitle = $post['title'] . ' | HVU Solar';
$metaDescription = mb_substr((string) $post['excerpt'], 0, 155);
$ogImage = $post['image'] ?? '/assets/images/hero-image-1.png';

// Article rich result. The body is the section prose joined up — schema wants the
// text, not the heading structure.
$jsonLd = [[
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $post['title'],
    'description' => $post['excerpt'],
    'image' => APP_URL . ($post['image'] ?? '/assets/images/hero-image-1.png'),
    'datePublished' => $post['published_at'],
    'dateModified' => $post['published_at'],
    'author' => ['@type' => 'Organization', 'name' => $post['author'] ?? 'HVU Solar Team'],
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'Hindustan Vidyut Udyog Solar',
        'logo' => ['@type' => 'ImageObject', 'url' => APP_URL . '/assets/images/hvul-logo.png'],
    ],
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => seo_canonical()],
    'articleBody' => implode("

", array_column($post['sections'], 1)),
], seo_breadcrumb_schema($post['title'])];
$related = array_values(array_filter($posts, fn($p) => $p['slug'] !== $post['slug']));
usort($related, fn($a, $b) => strcmp($b['published_at'], $a['published_at']));
$related = array_slice($related, 0, 3);

$anchorFor = fn(string $label): string => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $label));

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$postUrl = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/blog-details.php?slug=' . urlencode($post['slug']);
$shareLinks = [
    'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($postUrl),
    'twitter'  => 'https://twitter.com/intent/tweet?url=' . urlencode($postUrl) . '&text=' . urlencode($post['title']),
    'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($postUrl),
    'whatsapp' => 'https://wa.me/?text=' . urlencode($post['title'] . ' ' . $postUrl),
];

require_once __DIR__ . '/components/icon.php';
require __DIR__ . '/components/header.php';
?>

<section class="mx-auto container px-6 py-12">
  <div class="grid gap-10 lg:grid-cols-[260px_1fr]">
    <aside class="hidden lg:block">
      <div class="sticky top-44">
        <p class="text-sm font-semibold text-gray-900 mb-4">On this page</p>
        <ul class="space-y-2 text-sm">
          <?php foreach ($post['sections'] as [$heading, $paragraph]): ?>
            <li>
              <a href="#<?= e($anchorFor($heading)) ?>" class="text-gray-600 hover:text-primary-700"><?= e($heading) ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </aside>

    <article>
      <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight"><?= e($post['title']) ?></h1>

      <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-gray-500">
        <span class="inline-flex items-center gap-1.5">
          <?= icon('calendar', 'h-4 w-4 text-accent-500') ?>
          <?= e(date('M j, Y', strtotime($post['published_at']))) ?>
        </span>
        <span class="inline-flex items-center gap-1.5">
          <?= icon('users', 'h-4 w-4 text-accent-500') ?>
          <?= e($post['author']) ?>
        </span>
      </div>

      <p class="mt-5 text-lg text-gray-600 leading-relaxed"><?= e($post['excerpt']) ?></p>

      <div class="mt-8 aspect-video rounded-2xl bg-gray-100 overflow-hidden">
        <img src="<?= e($post['image']) ?>" class="h-full w-full object-cover" alt="<?= e($post['title']) ?>">
      </div>

      <div class="mt-10 space-y-8">
        <?php foreach ($post['sections'] as [$heading, $paragraph]): ?>
          <div id="<?= e($anchorFor($heading)) ?>" class="scroll-mt-44">
            <h2 class="text-xl font-bold text-gray-900"><?= e($heading) ?></h2>
            <p class="mt-3 text-gray-700 leading-relaxed"><?= e($paragraph) ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="mt-12 card bg-accent-400 border border-gray-900 ring-0 shadow-none flex flex-wrap items-center justify-between gap-4">
        <div>
          <h3 class="font-semibold text-gray-900">Ready to go solar?</h3>
          <p class="mt-1 text-sm text-gray-800">Book a free site survey and get a quote specific to your roof.</p>
        </div>
        <a href="/contact.php" class="inline-flex items-center gap-2 rounded-full bg-ink py-1.5 pl-5 pr-1.5 text-sm font-semibold text-white hover:bg-primary-700">
          Get a Free Quote
          <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white text-ink"><?= icon('arrow-right', 'h-4 w-4') ?></span>
        </a>
      </div>

      <div class="mt-10 flex flex-wrap items-center justify-between gap-4 border-t border-gray-200 pt-6">
        <a href="/blog.php" class="inline-flex items-center gap-2 rounded-full bg-accent-500 py-1.5 pl-1.5 pr-5 text-sm font-semibold text-white hover:bg-accent-600">
          <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white text-accent-600"><?= icon('arrow-left', 'h-4 w-4') ?></span>
          Back to Blog
        </a>

        <div class="flex items-center gap-3">
          <span class="text-sm font-medium text-gray-600">Share:</span>
          <?php foreach ($shareLinks as $network => $url): ?>
            <a href="<?= e($url) ?>" target="_blank" rel="noopener noreferrer"
               aria-label="Share on <?= e(ucfirst($network)) ?>"
               class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-300 text-gray-700 hover:border-accent-500 hover:bg-accent-500 hover:text-white transition-colors">
              <?= icon($network, 'h-4 w-4') ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </article>
  </div>
</section>

<?php if ($related): ?>
<section class="bg-primary-50 py-16">
  <div class="mx-auto container px-6">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">More From the Blog</h2>
        <p class="mt-2 text-sm text-gray-600">Guides and updates from our solar team.</p>
      </div>
      <a href="/blog.php" class="btn-outline text-sm py-1 extra-padding">
        View All Posts <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
      </a>
    </div>
    <div class="grid gap-8 md:grid-cols-3">
      <?php foreach ($related as $rel): ?>
        <a href="/blog-details.php?slug=<?= urlencode($rel['slug']) ?>" class="card shimmer group overflow-hidden p-3 flex flex-col border border-gray-900 shadow-none bg-white hover:bg-primary-100 transition-colors">
          <div class="relative aspect-video rounded-2xl bg-gray-100 overflow-hidden">
            <img src="<?= e($rel['image']) ?>" class="h-full w-full object-cover" alt="<?= e($rel['title']) ?>">
            <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full bg-white/95 backdrop-blur px-3 py-1.5 text-xs font-semibold text-primary-700 shadow-sm">
              <?= icon('calendar', 'h-3.5 w-3.5') ?>
              <?= e(date('M j, Y', strtotime($rel['published_at']))) ?>
            </span>
          </div>
          <div class="p-4 pb-2 flex flex-col flex-1">
            <h3 class="font-bold text-gray-900 text-lg leading-snug group-hover:text-primary-700 transition-colors"><?= e($rel['title']) ?></h3>
            <p class="mt-2 text-sm text-gray-600 flex-1"><?= e($rel['excerpt']) ?></p>
            <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4">
              <span class="text-xs font-medium text-gray-500"><?= e($rel['author']) ?></span>
              <span class="btn-outline text-sm py-1">
                Read More
                <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
              </span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>


<?php require __DIR__ . '/components/footer.php'; ?>
