<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/product-content.php';
require_once __DIR__ . '/config/solar-calc.php';

// One page serves both catalogue products (?id=) and kits (?kit=). Kits come from their
// own table but are normalised into the product row shape, so everything below this
// point renders identically for either.
$kitSlug = trim($_GET['kit'] ?? '');

if ($kitSlug !== '') {
    $stmt = db()->prepare('SELECT * FROM solar_kits WHERE slug = ? AND is_active = 1');
    $stmt->execute([$kitSlug]);
    $kitRow = $stmt->fetch();
    $product = $kitRow ? kit_as_product($kitRow) : null;
} else {
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ? AND is_active = 1');
    $stmt->execute([(int) ($_GET['id'] ?? 0)]);
    $product = $stmt->fetch() ?: null;
}

if (!$product) {
    redirect('/products.php');
}

$kit = $product['_kit'] ?? null;
$detailUrl = product_url($product);

// leads has no product column, so the product name rides in the message. Taken from the
// row we just loaded, not from the POST body, so it cannot be forged.
lead_handle([
    'source' => 'product',
    'back' => $detailUrl . '#enquiry',
    'prepend' => ($kit ? 'Kit: ' : 'Product: ') . $product['name'],
    'success' => 'Thanks! We received your enquiry and will call you shortly.',
]);

$specs = product_specs($product['specs']);
$copy = product_copy($product['category']);

if ($kit) {
    // Other kits of the same type first, then any other kit.
    $relStmt = db()->prepare('SELECT * FROM solar_kits WHERE kit_type = ? AND id != ? AND is_active = 1 ORDER BY sort_order LIMIT 3');
    $relStmt->execute([$kit['kit_type'], $kit['id']]);
    $related = $relStmt->fetchAll();

    if (!$related) {
        $relStmt = db()->prepare('SELECT * FROM solar_kits WHERE id != ? AND is_active = 1 ORDER BY sort_order LIMIT 3');
        $relStmt->execute([$kit['id']]);
        $related = $relStmt->fetchAll();
    }

    $related = array_map('kit_as_product', $related);
} else {
    $relStmt = db()->prepare('SELECT * FROM products WHERE category = ? AND id != ? AND is_active = 1 ORDER BY sort_order LIMIT 3');
    $relStmt->execute([$product['category'], $product['id']]);
    $related = $relStmt->fetchAll();

    // Only three products are seeded, so a category usually has no siblings.
    // Fall back to any other product rather than rendering an empty section.
    if (!$related) {
        $relStmt = db()->prepare('SELECT * FROM products WHERE id != ? AND is_active = 1 ORDER BY sort_order LIMIT 3');
        $relStmt->execute([$product['id']]);
        $related = $relStmt->fetchAll();
    }
}

$phoneNumber = setting('company_phone', '+91 98765 43210');
$waDigits = preg_replace('/\D/', '', setting('company_whatsapp', $phoneNumber));
$waLink = 'https://wa.me/' . $waDigits . '?text=' . rawurlencode('Hi, I am interested in the ' . $product['name'] . '. Please share a quote.');

$productImage = $product['image_path']
    ? '/' . e($product['image_path'])
    : 'https://placehold.co/800x600?text=' . urlencode($product['name']);

// product_reviews is keyed to catalogue products only; kits have no rows to fetch.
$reviews = [];
if (!$kit) {
    $revStmt = db()->prepare('SELECT * FROM product_reviews WHERE product_id = ? AND is_approved = 1 ORDER BY created_at DESC');
    $revStmt->execute([$product['id']]);
    $reviews = $revStmt->fetchAll();
}

$discount = ($product['mrp'] && $product['price'] && $product['mrp'] > $product['price'])
    ? (int) round(100 - ($product['price'] / $product['mrp'] * 100))
    : 0;

$kitTypes = ['ongrid' => 'On-Grid', 'hybrid' => 'Hybrid', 'offgrid' => 'Off-Grid'];
$kitKw = $kit ? rtrim(rtrim(number_format((float) $kit['system_kw'], 1), '0'), '.') : '';
$kitEmi = $kit ? solar_emi((int) $product['price'], (float) setting('finance_rate', '9.5'), 60) : 0;
$kitYears = $kit
    ? solar_payback_years((int) $product['price'], (int) $kit['monthly_units'] * 12 * (float) setting('default_tariff', '8.0'))
    : null;

