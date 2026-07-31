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

$pageTitle = $post['title'] . ' — Hindustan Vidyut Udyog Solar';
$related = array_slice(array_filter($posts, fn($p) => $p['slug'] !== $post['slug']), 0, 2);

require_once __DIR__ . '/components/icon.php';
$bannerTitle = $post['title'];
$bannerExtra = '<span class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-400">'
    . icon('calendar', 'h-4 w-4')
    . e(date('M j, Y', strtotime($post['published_at']))) . ' &middot; ' . e($post['author'])
    . '</span>';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<article class="mx-auto container px-6 py-16 max-w-3xl">
  <div class="h-72 rounded-2xl bg-gray-100 overflow-hidden mb-10">
    <img src="<?= e($post['image']) ?>" class="h-full w-full object-cover" alt="<?= e($post['title']) ?>">
  </div>

  <div class="prose text-gray-700 space-y-5 leading-relaxed">
    <?php foreach (explode("\n\n", $post['body']) as $paragraph): ?>
      <p><?= e($paragraph) ?></p>
    <?php endforeach; ?>
  </div>

  <a href="/blog.php" class="btn-outline pl-1.5 pr-6 mt-10 text-sm">
    <span class="btn-icon"><?= icon('arrow-left', 'h-4 w-4') ?></span> Back to Blog
  </a>
</article>

<?php if ($related): ?>
<section class="bg-gray-50 py-16">
  <div class="mx-auto container px-6">
    <h2 class="text-2xl font-bold text-gray-900 text-center mb-10">More From the Blog</h2>
    <div class="grid gap-6 md:grid-cols-2 max-w-3xl mx-auto">
      <?php foreach ($related as $post): ?>
        <a href="/blog-details.php?slug=<?= urlencode($post['slug']) ?>" class="card hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900"><?= e($post['title']) ?></h3>
          <p class="mt-2 text-sm text-gray-600"><?= e($post['excerpt']) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/components/footer.php'; ?>
