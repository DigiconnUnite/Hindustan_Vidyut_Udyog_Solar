<?php
// Consultation CTA. Posts back to the current page, which must run consultation_handle()
// (config/helpers.php) before any output. Keeps the query string so pages like
// blog-details.php?slug=... return to the same post.
$action = e($_SERVER['REQUEST_URI']) . '#consultation';
?>
<section id="consultation" class="mx-3 my-20 rounded-3xl bg-ink pt-6 md:pt-10 overflow-hidden relative scroll-mt-24">
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
      <a href="#consultation" class="btn-primary mt-6 bg-white text-gray-900 hover:bg-gray-100">
        Start The Journey <span class="btn-icon bg-accent-500 text-ink"><?= icon('arrow-right', 'h-4 w-4') ?></span>
      </a>
    </div>

    <div class="card rounded-b-none max-w-4xl pb-0 ml-auto bg-white  shadow-lg">
      <?php require __DIR__ . '/flash-message.php'; ?>
      <h3 class="text-2xl font-bold text-gray-900">Get Your Free Consultation</h3>
      <form method="post" action="<?= $action ?>" class="mt-5 space-y-4">
        <?= csrf_field() ?>
        <div>
          <label class="text-sm font-medium text-primary-700">Your Name *</label>
          <input type="text" name="name" required placeholder="e.g. Jason Samuel" class="input mt-1">
        </div>
        <div>
          <label class="text-sm font-medium text-primary-700">Email *</label>
          <input type="email" name="email" required placeholder="e.g. hola@dominantsite.com" class="input mt-1">
        </div>
        <div>
          <label class="text-sm font-medium text-primary-700">Message *</label>
          <textarea name="message" required rows="4" placeholder="Tell us what you need here..." class="input mt-1"></textarea>
        </div>
        <button type="submit" class="btn-primary bg-accent-500 text-ink hover:bg-accent-400">
          Free Consultation <span class="btn-icon"><?= icon('send', 'h-4 w-4') ?></span>
        </button>
      </form>
    </div>
  </div>
</section>
