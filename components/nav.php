<?php
require_once __DIR__ . '/icon.php';
$navLinks = [
    '/index.php' => 'Home',
    '/about.php' => 'About',
    '/services.php' => 'Services',
    '/pm-surya-ghar.php' => 'PM Surya Ghar',
    '/products.php' => 'Products',
    '/blog.php' => 'Blog',
    '/contact.php' => 'Support',
];
$currentPath = '/' . basename($_SERVER['SCRIPT_NAME']);
if ($currentPath === '/blog-details.php') {
    $currentPath = '/blog.php';
}
$transparentHeader ??= false;
?>
<header id="site-header" class="fixed top-0 inset-x-0 z-40 <?= $transparentHeader ? 'p-3' : '' ?>">
  <div id="site-header-inner"
       class="transition-all duration-300 <?= $transparentHeader ? 'bg-transparent  rounded-2xl' : 'bg-white/90 shadow-sm backdrop-blur ' ?>">
    <div class="mx-auto flex container items-center justify-between px-6 py-4">
      <a href="/index.php" data-nav-anim class="flex items-center gap-2 font-bold text-lg header-logo <?= $transparentHeader ? 'text-white' : 'text-primary-700' ?>">
        <div class="flex items-center gap-3">
        <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-accent-500 text-ink"><?= icon('sun', 'h-6 w-6') ?>
          </span>
          <div>
            <p class="font-bold text-xl leading-tight">HUV - Solar</p>
            <p class="text-xs text-gray-400 tracking-wide">Solar &amp; Green Energy</p>
          </div>
        </div>
      </a>

      <nav data-nav-anim class="hidden md:flex items-center gap-8 text-sm font-medium header-links <?= $transparentHeader ? 'text-white/90' : 'text-gray-700' ?>">
        <?php foreach ($navLinks as $href => $label): ?>
          <a href="<?= e($href) ?>"
             class="border-b-2 pb-1 hover:border-accent-400 hover:text-accent-400 <?= $currentPath === $href ? 'border-accent-400 text-accent-400' : 'border-transparent' ?>">
            <?= e($label) ?>
          </a>
        <?php endforeach; ?>
      </nav>

      <a href="/contact.php" data-nav-anim class="btn-primary hidden md:inline-flex text-sm">Get a Quote <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span></a>

      <button id="nav-toggle" data-nav-anim class="md:hidden header-links <?= $transparentHeader ? 'text-white' : 'text-gray-700' ?>" aria-label="Toggle menu">
        <?= icon('menu', 'h-7 w-7') ?>
      </button>
    </div>

    <nav id="nav-mobile" class="hidden md:hidden border-t border-gray-100 px-6 py-4 space-y-3 bg-white">
      <?php foreach ($navLinks as $href => $label): ?>
        <a href="<?= e($href) ?>" class="block font-medium hover:text-primary-600 <?= $currentPath === $href ? 'text-primary-600' : 'text-gray-700' ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
      <a href="/contact.php" class="btn-primary w-full justify-between text-sm mt-2">Get a Quote <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span></a>
    </nav>
  </div>
</header>
<?php if ($transparentHeader): ?>
<script>
(() => {
  const header = document.getElementById('site-header');
  const inner = document.getElementById('site-header-inner');
  const onScroll = () => {
    const scrolled = window.scrollY > 40;
    header.classList.toggle('p-3', !scrolled);
    inner.classList.toggle('bg-transparent', !scrolled);
    inner.classList.toggle('rounded-2xl', !scrolled);
    inner.classList.toggle('bg-white/90', scrolled);
    inner.classList.toggle('backdrop-blur', scrolled);
    inner.classList.toggle('shadow-sm', scrolled);
    header.querySelectorAll('.header-logo').forEach(el => {
      el.classList.toggle('text-white', !scrolled);
      el.classList.toggle('text-primary-700', scrolled);
    });
    header.querySelectorAll('.header-links').forEach(el => {
      el.classList.toggle('text-white', !scrolled);
      el.classList.toggle('text-white/90', !scrolled);
      el.classList.toggle('text-gray-700', scrolled);
    });
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();
</script>
<?php endif; ?>
