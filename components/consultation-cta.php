<?php
// Consultation CTA — the dark band wrapper around the site's shared lead form
// (components/lead-form.php). The embedding page must call lead_handle() before any
// output; see config/helpers.php.
//
// Set $ctaBare = true to get just the form card without the band and left-hand copy —
// used by index.php to float it over the hero.
$ctaBare = $ctaBare ?? false;
$leadAction = e($_SERVER['REQUEST_URI']) . '#enquiry';
?>
<?php if (!$ctaBare): ?>
<section class="mx-3 my-20 rounded-3xl bg-ink pt-6 md:pt-10 overflow-hidden relative">
  <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image:url('/assets/images/hero-section-1.png')"></div>
  <div class="relative container mx-auto grid gap-8 md:grid-cols-2 items-center">
    <div class="p-6 md:p-4">
      <span class="inline-flex rounded-full border border-accent-400/50 text-accent-400 text-sm font-medium px-4 py-1.5">
        Powered by Trust and Results
      </span>
      <h2 class="mt-6 text-3xl md:text-4xl font-extrabold text-white leading-tight">
        Ready to switch to solar?<br>Let's start the journey.
      </h2>
      <p class="mt-4 text-gray-300 max-w-md">
        With <span class="font-semibold text-white">Hindustan Vidyut Udyog</span>, solar isn't complicated.
        It's reliable, intelligent, and built to perform. Let's bring clean energy to life together.
      </p>
      <a href="#enquiry" class="btn-primary mt-6 bg-white text-gray-900 hover:bg-gray-100">
        Start The Journey <span class="btn-icon bg-accent-500 text-ink"><?= icon('arrow-right', 'h-4 w-4') ?></span>
      </a>
    </div>

    <div class="pb-6 md:pb-10">
<?php endif; ?>

      <?php
      $leadTitle = 'Get Your Free Consultation';
      $leadEyebrow = '';
      require __DIR__ . '/lead-form.php';
      ?>

<?php if (!$ctaBare): ?>
    </div>
  </div>
</section>
<?php endif; ?>