$pageTitle = $product['name'] . ' — Price, Specs & Warranty | HVU Solar';
$metaDescription = mb_substr(trim((string) $product['description']), 0, 155)
    ?: 'Buy ' . $product['name'] . ' with installation across Delhi NCR.';
$ogImage = $product['image_path'] ? '/' . $product['image_path'] : '/assets/images/hero-image-1.png';

// Product rich result — array_filter drops the keys we have no data for, since
// an empty offers/rating block is worse than none at all.
$jsonLd = [array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product['name'],
    'description' => $product['description'],
    'category' => ucfirst($product['category']),
    'brand' => $product['brand'] ? ['@type' => 'Brand', 'name' => $product['brand']] : null,
    'offers' => $product['price'] ? [
        '@type' => 'Offer',
        'price' => (string) (int) $product['price'],
        'priceCurrency' => 'INR',
        'availability' => $product['in_stock'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url' => APP_URL . $detailUrl,
        'seller' => ['@type' => 'Organization', 'name' => 'Hindustan Vidyut Udyog Solar'],
    ] : null,
    'aggregateRating' => ($product['rating'] && $product['review_count']) ? [
        '@type' => 'AggregateRating',
        'ratingValue' => (string) $product['rating'],
        'reviewCount' => (string) $product['review_count'],
    ] : null,
    'review' => $reviews ? array_map(fn($r) => [
        '@type' => 'Review',
        'author' => ['@type' => 'Person', 'name' => $r['author']],
        'reviewRating' => ['@type' => 'Rating', 'ratingValue' => (string) $r['rating'], 'bestRating' => '5'],
        'reviewBody' => $r['body'],
        'datePublished' => date('Y-m-d', strtotime($r['created_at'])),
    ], $reviews) : null,
]), seo_breadcrumb_schema($product['name'])];

$bannerTitle = $product['name'];
$bannerSubtitle = $product['description'];

require_once __DIR__ . '/components/icon.php';
require __DIR__ . '/components/header.php';
?>

