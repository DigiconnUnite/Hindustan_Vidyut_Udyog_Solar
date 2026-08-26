<?php
require_once __DIR__ . '/icon.php';
$navLinks = [
    '/index.php' => ['Home', 'sun'],
    '/about.php' => ['About', 'users'],
    '/services.php' => ['Services', 'wrench'],
    '/pm-surya-ghar.php' => ['PM Surya Ghar', 'shield'],
    '/products.php' => ['Products', 'package'],
    '/solar-calculator.php' => ['Calculator', 'settings'],
    '/projects.php' => ['Projects', 'layout-dashboard'],
    '/blog.php' => ['Blog', 'clipboard'],
    '/contact.php' => ['Support', 'inbox'],
];
$currentPath = '/' . basename($_SERVER['SCRIPT_NAME']);
// Detail pages have no nav entry of their own; highlight their listing page.
$navAliases = [
    '/blog-details.php' => '/blog.php',
    '/product-details.php' => '/products.php',
    '/solar-kits.php' => '/products.php',
    '/financing.php' => '/solar-calculator.php',
    '/certifications.php' => '/about.php',
    '/service-areas.php' => '/about.php',
    '/careers.php' => '/about.php',
];
$currentPath = $navAliases[$currentPath] ?? $currentPath;
$socials = ['facebook' => setting('social_facebook', '#'), 'twitter' => setting('social_twitter', '#'), 'youtube' => setting('social_youtube', '#')];
$phone = setting('company_phone', '+91 98765 43210');
$waDigits = preg_replace('/\D/', '', setting('company_whatsapp', $phone));
?>
<header id="site-header" class="fixed top-0 inset-x-0 z-40 bg-white shadow-lg">
  <!-- Row 1: utility ribbon -->
  <div id="header-ribbon" class="grid grid-rows-[1fr] overflow-hidden bg-primary-700 text-white text-xs transition-[grid-template-rows,opacity] duration-300">
    <div class="min-h-0 overflow-hidden">
    <div class="flex items-center justify-between gap-4 px-6 py-2">
      <div class="flex items-center gap-5">
        <a href="tel:<?= e(setting('company_phone', '+91 98765 43210')) ?>" class="inline-flex items-center gap-2 hover:text-accent-400">
          <?= icon('phone', 'h-4 w-4') ?><?= e(setting('company_phone', '+91 98765 43210')) ?>
        </a>
        <a href="mailto:<?= e(setting('company_email', 'info@hvusolar.com')) ?>" class="hidden sm:inline-flex items-center gap-2 hover:text-accent-400">
          <?= icon('mail', 'h-4 w-4') ?><?= e(setting('company_email', 'info@hvusolar.com')) ?>
        </a>
      </div>
      <div class="flex items-center gap-2">
        <?php foreach ($socials as $name => $url): ?>
          <a href="<?= e($url) ?>" aria-label="<?= e($name) ?>"
             class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-white/40 hover:bg-white hover:text-primary-700">
            <?= icon($name, 'h-4 w-4') ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    </div>
  </div>

  <!-- Row 2: brand + actions -->
  <div class="flex items-center justify-between gap-4 px-6 py-3">
    <a href="/index.php" data-nav-anim class="flex items-center gap-3">
      <img src="/assets/images/hvul-logo.png" alt="" class="h-14 w-auto">
      <span class="border-l border-gray-200 pl-3">
        <span class="block font-bold text-lg leading-tight text-primary-700">Hindustan Vidyut Udyog</span>
        <span class="block text-xs tracking-wide text-gray-500">Solar &amp; Green Energy</span>
      </span>
    </a>

    <div class="hidden md:flex items-center gap-3 text-sm">
      <a href="tel:<?= e($phone) ?>" class="inline-flex h-11 items-center gap-2 rounded-lg border border-primary-700 px-4 font-medium text-primary-700 hover:bg-primary-50">
        <?= icon('phone', 'h-4 w-4') ?> Call Now
      </a>
      <a href="https://wa.me/<?= e($waDigits) ?>" target="_blank" rel="noopener" class="inline-flex h-11 items-center gap-2 rounded-lg bg-[#25D366] px-4 font-medium text-white hover:bg-[#1ebe5b]">
        <?= icon('whatsapp', 'h-4 w-4') ?> WhatsApp
      </a>
      <a href="/contact.php" class="inline-flex h-11 items-center gap-2 rounded-lg bg-accent-500 pl-4 pr-1.5 font-semibold text-white hover:bg-accent-600">
        Get a Quote
        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-white text-accent-600"><?= icon('arrow-right', 'h-4 w-4') ?></span>
      </a>
      <details class="relative" id="login-menu">
        <summary class="inline-flex h-11 cursor-pointer list-none items-center gap-2 rounded-lg bg-primary-700 px-4 font-semibold text-white hover:bg-primary-800">
          <?= icon('log-in', 'h-4 w-4') ?> Login
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><polyline points="6 9 12 15 18 9"/></svg>
        </summary>
        <div class="absolute right-0 mt-2 w-56 overflow-hidden rounded-lg border border-gray-200 bg-white py-1 shadow-lg">
          <a href="/admin/login.php?role=admin" class="block px-4 py-2.5 text-gray-700 hover:bg-primary-50 hover:text-primary-700">Administrator Login</a>
          <a href="/consumer-login.php" class="block px-4 py-2.5 text-gray-700 hover:bg-primary-50 hover:text-primary-700">Consumer Login</a>
          <a href="/admin/login.php?role=staff" class="block px-4 py-2.5 text-gray-700 hover:bg-primary-50 hover:text-primary-700">Staff Login</a>
        </div>
      </details>
    </div>

    <button id="nav-toggle" data-nav-anim class="md:hidden text-gray-700" aria-label="Toggle menu">
      <?= icon('menu', 'h-7 w-7') ?>
    </button>
  </div>

  <!-- Row 3: icon nav bar -->
  <nav class="hidden md:block border-t border-gray-200 bg-primary-50">
    <div class="flex items-center gap-1 px-6 overflow-x-auto">
      <?php foreach ($navLinks as $href => [$label, $ico]): $active = $currentPath === $href; ?>
        <a href="<?= e($href) ?>"
           class="inline-flex items-center gap-2 whitespace-nowrap  px-4 py-3 text-sm font-medium <?= $active ? 'bg-white text-primary-700 shadow-[inset_0_-3px_0_0_#f5a623]' : 'text-gray-700 hover:text-primary-700 hover:bg-white/70' ?>">
          <?= icon($ico, 'h-4 w-4') ?><?= e($label) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </nav>

  <nav id="nav-mobile" class="hidden md:hidden border-t border-gray-100 px-6 py-4 space-y-3 bg-white">
    <?php foreach ($navLinks as $href => [$label, $ico]): ?>
      <a href="<?= e($href) ?>" class="flex items-center gap-2 font-medium hover:text-primary-600 <?= $currentPath === $href ? 'text-primary-600' : 'text-gray-700' ?>"><?= icon($ico, 'h-4 w-4') ?><?= e($label) ?></a>
    <?php endforeach; ?>
    <div class="mt-2 grid grid-cols-2 gap-2">
      <a href="tel:<?= e($phone) ?>" class="flex h-11 items-center justify-center gap-2 rounded-lg border border-primary-700 px-3 text-sm font-medium text-primary-700"><?= icon('phone', 'h-4 w-4') ?> Call</a>
      <a href="https://wa.me/<?= e($waDigits) ?>" target="_blank" rel="noopener" class="flex h-11 items-center justify-center gap-2 rounded-lg bg-[#25D366] px-3 text-sm font-medium text-white"><?= icon('whatsapp', 'h-4 w-4') ?> WhatsApp</a>
    </div>
    <a href="/contact.php" class="mt-2 flex h-11 w-full items-center justify-between rounded-lg bg-accent-500 pl-4 pr-1.5 text-sm font-semibold text-white hover:bg-accent-600">Get a Quote
      <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-white text-accent-600"><?= icon('arrow-right', 'h-4 w-4') ?></span>
    </a>
    <details>
      <summary class="flex h-11 w-full cursor-pointer list-none items-center justify-center gap-2 rounded-lg bg-primary-700 px-3 text-sm font-semibold text-white"><?= icon('log-in', 'h-4 w-4') ?> Login</summary>
      <a href="/admin/login.php?role=admin" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-primary-50">Administrator Login</a>
      <a href="/consumer-login.php" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-primary-50">Consumer Login</a>
      <a href="/admin/login.php?role=staff" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-primary-50">Staff Login</a>
    </details>
  </nav>
</header>
<style>
  /* Native disclosure: no JS. Hide the default triangle marker. */
  summary::-webkit-details-marker{display:none}
</style>
