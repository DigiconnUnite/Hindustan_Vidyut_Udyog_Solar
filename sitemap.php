<?php
/**
 * XML sitemap. Served at /sitemap.xml via the rewrite in .htaccess so search
 * engines find it at the conventional path without a build step.
 */
require_once __DIR__ . '/config/helpers.php';

header('Content-Type: application/xml; charset=utf-8');

// [path, changefreq, priority]
$urls = [
    ['/', 'weekly', '1.0'],
    ['/solar-calculator.php', 'monthly', '0.9'],
    ['/solar-kits.php', 'weekly', '0.9'],
    ['/products.php', 'weekly', '0.9'],
    ['/pm-surya-ghar.php', 'monthly', '0.9'],
    ['/financing.php', 'monthly', '0.8'],
    ['/projects.php', 'weekly', '0.8'],
    ['/services.php', 'monthly', '0.8'],
    ['/about.php', 'monthly', '0.6'],
    ['/certifications.php', 'monthly', '0.6'],
    ['/service-areas.php', 'monthly', '0.6'],
    ['/blog.php', 'weekly', '0.7'],
    ['/contact.php', 'monthly', '0.7'],
    ['/careers.php', 'weekly', '0.5'],
    ['/faq.php', 'monthly', '0.5'],
    ['/terms.php', 'yearly', '0.2'],
    ['/privacy.php', 'yearly', '0.2'],
];

// Products and blog posts are dynamic; a missing table shouldn't 500 the sitemap.
try {
    foreach (db()->query('SELECT id FROM products WHERE is_active = 1') as $row) {
        $urls[] = ['/product-details.php?id=' . $row['id'], 'monthly', '0.7'];
    }
} catch (Throwable) {
    // table not migrated yet — skip
}

$posts = @include __DIR__ . '/data/blog-posts.php';
if (is_array($posts)) {
    foreach ($posts as $post) {
        if (!empty($post['slug'])) {
            $urls[] = ['/blog-details.php?slug=' . rawurlencode($post['slug']), 'monthly', '0.6'];
        }
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as [$path, $freq, $priority]): ?>
  <url>
    <loc><?= e(APP_URL . $path) ?></loc>
    <changefreq><?= e($freq) ?></changefreq>
    <priority><?= e($priority) ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
