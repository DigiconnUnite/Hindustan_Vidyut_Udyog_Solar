<?php
require_once __DIR__ . '/config/helpers.php';
consultation_handle();
$pageTitle = 'Products — Hindustan Vidyut Udyog Solar';

$products = db()->query('SELECT * FROM products WHERE is_active = 1 ORDER BY sort_order')->fetchAll();
$categories = array_unique(array_column($products, 'category'));

$bannerTitle = 'Products';
$bannerSubtitle = 'Panels, inverters and battery storage for every home.';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>
</section>

<section class="mx-auto container px-6 py-16">
  <div class="flex flex-wrap gap-2 justify-center mb-10 rounded-full bg-gray-100 border border-gray-900 p-1.5 w-fit mx-auto" id="category-filters">
    <button class="filter-btn badge px-5 py-2 font-semibold transition-colors bg-accent-500 text-ink" data-category="all">All</button>
    <?php foreach ($categories as $category): ?>
      <button class="filter-btn badge px-5 py-2 font-semibold transition-colors text-gray-600 hover:text-gray-900" data-category="<?= e($category) ?>">
        <?= e(ucfirst($category)) ?>
      </button>
    <?php endforeach; ?>
  </div>

  <div class="grid gap-8 md:grid-cols-3" id="product-grid">
    <?php foreach ($products as $product): ?>
      <a href="/contact.php?product=<?= urlencode($product['name']) ?>" class="card product-card group overflow-hidden p-3 flex flex-col border border-gray-900 shadow-none hover:bg-primary-50 transition-colors" data-category="<?= e($product['category']) ?>">
        <div class="relative h-52 rounded-2xl bg-gray-100 overflow-hidden">
          <img src="<?= $product['image_path'] ? '/' . e($product['image_path']) : 'https://placehold.co/400x160?text=' . urlencode($product['name']) ?>" class="h-full w-full object-cover" alt="<?= e($product['name']) ?>">
          <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full bg-white/95 backdrop-blur px-3 py-1.5 text-xs font-semibold text-primary-700 shadow-sm">
            <?= e(ucfirst($product['category'])) ?>
          </span>
        </div>
        <div class="p-4 pb-2 flex flex-col flex-1">
          <h3 class="font-bold text-gray-900 text-lg leading-snug group-hover:text-primary-700 transition-colors"><?= e($product['name']) ?></h3>
          <p class="mt-2 text-sm text-gray-600 flex-1"><?= e($product['description']) ?></p>
          <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4">
            <span class="text-xs font-medium text-gray-500"><?= e($product['specs']) ?></span>
            <span class="btn-outline text-sm py-1">
              Enquire
              <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
            </span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<script>
function applyCategory(category) {
  document.querySelectorAll('.filter-btn').forEach(b => {
    const on = b.dataset.category === category;
    b.classList.toggle('bg-accent-500', on);
    b.classList.toggle('text-ink', on);
    b.classList.toggle('text-gray-600', !on);
  });
  document.querySelectorAll('.product-card').forEach(card => {
    card.style.display = (category === 'all' || card.dataset.category === category) ? '' : 'none';
  });
}

document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => applyCategory(btn.dataset.category));
});

// Deep link: /products.php?category=panel — falls back to "all" for unknown values.
const wanted = new URLSearchParams(location.search).get('category');
if (wanted && document.querySelector('.filter-btn[data-category="' + CSS.escape(wanted) + '"]')) {
  applyCategory(wanted);
}
</script>

<?php require __DIR__ . '/components/consultation-cta.php'; ?>

<?php require __DIR__ . '/components/footer.php'; ?>
