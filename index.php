<?php
require_once __DIR__ . '/config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        flash('error', 'Please fill in all fields.');
        redirect('/index.php#consultation');
    }

    $stmt = db()->prepare(
        'INSERT INTO leads (name, phone, email, message) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$name, '', $email, $message]);

    flash('success', 'Thanks! We received your request and will contact you shortly.');
    redirect('/index.php#consultation');
}

$pageTitle = 'Hindustan Vidyut Udyog Solar — Residential Solar Installation';
$transparentHeader = true;

$services = db()->query('SELECT * FROM services ORDER BY sort_order LIMIT 4')->fetchAll();
$products = db()->query('SELECT * FROM products WHERE is_active = 1 ORDER BY sort_order LIMIT 3')->fetchAll();

require __DIR__ . '/components/header.php';
?>

<section id="hero" class="relative m-3 min-h-[calc(100vh-2rem)] rounded-3xl overflow-hidden">
  <div data-hero-bg class="absolute inset-0 bg-cover bg-center scale-110" style="background-image:url('/assets/images/hero-section-1.png')"></div>
  <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-transparent to-transparent"></div>
  <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/10 to-transparent"></div>

  <div class="relative mx-auto container px-6 pt-32 pb-28 md:pt-44 md:pb-36">
    <h1 data-hero-title class="text-4xl md:text-6xl font-extrabold text-white max-w-2xl leading-tight [text-shadow:0_2px_16px_rgba(0,0,0,0.45)]">
      Power Your Home With Solar
    </h1>
    <p data-hero-anim class="mt-5 text-lg text-white/90 max-w-xl [text-shadow:0_1px_8px_rgba(0,0,0,0.5)]">
      Eco-friendly, cost-effective and reliable residential solar installation —
      from free site survey to lifetime support.
    </p>
    <div data-hero-anim class="mt-8 flex flex-wrap gap-4">
      <a href="/contact.php" class="btn-primary">Get a Free Quote →</a>
      <a href="/services.php" class="btn-outline bg-white/90">Our Services</a>
    </div>

    <div class="mt-12 flex flex-wrap gap-4">
      <div data-hero-stat class="card bg-white/95 backdrop-blur px-6 py-4">
        <p class="text-2xl font-bold text-primary-700"><?= e(setting('stat_systems_installed', '1200+')) ?></p>
        <p class="text-sm text-gray-600">Systems Installed</p>
      </div>
      <div data-hero-stat class="card bg-white/95 backdrop-blur px-6 py-4">
        <p class="text-2xl font-bold text-primary-700"><?= e(setting('stat_years_experience', '10+')) ?></p>
        <p class="text-sm text-gray-600">Years Experience</p>
      </div>
      <div data-hero-stat class="card bg-white/95 backdrop-blur px-6 py-4">
        <p class="text-2xl font-bold text-primary-700"><?= e(setting('stat_customer_rating', '4.8/5')) ?></p>
        <p class="text-sm text-gray-600">Customer Rating</p>
      </div>
    </div>
  </div>

  <!-- <div class="absolute bottom-6 left-6 right-6">
    <div class="grid gap-4 md:grid-cols-3">
      <div data-hero-left class="flex items-center gap-4 rounded-2xl bg-white/20 backdrop-blur p-4 shadow-lg border border-white">
        <div>
          <h3 class="font-bold text-gray-900">Advancing Solar Solutions</h3>
          <p class="mt-1 text-sm text-gray-600">
            Hindustan Vidyut Udyog is committed to driving clean energy adoption —
            our installations empower homes to thrive sustainably.
          </p>
        </div>
        <a href="/about.php" aria-label="Watch our story" class="relative shrink-0 h-20 w-20 rounded-xl overflow-hidden bg-gray-800">
          <img src="/assets/images/hero-section-1.png" alt="" class="h-full w-full object-cover opacity-70">
          <span class="absolute inset-0 flex items-center justify-center">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-accent-500/90 text-white shadow ring-4 ring-white/20">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 ml-0.5" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
            </span>
          </span>
        </a>
      </div>

    </div>
  </div> -->

  <div data-hero-right class="md:absolute md:right-0 md:bottom-0 mx-6 mb-6 md:mx-0 md:mb-0 md:w-[min(90%,42rem)] rounded-3xl md:rounded-none md:rounded-tl-4xl bg-white p-3 md:p-0 md:pt-5 md:pl-5 shadow-lg grid gap-3 md:grid-cols-2">
    <div class="rounded-3xl p-5 border border-gray-900 flex flex-col justify-between">
      <p class="text-sm text-gray-600">
        At Hindustan Vidyut Udyog, we power growth and sustainability.
      </p>
      <div class="mt-4 flex items-center gap-6">
        <div>
          <p class="text-3xl font-extrabold text-gray-900"><?= e(setting('stat_systems_installed', '1200+')) ?></p>
          <p class="text-sm font-semibold text-gray-700">Solar Panels<br>Installed</p>
        </div>
        <div class="flex items-center gap-3">
          <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-accent-500/10 text-accent-600">
            <?= icon('shield', 'h-5 w-5') ?>
          </span>
          <p class="text-sm font-semibold text-gray-700">ISO, LEED, Energy<br>Star Certificate</p>
        </div>
      </div>
    </div>

    <div class="rounded-3xl bg-accent-500 p-5 flex flex-col justify-between">
      <h3 class="font-bold text-gray-900 text-lg leading-snug">
        Discover Next-Gen Solar Solutions
      </h3>
      <a href="/services.php" class="btn-outline bg-gray-900 border-gray-900 text-white hover:bg-gray-800 hover:border-gray-800 mt-4 self-start">
        Explore Solutions
      </a>
    </div>
  </div>