<section class="mx-auto container px-6 py-12 md:py-16">
  <div class="grid gap-10 lg:grid-cols-[1fr_420px] items-start">

    <!-- LEFT: product story -->
    <div>
      <div class="relative aspect-[4/3] rounded-2xl bg-gray-100 overflow-hidden border border-gray-900">
        <img src="<?= $productImage ?>" class="h-full w-full object-cover" alt="<?= e($product['name']) ?>">
        <span class="absolute top-4 left-4 inline-flex items-center gap-1.5 rounded-full bg-white/95 backdrop-blur px-3 py-1.5 text-xs font-semibold text-primary-700 shadow-sm">
          <?= icon('package', 'h-3.5 w-3.5') ?>
          <?= e($kit ? $kitKw . ' kW ' . ($kitTypes[$kit['kit_type']] ?? $kit['kit_type']) . ' Kit' : ucfirst($product['category'])) ?>
        </span>
      </div>

      <h1 class="mt-8 text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight"><?= e($product['name']) ?></h1>

      <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm">
        <?php foreach ($copy['trust'] as $point): ?>
          <span class="inline-flex items-center gap-1.5 text-gray-700">
            <span class="text-primary-600"><?= icon('shield', 'h-4 w-4') ?></span>
            <?= e($point) ?>
          </span>
        <?php endforeach; ?>
      </div>

      <?php if ($product['brand'] || $product['rating']): ?>
        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
          <?php if ($product['brand']): ?>
            <span class="badge bg-primary-50 font-semibold text-primary-700"><?= e($product['brand']) ?></span>
          <?php endif; ?>
          <?php if ($product['rating']): ?>
            <span class="inline-flex items-center gap-1.5">
              <span class="text-accent-500" aria-hidden="true"><?= str_repeat('★', (int) round($product['rating'])) ?></span>
              <span class="font-semibold text-gray-900"><?= e((string) $product['rating']) ?></span>
              <a href="#reviews" class="text-gray-500 hover:text-primary-700"><?= (int) $product['review_count'] ?> reviews</a>
            </span>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if ($product['price']): ?>
        <div class="mt-6 flex flex-wrap items-end gap-4">
          <p class="flex items-baseline gap-3">
            <span class="text-4xl font-extrabold text-gray-900"><?= e(inr((int) $product['price'])) ?></span>
            <?php if ($discount > 0): ?>
              <span class="text-lg text-gray-400 line-through"><?= e(inr((int) $product['mrp'])) ?></span>
              <span class="badge bg-accent-500 font-bold text-ink">
                <?= $kit ? e(inr((int) $kit['subsidy'])) . ' subsidy' : $discount . '% off' ?>
              </span>
            <?php endif; ?>
          </p>
          <?php if (!$kit && !$product['in_stock']): ?>
            <span class="badge bg-gray-100 text-gray-600">Out of stock</span>
          <?php endif; ?>
        </div>
        <?php if ($kit): ?>
          <p class="mt-1.5 text-sm text-gray-500">
            Complete installed system · or <span class="font-semibold text-gray-900"><?= e(inr($kitEmi)) ?>/month</span> for 60 months.
            <?php if ($kit['subsidy'] <= 0): ?>Subsidy does not apply to this system.<?php endif; ?>
          </p>
        <?php else: ?>
          <p class="mt-1.5 text-sm text-gray-500">
            Inclusive of taxes · installation quoted separately after a
            <a href="/contact.php" class="font-medium text-primary-700 hover:text-primary-600">free site survey</a>.
          </p>
        <?php endif; ?>
      <?php endif; ?>

      <p class="mt-5 text-lg text-gray-600 leading-relaxed"><?= e($product['description']) ?></p>

      <?php if ($kit): ?>
        <dl class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
          <?php foreach ([
              ['System size', $kitKw . ' kW'],
              ['Type', $kitTypes[$kit['kit_type']] ?? $kit['kit_type']],
              ['Generates', '~' . number_format((int) $kit['monthly_units']) . ' units/mo'],
              ['Pays back in', $kitYears === null ? '—' : $kitYears . ' yrs'],
          ] as [$label, $value]): ?>
            <div class="rounded-2xl border border-gray-900 bg-primary-50 px-4 py-3">
              <dt class="text-xs uppercase tracking-wide text-gray-500"><?= e($label) ?></dt>
              <dd class="mt-1 font-bold text-gray-900"><?= e($value) ?></dd>
            </div>
          <?php endforeach; ?>
        </dl>
      <?php endif; ?>

      <?php if ($product['datasheet_path'] && is_file(__DIR__ . '/' . $product['datasheet_path'])): ?>
        <a href="/<?= e($product['datasheet_path']) ?>" target="_blank" rel="noopener"
           class="btn-outline mt-6 text-sm py-1">
          Download datasheet (PDF) <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
        </a>
      <?php endif; ?>

      <!-- Specifications. A kit's `specs` are component names, not label/value pairs,
           so they list as a checklist rather than going through product_specs(). -->
      <?php if ($kit): ?>
        <div class="mt-12">
          <h2 class="text-2xl font-bold text-gray-900">What's included</h2>
          <ul class="mt-5 overflow-hidden rounded-2xl border border-gray-900">
            <?php foreach (array_filter(array_map('trim', explode('|', (string) $product['specs']))) as $i => $item): ?>
              <li class="flex items-start gap-3 px-5 py-3.5 <?= $i % 2 ? 'bg-white' : 'bg-primary-50' ?>">
                <span class="mt-0.5 shrink-0 text-primary-600"><?= icon('shield', 'h-4 w-4') ?></span>
                <span class="text-sm font-medium text-gray-900"><?= e($item) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php elseif ($specs): ?>
        <div class="mt-12">
          <h2 class="text-2xl font-bold text-gray-900">Specifications</h2>
          <dl class="mt-5 overflow-hidden rounded-2xl border border-gray-900">
            <?php foreach ($specs as $i => [$label, $value]): ?>
              <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-3.5 <?= $i % 2 ? 'bg-white' : 'bg-primary-50' ?>">
                <dt class="text-sm font-medium text-gray-600"><?= e($label) ?></dt>
                <dd class="text-sm font-semibold text-gray-900"><?= e($value) ?></dd>
              </div>
            <?php endforeach; ?>
          </dl>
        </div>
      <?php endif; ?>

      <!-- Why choose this -->
      <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900"><?= e($copy['benefits_title']) ?></h2>
        <div class="mt-6 grid gap-5 sm:grid-cols-2">
          <?php foreach ($copy['benefits'] as [$ico, $title, $text]): ?>
            <div class="card border border-gray-900 shadow-none ring-0 hover:bg-primary-50 transition-colors">
              <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-accent-400 text-ink">
                <?= icon($ico, 'h-5 w-5') ?>
              </span>
              <h3 class="mt-4 font-bold text-gray-900"><?= e($title) ?></h3>
              <p class="mt-2 text-sm text-gray-600 leading-relaxed"><?= e($text) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Process -->
      <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900">How It Works</h2>
        <ol class="mt-6 space-y-5">
          <?php foreach ($copy['steps'] as $n => [$title, $text]): ?>
            <li class="flex gap-4">
              <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-600 text-sm font-bold text-white"><?= $n + 1 ?></span>
              <div class="pt-1">
                <h3 class="font-bold text-gray-900"><?= e($title) ?></h3>
                <p class="mt-1 text-sm text-gray-600 leading-relaxed"><?= e($text) ?></p>
              </div>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>

      <!-- FAQ: native <details>, no JS -->
      <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900">Frequently Asked Questions</h2>
        <div class="mt-6 space-y-3">
          <?php foreach ($copy['faqs'] as [$question, $answer]): ?>
            <details class="group rounded-2xl border border-gray-900 bg-white px-5 py-4 open:bg-primary-50">
              <summary class="flex cursor-pointer items-center justify-between gap-4 font-semibold text-gray-900 list-none">
                <?= e($question) ?>
                <span class="shrink-0 text-primary-600 transition-transform group-open:rotate-90"><?= icon('arrow-right', 'h-4 w-4') ?></span>
              </summary>
              <p class="mt-3 text-sm text-gray-600 leading-relaxed"><?= e($answer) ?></p>
            </details>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Subsidy CTA band -->
      <div class="mt-12 card bg-accent-400 border border-gray-900 ring-0 shadow-none flex flex-wrap items-center justify-between gap-4">
        <div>
          <h3 class="font-semibold text-gray-900">Eligible for up to 40% government subsidy</h3>
          <p class="mt-1 text-sm text-gray-800">Check what you can claim under the PM Surya Ghar scheme.</p>
        </div>
        <a href="/pm-surya-ghar.php" class="inline-flex items-center gap-2 rounded-full bg-ink py-1.5 pl-5 pr-1.5 text-sm font-semibold text-white hover:bg-primary-700">
          Check Eligibility
          <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white text-ink"><?= icon('arrow-right', 'h-4 w-4') ?></span>
        </a>
      </div>

      <div class="mt-10 border-t border-gray-200 pt-6">
        <a href="/products.php" class="inline-flex items-center gap-2 rounded-full bg-accent-500 py-1.5 pl-1.5 pr-5 text-sm font-semibold text-white hover:bg-accent-600">
          <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white text-accent-600"><?= icon('arrow-left', 'h-4 w-4') ?></span>
          Back to Products
        </a>
      </div>
    </div>

    <!-- RIGHT: sticky lead panel -->
    <aside class="lg:sticky lg:top-44">
      <?php
      $leadAction = $detailUrl . '#enquiry';
      require __DIR__ . '/components/lead-form.php';
      ?>
    </aside>

  </div>
