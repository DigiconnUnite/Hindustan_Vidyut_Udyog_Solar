<?php require_once __DIR__ . '/icon.php'; ?>
<footer class="bg-ink rounded-3xl m-3 text-gray-300 mt-20">
  <div class="mx-auto container px-6 md:px-10 pt-14 pb-8">

    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-8 mb-10">
      <div class="flex items-center gap-3">
        <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-accent-500 text-ink"><?= icon('sun', 'h-6 w-6') ?></span>
        <div>
          <p class="font-bold text-white text-xl leading-tight">HUV - Solar</p>
          <p class="text-xs text-gray-400 tracking-wide">Solar &amp; Green Energy</p>
        </div>
      </div>

      <div class="w-full md:w-auto md:min-w-[380px]">
        <h4 class="font-bold text-white mb-3">Stay Update, Subscribe Now!</h4>
        <form class="flex items-center gap-2 rounded-full border border-accent-400/40 bg-ink p-1.5 pl-5">
          <input type="email" placeholder="Email" class="flex-1 bg-transparent text-sm text-gray-200 placeholder:text-gray-500 focus:outline-none">
          <button type="button" class="btn-primary bg-accent-500 text-ink hover:bg-accent-400">
            Subscribe <span class="btn-icon"><?= icon('send', 'h-4 w-4') ?></span>
          </button>
        </form>
      </div>
    </div>

    <div class="border-t border-white/10"></div>

    <div class="grid gap-10 md:grid-cols-4 py-10">
      <div>
        <p class="font-semibold text-white leading-snug">
          We don't wait for the future.<br>
          At Hindustan Vidyut Udyog, we build it with<br>
          clean, intelligent energy.
        </p>
        <div class="flex items-center gap-3 mt-6">
          <a href="#" aria-label="Facebook" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-500 text-ink hover:bg-accent-400">
            <?= icon('facebook', 'h-4 w-4') ?>
          </a>
          <a href="#" aria-label="Twitter" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-500 text-ink hover:bg-accent-400">
            <?= icon('twitter', 'h-4 w-4') ?>
          </a>
          <a href="#" aria-label="YouTube" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-500 text-ink hover:bg-accent-400">
            <?= icon('youtube', 'h-4 w-4') ?>
          </a>
        </div>
      </div>

      <div>
        <h4 class="font-semibold text-accent-400 mb-4">Quick Links</h4>
        <ul class="space-y-3 text-sm">
          <li><a href="/index.php" class="text-gray-300 hover:text-white">Home</a></li>
          <li><a href="/about.php" class="text-gray-300 hover:text-white">About Us</a></li>
          <li><a href="/services.php" class="text-gray-300 hover:text-white">Services</a></li>
          <li><a href="/products.php" class="text-gray-300 hover:text-white">Products</a></li>
          <li><a href="/blog.php" class="text-gray-300 hover:text-white">Blog</a></li>
          <li><a href="/contact.php" class="text-gray-300 hover:text-white">Support</a></li>
        </ul>
      </div>

      <div>
        <h4 class="font-semibold text-accent-400 mb-4">Admin</h4>
        <ul class="space-y-3 text-sm">
          <li><a href="/admin/login.php" class="text-gray-300 hover:text-white">Staff Login</a></li>
        </ul>
      </div>

      <div>
        <h4 class="font-semibold text-accent-400 mb-4">Contact Information</h4>
        <ul class="space-y-4 text-sm">
          <li class="flex items-start gap-3">
            <span class="mt-0.5 text-accent-400"><?= icon('map-pin', 'h-4 w-4') ?></span>
            <span class="text-white font-medium"><?= e(setting('company_address', 'India')) ?></span>
          </li>
          <li class="flex items-center gap-3">
            <span class="text-accent-400"><?= icon('mail', 'h-4 w-4') ?></span>
            <span class="text-white font-medium"><?= e(setting('company_email', 'info@hvusolar.com')) ?></span>
          </li>
          <li class="flex items-center gap-3">
            <span class="text-accent-400"><?= icon('phone', 'h-4 w-4') ?></span>
            <span class="text-white font-medium"><?= e(setting('company_phone', '+91 98765 43210')) ?></span>
          </li>
        </ul>
      </div>
    </div>

    <div class="border-t border-white/10 pt-6 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-white/50">
      <p>Hindustan Vidyut Udyog, <?= date('Y') ?> &copy; All Rights Reserved</p>
      <p>Developed by <a href="https://digiconnunite.com" target="_blank" rel="noopener" class="hover:text-white/80">Digiconn Unite</a></p>
      <div class="flex items-center gap-4">
        <a href="#" class="hover:text-white/80">Terms &amp; Conditions</a>
        <span>|</span>
        <a href="#" class="hover:text-white/80">Privacy Policy</a>
        <span>|</span>
        <a href="/contact.php" class="hover:text-white/80">FAQ</a>
      </div>
    </div>
  </div>
</footer>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>