</section>

<section class="mx-auto container px-6 py-20">
  <div class="text-center max-w-2xl mx-auto mb-12">
    <h2 class="text-3xl font-bold text-gray-900">Our Services</h2>
    <p class="mt-3 text-gray-600">Everything you need to go solar, handled end to end.</p>
  </div>
  <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
    <?php foreach ($services as $service): ?>
      <div class="card text-center">
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-primary-50 text-primary-600 font-bold">
          <?= strtoupper(substr($service['title'], 0, 1)) ?>
        </div>
        <h3 class="font-semibold text-gray-900"><?= e($service['title']) ?></h3>
        <p class="mt-2 text-sm text-gray-600"><?= e($service['description']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="bg-primary-50/60 py-20">
  <div class="mx-auto container px-6">
    <div class="text-center max-w-2xl mx-auto mb-12">
      <h2 class="text-3xl font-bold text-gray-900">Featured Products</h2>
      <p class="mt-3 text-gray-600">Quality panels, inverters and storage from trusted manufacturers.</p>
    </div>
    <div class="grid gap-6 md:grid-cols-3">
      <?php foreach ($products as $product): ?>
        <div class="card">
          <div class="h-40 rounded-xl bg-gray-100 mb-4 flex items-center justify-center text-gray-400 text-sm">
            <?= $product['image_path'] ? '<img src="/' . e($product['image_path']) . '" class="h-full w-full object-cover rounded-xl" alt="' . e($product['name']) . '">' : 'No image' ?>
          </div>
          <h3 class="font-semibold text-gray-900"><?= e($product['name']) ?></h3>
          <p class="mt-1 text-sm text-gray-600"><?= e($product['specs']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-10">
      <a href="/products.php" class="btn-outline">View All Products</a>
    </div>
  </div>
</section>

<section id="consultation" class="mx-3 my-20 rounded-3xl bg-[#03130a] pt-6 md:pt-10 overflow-hidden relative scroll-mt-24">
  <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image:url('/assets/images/hero-section-1.png')"></div>
  <div class="relative container mx-auto grid gap-8 md:grid-cols-2 items-center">
    <div class="p-6 md:p-4">
      <span class="inline-flex rounded-full border border-lime-400/50 text-lime-400 text-sm font-medium px-4 py-1.5">
        Powered by Trust and Results
      </span>
      <h2 class="mt-6 text-3xl md:text-4xl font-extrabold text-white leading-tight">
        Ready to switch to solar?<br>Let's start the journey.
      </h2>
      <p class="mt-4 text-gray-300 max-w-md">
        With <span class="font-semibold text-white">Hindustan Vidyut Udyog</span>, solar isn't complicated.
        It's reliable, intelligent, and built to perform. Let's bring clean energy to life together.
      </p>
      <a href="#consultation" class="mt-6 inline-flex rounded-full bg-white text-gray-900 font-semibold px-6 py-3 hover:bg-gray-100 transition">
        Start The Journey
      </a>
    </div>

    <div class="card rounded-b-none max-w-4xl ">
      <?php require __DIR__ . '/components/flash-message.php'; ?>
      <h3 class="text-2xl font-bold text-gray-900">Get Your Free Consultation</h3>
      <form method="post" action="/index.php#consultation" class="mt-5 space-y-4">
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
        <button type="submit" class="rounded-full bg-lime-400 text-[#03130a] font-semibold px-6 py-3 hover:bg-lime-300 transition">
          Free Consultation
        </button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>