</section>

<?php if ($reviews): ?>
<section id="reviews" class="mx-auto container px-6 pb-16 scroll-mt-40">
  <div class="flex flex-wrap items-end justify-between gap-4">
    <h2 class="text-2xl font-bold text-gray-900">Customer reviews</h2>
    <?php if ($product['rating']): ?>
      <p class="flex items-center gap-2 text-sm">
        <span class="text-lg text-accent-500" aria-hidden="true"><?= str_repeat('★', (int) round($product['rating'])) ?></span>
        <span class="font-bold text-gray-900"><?= e((string) $product['rating']) ?> out of 5</span>
        <span class="text-gray-500">· <?= (int) $product['review_count'] ?> ratings</span>
      </p>
    <?php endif; ?>
  </div>

  <div class="mt-8 grid gap-6 md:grid-cols-2">
    <?php foreach ($reviews as $r): ?>
      <?php // Same dark quote card as the homepage testimonials, so reviews read
            // as one component wherever they appear. ?>
      <figure class="flex h-full flex-col rounded-3xl bg-ink px-8 py-7">
        <span class="text-accent-500 text-lg" aria-label="<?= (int) $r['rating'] ?> out of 5">
          <?= str_repeat('★', (int) $r['rating']) ?><span class="text-white/25"><?= str_repeat('★', 5 - (int) $r['rating']) ?></span>
        </span>
        <blockquote class="mt-4 flex-1 font-medium italic leading-relaxed text-white">
          &ldquo;<?= e($r['body']) ?>&rdquo;
        </blockquote>
        <figcaption class="mt-6 flex items-center gap-4">
          <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-accent-500 font-bold text-ink ring-2 ring-accent-400">
            <?= e(mb_substr($r['author'], 0, 1)) ?>
          </span>
          <span>
            <span class="block font-semibold text-white"><?= e($r['author']) ?></span>
            <span class="block text-sm text-accent-400">
              <?= e($r['location'] ?: 'Verified customer') ?> · <?= e(date('M Y', strtotime($r['created_at']))) ?>
            </span>
          </span>
        </figcaption>
      </figure>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($related): ?>
