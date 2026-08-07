<?php
require_once __DIR__ . '/icon.php';
$navLinks = [
    '/index.php' => ['Home', 'sun'],
    '/about.php' => ['About', 'users'],
    '/services.php' => ['Services', 'wrench'],
    '/pm-surya-ghar.php' => ['PM Surya Ghar', 'shield'],
    '/products.php' => ['Products', 'package'],
    '/blog.php' => ['Blog', 'clipboard'],
    '/contact.php' => ['Support', 'inbox'],
];
$currentPath = '/' . basename($_SERVER['SCRIPT_NAME']);
if ($currentPath === '/blog-details.php') {
    $currentPath = '/blog.php';
}
$socials = ['facebook' => setting('social_facebook', '#'), 'twitter' => setting('social_twitter', '#'), 'youtube' => setting('social_youtube', '#')];
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
      <a href="/contact.php" class="inline-flex items-center gap-2 rounded-lg border border-primary-700 px-4 py-2 font-medium text-primary-700 hover:bg-primary-50">
        <?= icon('mail', 'h-4 w-4') ?> Contact Us
      </a>
      <a href="/contact.php#grievance" class="inline-flex items-center gap-2 rounded-lg border border-primary-700 px-4 py-2 font-medium text-primary-700 hover:bg-primary-50">
        <?= icon('inbox', 'h-4 w-4') ?> Grievance
      </a>
      <a href="/contact.php" class="inline-flex items-center gap-2 rounded-lg bg-accent-500 py-1.5 pl-4 pr-1.5 font-semibold text-white hover:bg-accent-600">
        Get a Quote
        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-white text-accent-600"><?= icon('arrow-right', 'h-4 w-4') ?></span>
      </a>
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
    <a href="/contact.php" class="mt-2 flex w-full items-center justify-between rounded-lg bg-accent-500 py-1.5 pl-4 pr-1.5 text-sm font-semibold text-white hover:bg-accent-600">Get a Quote
      <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-white text-accent-600"><?= icon('arrow-right', 'h-4 w-4') ?></span>
    </a>
  </nav>
</header>
