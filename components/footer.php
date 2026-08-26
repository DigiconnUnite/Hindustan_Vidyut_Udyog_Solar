<?php
require_once __DIR__ . '/icon.php';
$footerLinks = [
    '/index.php' => 'Home',
    '/about.php' => 'About Us',
    '/services.php' => 'Services',
    '/products.php' => 'Products',
    '/products.php?category=kit' => 'Solar Kits',
    '/blog.php' => 'Blog',
    '/contact.php' => 'Support',
];
// Tools and proof — the pages that answer "should I?" rather than "what do you sell?"
$footerResources = [
    '/solar-calculator.php' => 'Solar Calculator',
    '/financing.php' => 'Financing & EMI',
    '/projects.php' => 'Our Projects',
    '/certifications.php' => 'Certifications',
    '/service-areas.php' => 'Service Areas',
    '/careers.php' => 'Careers',
];
// PM Surya Ghar subsidises grid-connected (on-grid) rooftop solar only — off-grid and
// hybrid are sold by us but are NOT scheme-eligible, so the subsidy note is on-grid only.
$footerSolarTypes = [
    ['/products.php?category=kit&type=ongrid', 'On-Grid Rooftop Solar', true],
    ['/products.php?category=kit&type=offgrid', 'Off-Grid Solar with Storage', false],
    ['/products.php?category=kit&type=hybrid', 'Hybrid Solar Systems', false],
];
// Payment marks. Drop the official SVG from each brand's media kit into
// assets/images/payments/<file> and it renders automatically; until then the
// label shows as a text badge, so nothing looks broken while assets are pending.
// Third value is an optical-size class: these SVGs have different canvases
// (UPI 432x216, Visa/RuPay/NetBanking square) so a single height renders the
// square ones small — their mark is padded inside an empty square. Sized per
// logo so the marks look equal, not so the boxes are equal.
$footerPayments = [
    ['upi.svg', 'UPI', 'h-10'],
    ['visa.svg', 'Visa', 'h-12'],
    ['mastercard.svg', 'Mastercard', 'h-10'],
    ['rupay.svg', 'RuPay', 'h-12'],
    ['netbanking.svg', 'Net Banking', 'h-12'],
];
$footerSocials = [
    'facebook' => setting('social_facebook', '#'),
    'twitter' => setting('social_twitter', '#'),
    'youtube' => setting('social_youtube', '#'),
];
?>
<footer class="bg-ink  text-gray-300 ">
  <div class="mx-auto">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 px-6 py-6 border-b border-white/10">
    <a href="/index.php" class="flex items-center gap-3">
      <img src="/assets/images/hvul-logo.png" alt="" class="h-14 w-auto">
      <span class="border-l border-white/20 pl-3">
        <span class="block font-bold text-lg leading-tight text-white">Hindustan Vidyut Udyog</span>
        <span class="block text-xs tracking-wide text-gray-400">Solar &amp; Green Energy</span>
      </span>
    </a>

    <?php $activeSocials = array_filter($footerSocials, fn($u) => $u !== '' && $u !== '#'); ?>
    <?php if ($activeSocials): ?>
      <div class="flex items-center gap-4 self-start md:self-auto">
        <?php foreach ($activeSocials as $name => $url): ?>
          <a href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($name)) ?>"
             class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white text-white transition-colors hover:bg-white hover:text-ink">
            <?= icon($name, 'h-4 w-4') ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

   

    <a href="/contact.php" class="inline-flex items-center gap-2 self-start rounded-lg bg-accent-500 py-1.5 pl-4 pr-1.5 text-sm font-semibold text-white hover:bg-accent-600">
      Get a Quote
      <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-white text-accent-600"><?= icon('arrow-right', 'h-4 w-4') ?></span>
    </a>
  </div>

  <!-- Row 2: columns -->
  <div class="grid gap-10 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 px-6 py-10">
    <div>
      <h4 class=" font-bold text-white">Solar, handled end to end</h4>
      <p class="mt-3 text-sm leading-relaxed text-gray-400">
        We design, install and maintain rooftop solar across Gurgaon, Delhi NCR and Faridabad.
        Design, installation, DISCOM approval and PM Surya Ghar subsidy paperwork all stay with
        one in-house team, so there is never a vendor to chase. Most households cut their bill
        by 70–90% and see the system pay for itself in four to six years.
      </p>
    </div>

    <div>
      <h4 class="font-semibold text-accent-400 mb-4">Solar Systems</h4>
      <ul class="space-y-3 text-sm">
        <?php foreach ($footerSolarTypes as [$href, $label, $subsidised]): ?>
          <li>
            <a href="<?= e($href) ?>" class="text-gray-300 hover:text-white">
              <?= e($label) ?>
            </a>
            <?php if ($subsidised): ?>
              <span
                class="ml-1.5 align-middle rounded bg-accent-500/15 px-1.5 py-0.5 text-[10px] font-semibold text-accent-400">Subsidy</span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
        <li class="pt-1">
          <a href="/pm-surya-ghar.php"
            class="inline-flex items-center gap-1.5 font-medium text-accent-400 hover:text-accent-500">
            PM Surya Ghar Subsidy
            <?= icon('arrow-right', 'h-3.5 w-3.5') ?>
          </a>
        </li>
      </ul>
      <p class="mt-3 text-xs text-gray-500 leading-relaxed">Subsidy applies to grid-connected rooftop systems only.</p>
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
      <h4 class="font-semibold text-accent-400 mb-4">Resources</h4>
      <ul class="space-y-3 text-sm">
        <?php foreach ($footerResources as $href => $label): ?>
          <li><a href="<?= e($href) ?>" class="text-gray-300 hover:text-white"><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div>
      <h4 class="font-semibold text-accent-400 mb-4">Contact Information</h4>
      <ul class="space-y-3 text-sm">
        <li class="flex items-start gap-3">
          <span class="mt-0.5 text-accent-400"><?= icon('map-pin', 'h-4 w-4') ?></span>
          <span class="text-white text-wrap font-medium">Hindustan Vidyut Udyog, <br> 810 & 811, 8th floor, <br> SVH 83 Metro Street, <br> Sector 83 , Gurgaon</span>
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
      <a href="/contact.php" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-accent-400 hover:text-accent-500">
        Send us a message
        <?= icon('arrow-right', 'h-4 w-4') ?>
      </a>
    </div>
  </div>

  <!-- Row 3: payment methods -->
  <div class="border-t border-white/10 px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-6">
    <h4 class="shrink-0 text-xs font-semibold uppercase tracking-wide text-gray-400">We Accept</h4>
    <ul class="flex flex-wrap items-center gap-x-6 gap-y-3">
      <?php foreach ($footerPayments as [$file, $label, $size]): ?>
        <?php $svg = '/assets/images/payments/' . $file; ?>
        <li class="flex h-12 items-center">
          <?php if (is_file(__DIR__ . '/..' . $svg)): ?>
            <img src="<?= e($svg) ?>" alt="<?= e($label) ?>" loading="lazy"
                 class="<?= e($size) ?> w-auto max-w-[96px] object-contain">
          <?php else: ?>
            <span class="inline-flex h-8 items-center rounded-md border border-white/25 px-3 text-xs font-semibold text-gray-300"><?= e($label) ?></span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <!-- Row 4: legal bar -->
  <div class="border-t border-white/10 px-6 py-5 flex flex-col md:flex-row md:items-center gap-4 text-sm text-white/80">
    <p class="md:flex-1">&copy; <?= date('Y') ?> <span class="text-accent-400 border-b  border-accent-400">Hindustan Vidyut Udyog Solar</span>. All Rights Reserved.</p>
    <p class="md:text-center">Designed &amp; Developed by
      <a href="https://digiconnunite.com" target="_blank" rel="noopener" class="font-medium hover:text-accent-400">Digiconn Unite</a>
    </p>
    <nav aria-label="Legal" class="md:flex-1 flex flex-wrap items-center gap-x-4 gap-y-2 md:justify-end">
      <a href="/terms.php" class="hover:text-white">Terms &amp; Conditions</a>
      <a href="/privacy.php" class="hover:text-white">Privacy Policy</a>
      <a href="/faq.php" class="hover:text-white">FAQ</a>
      <a href="/admin/login.php" class="hover:text-white">Staff Login</a>
    </nav>
  </div>
  </div>
</footer>

<?php $waDigits = preg_replace('/\D/', '', setting('company_whatsapp', setting('company_phone', '+91 98765 43210'))); ?>
<a href="https://wa.me/<?= e($waDigits) ?>" target="_blank" rel="noopener"
   aria-label="Chat with us on WhatsApp"
   class="fixed bottom-6 right-6 z-50 inline-flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg hover:bg-[#1ebe5b]">
  <span class="wa-pulse" aria-hidden="true"></span>
  <?= icon('whatsapp', 'h-7 w-7 relative') ?>
</a>
<style>
  .wa-pulse{position:absolute;inset:0;border-radius:9999px;background:#25D366;opacity:.7;
    animation:wa-pulse 1.8s cubic-bezier(0,0,.2,1) infinite}
  @keyframes wa-pulse{75%,100%{transform:scale(1.8);opacity:0}}
  @media (prefers-reduced-motion:reduce){.wa-pulse{animation:none}}
</style>

<!-- defer so neither script blocks first paint; app.js waits on GSAP being
     parsed first, which defer preserves (document order, before DOMContentLoaded). -->
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script defer src="/assets/js/app.js"></script>
</body>
</html>
