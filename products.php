<?php
require_once __DIR__ . '/config/helpers.php';
$pageTitle = 'Products — Hindustan Vidyut Udyog Solar';

$products = db()->query('SELECT * FROM products WHERE is_active = 1 ORDER BY sort_order')->fetchAll();
$categories = array_unique(array_column($products, 'category'));

require __DIR__ . '/components/header.php';
?>

<section class="bg-primary-50/60 py-16">
  <div class="mx-auto container px-6 text-center">
    <h1 class="text-4xl font-bold text-gray-900">Products</h1>
    <p class="mt-3 text-gray-600 max-w-2xl mx-auto">Panels, inverters and battery storage for every home.</p>
  </div>
</section>

<section class="mx-auto container px-6 py-16">
  <div class="flex flex-wrap gap-3 justify-center mb-10" id="category-filters">
    <button class="filter-btn badge bg-primary-600 text-white" data-category="all">All</button>
    <?php foreach ($categories as $category): ?>
      <button class="filter-btn badge bg-gray-100 text-gray-700" data-category="<?= e($category) ?>">
        <?= e(ucfirst($category)) ?>
      </button>
    <?php endforeach; ?>
  </div>

  <div class="grid gap-6 md:grid-cols-3" id="product-grid">
    <?php foreach ($products as $product): ?>
      <div class="card product-card" data-category="<?= e($product['category']) ?>">
        <div class="h-40 rounded-xl bg-gray-100 mb-4 flex items-center justify-center text-gray-400 text-sm overflow-hidden">
          <?= $product['image_path'] ? '<img src="/' . e($product['image_path']) . '" class="h-full w-full object-cover" alt="' . e($product['name']) . '">' : 'No image' ?>
        </div>
        <span class="badge bg-primary-50 text-primary-700 mb-2"><?= e(ucfirst($product['category'])) ?></span>
        <h3 class="font-semibold text-gray-900"><?= e($product['name']) ?></h3>
        <p class="mt-1 text-sm text-gray-600"><?= e($product['description']) ?></p>
        <p class="mt-2 text-sm font-medium text-primary-700"><?= e($product['specs']) ?></p>
        <a href="/contact.php?product=<?= urlencode($product['name']) ?>" class="btn-outline w-full mt-4 text-sm">Enquire</a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<script>
document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const category = btn.dataset.category;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('bg-primary-600', 'text-white'));
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.add('bg-gray-100', 'text-gray-700'));
    btn.classList.add('bg-primary-600', 'text-white');
    btn.classList.remove('bg-gray-100', 'text-gray-700');

    document.querySelectorAll('.product-card').forEach(card => {
      card.style.display = (category === 'all' || card.dataset.category === category) ? '' : 'none';
    });
  });
});
</script>

<?php require __DIR__ . '/components/footer.php'; ?>