<section class="bg-primary-50 py-16">
  <div class="mx-auto container px-6">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">You May Also Like</h2>
        <p class="mt-2 text-sm text-gray-600"><?= $kit ? 'Other complete kits from our range.' : 'Other products from our solar range.' ?></p>
      </div>
      <a href="/products.php" class="btn-outline text-sm py-1">
        View All Products <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
      </a>
    </div>
    <div class="grid gap-8 md:grid-cols-3">
      <?php foreach ($related as $rel): ?>
        <?php $relKit = $rel['_kit'] ?? null; ?>
        <a href="<?= e(product_url($rel)) ?>" class="card shimmer group overflow-hidden p-3 flex flex-col border border-gray-900 shadow-none bg-white hover:bg-primary-100 transition-colors">
          <div class="relative h-52 rounded-2xl bg-gray-100 overflow-hidden">
            <img src="<?= $rel['image_path'] ? '/' . e($rel['image_path']) : 'https://placehold.co/400x160?text=' . urlencode($rel['name']) ?>" class="h-full w-full object-cover" alt="<?= e($rel['name']) ?>">
            <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full bg-white/95 backdrop-blur px-3 py-1.5 text-xs font-semibold text-primary-700 shadow-sm">
              <?= e($relKit ? rtrim(rtrim(number_format((float) $relKit['system_kw'], 1), '0'), '.') . ' kW' : ucfirst($rel['category'])) ?>
            </span>
          </div>
          <div class="p-4 pb-2 flex flex-col flex-1">
            <h3 class="font-bold text-gray-900 text-lg leading-snug group-hover:text-primary-700 transition-colors"><?= e($rel['name']) ?></h3>
            <p class="mt-2 text-sm text-gray-600 flex-1"><?= e($rel['description']) ?></p>
            <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4">
              <span class="text-xs font-medium text-gray-500"><?= e($relKit ? inr((int) $rel['price']) : (string) $rel['specs']) ?></span>
              <span class="btn-outline text-sm py-1">
                View
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

<!-- Mobile action bar. Body gets matching bottom padding so it never covers the footer. -->
<div class="lg:hidden fixed inset-x-0 bottom-0 z-30 border-t border-gray-200 bg-white px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] shadow-[0_-4px_16px_rgba(0,0,0,0.08)]">
  <div class="flex items-center gap-2">
    <a href="tel:<?= e($phoneNumber) ?>" aria-label="Call us"
       class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-primary-600 text-primary-700">
      <?= icon('phone', 'h-5 w-5') ?>
    </a>
    <a href="<?= e($waLink) ?>" target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp"
       class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-[#25D366] text-white">
      <?= icon('whatsapp', 'h-5 w-5') ?>
    </a>
    <a href="#enquiry" class="flex-1 inline-flex items-center justify-between gap-2 rounded-lg bg-accent-500 py-1.5 pl-5 pr-1.5 font-semibold text-white hover:bg-accent-600">
      Get a Free Quote
      <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-white text-accent-600"><?= icon('arrow-right', 'h-4 w-4') ?></span>
    </a>
  </div>
</div>
<div class="lg:hidden h-20" aria-hidden="true"></div>

<?php require __DIR__ . '/components/footer.php'; ?>
