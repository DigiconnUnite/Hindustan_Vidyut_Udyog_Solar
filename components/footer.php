<?php
require_once __DIR__ . '/icon.php';
$footerLinks = [
    '/index.php' => 'Home',
    '/about.php' => 'About Us',
    '/services.php' => 'Services',
    '/pm-surya-ghar.php' => 'PM Surya Ghar',
    '/products.php' => 'Products',
    '/blog.php' => 'Blog',
    '/contact.php' => 'Support',
];
$footerSocials = [
    'facebook' => setting('social_facebook', '#'),
    'twitter' => setting('social_twitter', '#'),
    'youtube' => setting('social_youtube', '#'),
];
?>
<footer class="bg-ink text-gray-300 ">
  <!-- Row 1: brand + subscribe -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 px-6 py-6 border-b border-white/10">
    <a href="/index.php" class="flex items-center gap-3">
      <img src="/assets/images/hvul-logo.png" alt="" class="h-14 w-auto">
      <span class="border-l border-white/20 pl-3">
        <span class="block font-bold text-lg leading-tight text-white">Hindustan Vidyut Udyog</span>
        <span class="block text-xs tracking-wide text-gray-400">Solar &amp; Green Energy</span>
      </span>
    </a>

    <a href="/contact.php" class="inline-flex items-center gap-2 self-start rounded-lg bg-accent-500 py-1.5 pl-4 pr-1.5 text-sm font-semibold text-white hover:bg-accent-600">
      Get a Quote
      <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-white text-accent-600"><?= icon('arrow-right', 'h-4 w-4') ?></span>
    </a>
  </div>

  <!-- Row 2: columns -->
  <div class="grid gap-10 md:grid-cols-4 px-6 py-10">
    <div>
      <p class="font-semibold text-white leading-snug">
        We don't wait for the future.<br>
        At Hindustan Vidyut Udyog, we build it with<br>
        clean, intelligent energy.
      </p>
      <div class="flex items-center gap-2 mt-6">
        <?php foreach ($footerSocials as $name => $url): ?>
          <a href="<?= e($url) ?>" aria-label="<?= e($name) ?>"
             class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-500 text-white hover:bg-accent-600">
            <?= icon($name, 'h-4 w-4') ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div>
      <h4 class="font-semibold text-accent-400 mb-4">Quick Links</h4>
      <ul class="space-y-3 text-sm">
        <?php foreach ($footerLinks as $href => $label): ?>
          <li><a href="<?= e($href) ?>" class="text-gray-300 hover:text-white"><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div>
      <h4 class="font-semibold text-accent-400 mb-4">Contact Information</h4>
      <ul class="space-y-3 text-sm">
        <li class="flex items-start gap-3">
          <span class="mt-0.5 text-accent-400"><?= icon('map-pin', 'h-4 w-4') ?></span>
          <span class="text-white font-medium"><?= e(setting('company_address', 'India')) ?></span>
        </li>
        <li class="flex items-center gap-3">
          <span class="text-accent-400"><?= icon('mail', 'h-4 w-4') ?></span>
          <a href="mailto:<?= e(setting('company_email', 'info@hvusolar.com')) ?>" class="text-white font-medium hover:text-accent-400"><?= e(setting('company_email', 'info@hvusolar.com')) ?></a>
        </li>
        <li class="flex items-center gap-3">
          <span class="text-accent-400"><?= icon('phone', 'h-4 w-4') ?></span>
          <a href="tel:<?= e(setting('company_phone', '+91 98765 43210')) ?>" class="text-white font-medium hover:text-accent-400"><?= e(setting('company_phone', '+91 98765 43210')) ?></a>
        </li>
      </ul>
    </div>

    <div class="rounded-2xl bg-white p-5 shadow-sm">
      <h4 class="font-bold text-gray-900 mb-4">Send Us a Message</h4>
      <form method="post" action="/index.php#consultation" class="space-y-3">
        <?= csrf_field() ?>
        <input type="text" name="name" required placeholder="Your name" class="input">
        <input type="email" name="email" required placeholder="Your email" class="input">
        <textarea name="message" required rows="3" placeholder="How can we help?" class="input"></textarea>
        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-accent-500 py-1.5 pl-4 pr-1.5 text-sm font-semibold text-white hover:bg-accent-600">
          Send Message
          <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-white text-accent-600"><?= icon('send', 'h-4 w-4') ?></span>
        </button>
      </form>
    </div>
  </div>

  <!-- Row 3: legal bar -->
  <div class="border-t border-white/10 px-6 py-5 flex flex-col md:flex-row items-center justify-between gap-3 text-sm text-white/80">
    <p class="md:flex-1">&copy; <?= date('Y') ?> Hindustan Vidyut Udyog Solar. All Rights Reserved.</p>
    <p class="md:shrink-0">Designed &amp; Developed by <a href="https://digiconnunite.com" target="_blank" rel="noopener" class="font-medium hover:text-accent-400">Digiconn Unite</a></p>
    <div class="flex items-center justify-end gap-4 md:flex-1">
      <a href="/terms.php" class="hover:text-white">Terms &amp; Conditions</a>
      <span>|</span>
      <a href="/privacy.php" class="hover:text-white">Privacy Policy</a>
      <span>|</span>
      <a href="/faq.php" class="hover:text-white">FAQ</a>
      <span>|</span>
      <a href="/admin/login.php" class="hover:text-white">Staff Login</a>
    </div>
  </div>
</footer>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>
