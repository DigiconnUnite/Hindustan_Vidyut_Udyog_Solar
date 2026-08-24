<?php
require_once __DIR__ . '/config/helpers.php';

consultation_handle();
require_once __DIR__ . '/config/solar-calc.php';

$pageTitle = 'Rooftop Solar Installation in Gurgaon & Delhi NCR | HVU Solar';
$metaDescription = 'Rooftop solar installation with PM Surya Ghar subsidy up to ₹78,000. Free site survey, ALMM-listed panels, EMI options. Serving Gurgaon, Delhi NCR & Faridabad.';

$services = db()->query('SELECT * FROM services ORDER BY sort_order LIMIT 4')->fetchAll();
$products = db()->query('SELECT * FROM products WHERE is_active = 1 ORDER BY sort_order LIMIT 3')->fetchAll();
// Featured kits for the homepage strip. Falls back to the first three by size so
// the section never renders empty if nobody has ticked "featured" in admin.
$homeKits = db()->query('SELECT * FROM solar_kits WHERE is_active = 1 AND is_featured = 1 ORDER BY sort_order LIMIT 3')->fetchAll()
    ?: db()->query('SELECT * FROM solar_kits WHERE is_active = 1 ORDER BY sort_order LIMIT 3')->fetchAll();
$posts = require __DIR__ . '/data/blog-posts.php';
usort($posts, fn($a, $b) => strcmp($b['published_at'], $a['published_at']));
$posts = array_slice($posts, 0, 3);
$testimonials = require __DIR__ . '/data/testimonials.php';

// Homepage FAQ. Answers carry links, so they are rendered as raw HTML by
// components/faq-section.php — keep this list authored here, never user input.
$faqItems = [
    ['How much does rooftop solar cost in Gurgaon?',
     'A turnkey residential system runs roughly ₹60,000 per kW installed. A typical 3 kW home system is about ₹1,80,000 before subsidy and ₹1,02,000 after the full ₹78,000 PM Surya Ghar subsidy. <a href="/solar-calculator.php" class="font-semibold text-primary-700 hover:text-primary-600">Use the calculator</a> for a figure based on your own bill.'],
    ['How much subsidy will I actually get?',
     'Under PM Surya Ghar you get ₹30,000 per kW for the first 2 kW and ₹18,000 for the third, capped at ₹78,000. So 1 kW earns ₹30,000, 2 kW earns ₹60,000, and anything 3 kW or larger earns ₹78,000. It is paid straight into your bank account after commissioning. <a href="/pm-surya-ghar.php" class="font-semibold text-primary-700 hover:text-primary-600">Full scheme details</a>.'],
    ['How much will my electricity bill drop?',
     'Most households see a 70–90% reduction. Fixed charges and meter rent still apply, so bills rarely reach exactly zero. A correctly sized system typically pays for itself in four to six years and then runs for another twenty.'],
    ['How long does installation take?',
     'The physical installation is one to three days. The full process — application, DISCOM approval, installation, net meter and inspection — usually runs three to six weeks. We handle the paperwork at every stage.'],
    ['Will solar work during a power cut?',
     'A standard on-grid system shuts down during an outage for lineworker safety. If you need backup, a hybrid system with battery storage keeps essential circuits running. We quote both so you can compare.'],
    ['What maintenance does it need?',
     'Very little. Rinse the panels every few months to clear dust and check the inverter status light. We offer an annual maintenance contract covering cleaning, electrical checks and performance monitoring.'],
    ['Do you offer EMI or financing?',
     'Yes. Collateral-free solar loans are available for residential systems, with tenures up to seven years. For most homes the EMI lands close to the bill it replaces. <a href="/financing.php" class="font-semibold text-primary-700 hover:text-primary-600">See financing options</a>.'],
    ['What warranty do I get?',
     'Panels carry a 25-year performance warranty, inverters five to ten years depending on model, and our own installation workmanship is warranted for five years.'],
];

// FAQPage schema — the homepage is the page most likely to earn the rich result.
$jsonLd = [[
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(fn($f) => [
        '@type' => 'Question',
        'name' => $f[0],
        // Schema wants the answer text, not the markup we render on the page.
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($f[1])],
    ], $faqItems),
]];

require __DIR__ . '/components/header.php';
?>

<?php $heroSlides = ['/assets/images/hero-image-1.png', '/assets/images/hero-image-2.png', '/assets/images/hero-image-3.png']; ?>
<section id="hero" class="relative overflow-hidden">
  <?php foreach ($heroSlides as $i => $slide): ?>
    <?php // first slide sits in flow and sets the section height; the rest overlay it ?>
    <img data-hero-slide src="<?= e($slide) ?>" alt=""
         class="w-full h-auto transition-opacity duration-1000 <?= $i ? 'absolute inset-0 opacity-0' : 'block' ?>">
  <?php endforeach; ?>
</section>

<section class="mx-auto container px-6 pt-10">
  <div class="grid gap-8 md:grid-cols-2 md:items-center">
  <div>
    <h2 class="text-3xl font-extrabold text-gray-900 leading-tight">
      Powering Homes and Businesses with Clean Solar Energy
    </h2>
    <p class="mt-4 text-gray-600">
      Hindustan Vidyut Udyog designs, installs and maintains rooftop solar systems end to end —
      from the first site survey to subsidy paperwork and long-term AMC. Reliable hardware,
      certified workmanship, and savings that start with your very next bill.
    </p>
  </div>

  <div data-hero-right class="grid gap-3 sm:grid-cols-2">
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
        Explore Solutions <span class="btn-icon bg-accent-500 text-ink"><?= icon('arrow-right', 'h-4 w-4') ?></span>
      </a>
    </div>
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

<section class="relative mx-3 my-3 overflow-hidden rounded-3xl bg-ink bg-cover bg-center px-6 py-16"
         style="background-image:url('/assets/images/pm-surya-ghar-banner.png')">
  <div class="absolute inset-0 bg-ink/85"></div>
  <div class="relative mx-auto container grid gap-8 md:grid-cols-2 items-center">
    <div>
      <span class="inline-flex rounded-full border border-accent-400/50 text-accent-400 text-sm font-medium px-4 py-1.5">
        Government Scheme
      </span>
      <h2 class="mt-5 text-3xl font-extrabold text-white leading-tight">
        Up to ₹78,000 Government Subsidy on Solar
      </h2>
      <p class="mt-4 text-gray-300 max-w-md">
        Under the PM Surya Ghar Muft Bijli Yojana, eligible households get a direct subsidy
        toward their rooftop solar system. We check your eligibility and handle the paperwork.
      </p>
      <a href="/pm-surya-ghar.php" class="btn-primary mt-6 bg-white text-gray-900 hover:bg-gray-100">
        Learn More <span class="btn-icon bg-accent-500 text-ink"><?= icon('arrow-right', 'h-4 w-4') ?></span>
      </a>
    </div>
    <div class="flex flex-wrap gap-4">
      <?php foreach ([
        ['1 kW', '₹30,000'],
        ['2 kW', '₹60,000'],
        ['3 kW+', '₹78,000'],
      ] as [$size, $amount]): ?>
        <div class="card bg-white/95 backdrop-blur px-6 py-4">
          <p class="text-2xl font-bold text-primary-700"><?= e($amount) ?></p>
          <p class="text-sm text-gray-600"><?= e($size) ?> system</p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="mx-auto container px-6 py-20">
  <div class="max-w-3xl mb-12">
    <h2 class="text-3xl font-bold text-gray-900">What We Offer</h2>
    <p class="mt-3 text-gray-600">
      Our service covers every aspect of solar design and installation, from ensuring technical
      precision to creating visual harmony that fits your space. We aim to deliver not just
      clean energy, but a seamless experience that feels reliable, natural, and made to last for years.
    </p>
  </div>
  <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
    <?php foreach ([
      ['Comprehensive Site Assessment', 'We assess location, sunlight, shading, and energy needs to design the most efficient, site-specific solar system.'],
      ['Custom System Design', 'Each solar system is designed to maximize performance, match your property, and meet technical and visual expectations.'],
      ['High-Efficiency Solar Panels', 'We install premium, high-performance panels built for durability, long-term output, and guaranteed energy savings over time.'],
      ['Advanced Inverters & Components', 'Reliable inverters & system components that ensure safety, smart monitoring, & top-level energy conversion efficiency.'],
      ['Professional Installation', 'Our certified technicians ensure fast, safe, and accurate installation with minimal disruption to your property and routine.'],
      ['Grid Integration & Metering', 'We handle all utility approvals and grid connection, making sure your solar system runs legally and efficiently from day one.'],
      ['System Testing & Commissioning', 'Every system is tested for safety, performance, and reliability before final handover and basic training is provided.'],
      ['Ongoing Support & Maintenance', 'We offer maintenance packages including system monitoring, performance checks, cleaning, and technical support for long-term peace of mind.'],
    ] as $i => [$title, $desc]): ?>
      <div class="card border border-gray-900 shadow-none">
        <span class="badge bg-accent-500 text-ink font-bold text-sm h-8 w-8 justify-center px-0"><?= sprintf('%02d', $i + 1) ?></span>
        <h3 class="mt-4 font-semibold text-gray-900"><?= e($title) ?></h3>
        <p class="mt-2 text-sm text-gray-600"><?= e($desc) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="bg-primary-50/60 py-20">
  <div class="mx-auto container px-6">
    <div class="flex flex-wrap items-end justify-between gap-6 mb-12">
      <div class="max-w-2xl">
        <h2 class="text-3xl font-bold text-gray-900">Featured Products</h2>
        <p class="mt-3 text-gray-600">Quality panels, inverters and storage from trusted manufacturers.</p>
      </div>
      <a href="/products.php" class="btn-outline shrink-0">View All Products <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span></a>
    </div>
    <div class="grid gap-8 md:grid-cols-3">
      <?php foreach ($products as $product): ?>
        <a href="/product-details.php?id=<?= (int) $product['id'] ?>" class="card group overflow-hidden p-3 flex flex-col border border-gray-900 shadow-none hover:bg-primary-50 transition-colors">
          <div class="relative h-52 rounded-2xl bg-gray-100 overflow-hidden">
            <img src="<?= $product['image_path'] ? '/' . e($product['image_path']) : 'https://placehold.co/400x160?text=' . urlencode($product['name']) ?>" class="h-full w-full object-cover" alt="<?= e($product['name']) ?>">
            <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full bg-white/95 backdrop-blur px-3 py-1.5 text-xs font-semibold text-primary-700 shadow-sm">
              <?= e(ucfirst($product['category'])) ?>
            </span>
          </div>
          <div class="p-4 pb-2 flex flex-col flex-1">
            <h3 class="font-bold text-gray-900 text-lg leading-snug group-hover:text-primary-700 transition-colors"><?= e($product['name']) ?></h3>
            <p class="mt-2 text-sm text-gray-600 flex-1"><?= e($product['description']) ?></p>
            <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4">
              <span class="text-xs font-medium text-gray-500"><?= e($product['specs']) ?></span>
              <span class="btn-outline text-sm py-1">
                Enquire
                <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
              </span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Solar kits: the way most homeowners actually buy -->
<section class="mx-auto container px-6 py-20">
  <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
    <div class="max-w-xl">
      <h2 class="text-3xl font-bold text-gray-900">Complete Solar Kits</h2>
      <p class="mt-3 text-gray-600">Panels, inverter, structure, cabling and paperwork in one price. Subsidy already applied.</p>
    </div>
    <a href="/solar-kits.php" class="btn-outline">
      All kits <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
    </a>
  </div>

  <div class="grid gap-6 md:grid-cols-3">
    <?php foreach ($homeKits as $kit):
        $kitNet = (int) ($kit['price'] - $kit['subsidy']);
    ?>
      <a href="/solar-kits.php#<?= e($kit['slug']) ?>"
         class="card group flex flex-col border border-gray-900 shadow-none hover:bg-primary-50 transition-colors">
        <div class="flex items-start justify-between gap-3">
          <h3 class="text-xl font-bold text-gray-900 group-hover:text-primary-700"><?= e($kit['name']) ?></h3>
          <span class="badge shrink-0 bg-primary-50 text-primary-700"><?= e(rtrim(rtrim(number_format((float) $kit['system_kw'], 1), '0'), '.')) ?> kW</span>
        </div>
        <p class="mt-1.5 text-sm text-gray-500"><?= e($kit['suits']) ?></p>
        <p class="mt-5 text-3xl font-extrabold text-gray-900"><?= e(inr($kitNet)) ?></p>
        <?php if ($kit['subsidy'] > 0): ?>
          <p class="mt-1 text-sm text-gray-500">
            <span class="line-through"><?= e(inr((int) $kit['price'])) ?></span>
            <span class="ml-1.5 font-semibold text-primary-700">after subsidy</span>
          </p>
        <?php endif; ?>
        <p class="mt-4 text-sm text-gray-600 flex-1">Generates ~<?= number_format((int) $kit['monthly_units']) ?> units a month.</p>
        <span class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-primary-700">
          See what's included <?= icon('arrow-right', 'h-4 w-4') ?>
        </span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- Calculator strip -->
<section class="mx-3 my-3 overflow-hidden rounded-3xl bg-ink">
  <div class="mx-auto container grid gap-8 px-6 py-16 md:grid-cols-2 md:items-center">
    <div>
      <span class="inline-flex rounded-full border border-accent-400/50 px-4 py-1.5 text-sm font-medium text-accent-400">
        Free tool
      </span>
      <h2 class="mt-5 text-3xl md:text-4xl font-extrabold text-white leading-tight">
        What would solar save you?
      </h2>
      <p class="mt-4 max-w-md text-gray-300">
        Enter your monthly electricity bill and see your system size, the PM Surya Ghar subsidy
        you qualify for, your monthly saving and how fast it pays for itself.
      </p>
      <a href="/solar-calculator.php" class="btn-primary mt-7 bg-accent-500 text-ink hover:bg-accent-400">
        Calculate my savings <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
      </a>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <?php foreach ([
          ['₹78,000', 'Maximum central subsidy'],
          ['4–6 yrs', 'Typical payback period'],
          ['70–90%', 'Bill reduction'],
          ['25 yrs', 'Panel performance warranty'],
      ] as [$stat, $label]): ?>
        <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
          <p class="text-2xl font-extrabold text-accent-400"><?= e($stat) ?></p>
          <p class="mt-1 text-sm text-gray-400"><?= e($label) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php
// Split into two rows so each scrolls its own set instead of the same loop twice.
$half = (int) ceil(count($testimonials) / 2);
$rows = [array_slice($testimonials, 0, $half), array_slice($testimonials, $half)];

// The -50% loop only reads as seamless if the half it scrolls past already
// overflows the widest viewport — otherwise a gap opens that no offset can
// cover, which is why row 2 (starting at -50%) showed empty space.
// Card = 32rem + 1.5rem gap = 536px. Fill 2560px, then double for the loop.
$rowWidth = max(1, $half) * 536;
$copies = max(1, (int) ceil(2560 / $rowWidth)) * 2;
?>
<section class="py-20">
  <div class="mx-auto container px-6">
    <div class="max-w-3xl">
      <span class="inline-flex rounded-full border border-gray-900 px-4 py-1.5 text-sm font-medium text-primary-700">
        Powered by Trust and Results
      </span>
      <h2 class="mt-5 text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight">
        What Our Clients Say Matters
      </h2>
      <p class="mt-4 text-gray-600">
        Every <span class="font-semibold text-gray-900">Hindustan Vidyut Udyog</span> installation reflects the
        trust and quality behind our work. Our customers' voices aren't just validation — they guide how we
        keep improving.
      </p>
    </div>
  </div>

  <div class="mt-12 space-y-6">
    <?php foreach ($rows as $i => $row): ?>
      <div class="marquee">
        <div class="flex w-max <?= $i === 1 ? 'animate-marquee-reverse' : 'animate-marquee' ?>"
             style="animation-duration:<?= (int) round($rowWidth * ($copies / 2) / 27) ?>s">
          <?php for ($copy = 0; $copy < $copies; $copy++): ?>
            <?php foreach ($row as $t): ?>
              <figure class="relative mr-6 w-[min(90vw,32rem)] shrink-0 pt-4">
                <div class="flex h-full flex-col rounded-3xl bg-ink pl-16 pr-8 py-7">
                  <blockquote class="text-white font-medium italic leading-relaxed">
                    &ldquo;<?= e($t['quote']) ?>&rdquo;
                  </blockquote>
                  <figcaption class="mt-6 flex items-center gap-4">
                    <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-accent-500 text-ink font-bold ring-2 ring-accent-400">
                      <?php if (!empty($t['photo'])): ?>
                        <img src="<?= e($t['photo']) ?>" alt="" class="h-full w-full object-cover">
                      <?php else: ?>
                        <?= e(mb_substr($t['name'], 0, 1)) ?>
                      <?php endif; ?>
                    </span>
                    <span>
                      <span class="block font-semibold text-white"><?= e($t['name']) ?></span>
                      <span class="block text-sm text-accent-400"><?= e($t['role']) ?></span>
                    </span>
                  </figcaption>
                </div>
              </figure>
            <?php endforeach; ?>
          <?php endfor; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Why HVU: image left, copy right. -->
<section class="mx-auto container px-6 py-20">
  <div class="grid gap-12 md:grid-cols-2 md:items-center">
    <div class="overflow-hidden rounded-3xl border border-gray-900">
      <img src="/assets/images/green-hand-with-solar.png" alt="Rooftop solar installation by Hindustan Vidyut Udyog"
           loading="lazy" class="h-full w-full object-cover aspect-[4/3]">
    </div>

    <div>
      <span class="inline-flex rounded-full border border-gray-900 px-4 py-1.5 text-sm font-medium text-primary-700">
        Why Hindustan Vidyut Udyog
      </span>
      <h2 class="mt-5 text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight">
        One team from site survey to your first zero-rupee bill
      </h2>
      <p class="mt-4 text-gray-600 leading-relaxed">
        Most solar problems start where responsibility changes hands. We keep design, installation,
        DISCOM approval and subsidy paperwork under one roof, so there is never a vendor to chase
        and never a step that falls between two people.
      </p>

      <ul class="mt-6 space-y-3">
        <?php foreach ([
            'Free site survey, system design and quotation',
            'PM Surya Ghar subsidy filed and tracked for you',
            'Tier-1 panels with a 25-year performance warranty',
            'In-house AMC team, not a third-party contractor',
        ] as $point): ?>
          <li class="flex items-start gap-3 text-gray-700">
            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-accent-500 text-ink">
              <?= icon('check', 'h-3.5 w-3.5') ?>
            </span>
            <?= e($point) ?>
          </li>
        <?php endforeach; ?>
      </ul>

      <a href="/about.php" class="btn-outline mt-8">
        More About Us <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
      </a>
    </div>
  </div>
</section>

<?php
$faqEyebrow = 'Questions, Answered';
$faqTitle = 'Everything homeowners ask before going solar';
$faqIntro = 'Costs, subsidy, timelines and what happens after installation. If yours is not here, ask us directly.';
$faqFooter = '<a href="/faq.php" class="btn-outline">Read all FAQs <span class="btn-icon">' . icon('arrow-right', 'h-4 w-4') . '</span></a>';
require __DIR__ . '/components/faq-section.php';
?>

<!-- Blog: plain section, now above the artwork band. -->
<section class="mx-auto container px-6 py-20">
  <div class="flex flex-wrap items-end justify-between gap-6 mb-12">
    <div class="max-w-2xl">
      <h2 class="text-3xl font-bold text-gray-900">Latest From the Blog</h2>
      <p class="mt-3 text-gray-600">Tips, guides and updates from the HVU Solar team.</p>
    </div>
    <a href="/blog.php" class="btn-outline shrink-0">View All Posts <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span></a>
  </div>
  <div class="grid gap-8 md:grid-cols-3">
    <?php foreach ($posts as $post): ?>
      <a href="/blog-details.php?slug=<?= urlencode($post['slug']) ?>" class="card shimmer group overflow-hidden p-3 flex flex-col border border-gray-900 shadow-none hover:bg-primary-50 transition-colors">
        <div class="relative aspect-video rounded-2xl bg-gray-100 overflow-hidden">
          <img src="<?= e($post['image']) ?>" class="h-full w-full object-cover" alt="<?= e($post['title']) ?>">
          <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full bg-white/95 backdrop-blur px-3 py-1.5 text-xs font-semibold text-primary-700 shadow-sm">
            <?= icon('calendar', 'h-3.5 w-3.5') ?>
            <?= e(date('M j, Y', strtotime($post['published_at']))) ?>
          </span>
        </div>
        <div class="p-4 pb-2 flex flex-col flex-1">
          <h3 class="font-bold text-gray-900 text-lg leading-snug group-hover:text-primary-700 transition-colors"><?= e($post['title']) ?></h3>
          <p class="mt-2 text-sm text-gray-600 flex-1"><?= e($post['excerpt']) ?></p>
          <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4">
            <span class="text-xs font-medium text-gray-500"><?= e($post['author']) ?></span>
            <span class="btn-outline text-sm py-1">
              Read More
              <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
            </span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- Closing CTA over the artwork. The scene is bottom-anchored — houses and trees
     along the bottom edge, open sky in the middle — so the copy sits centred in
     that empty sky and the deep bottom padding keeps the houses clear of it. -->
<section class="relative isolate overflow-hidden pt-20 pb-[clamp(9rem,28vw,24rem)]">
  <div class="absolute inset-0 -z-10 bg-[url('/assets/images/bg/bottom-bg.png')] bg-[length:100%_auto] bg-bottom bg-no-repeat"
       role="presentation"></div>

  <!-- Fades the artwork's hard top edge into the page above it. -->
  <div class="absolute inset-x-0 top-0 -z-10 h-32 bg-gradient-to-b from-white to-transparent"></div>

  <div class="mx-auto container px-6">
    <div class="mx-auto max-w-2xl text-center">
      <span class="inline-flex rounded-full border border-gray-900 bg-white/70 px-4 py-1.5 text-sm font-medium text-primary-700 backdrop-blur">
        Powered by the Sun, Built for You
      </span>
      <h2 class="mt-6 text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight">
        Turn your rooftop into a power plant
      </h2>
      <p class="mt-4 text-gray-700">
        Free site survey, PM Surya Ghar subsidy handled end to end, and a system sized to your
        actual bill — not your roof size. Most homes cut their electricity bill by 70–90%.
      </p>
      <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
        <a href="/contact.php" class="btn-primary bg-accent-500 text-ink hover:bg-accent-400">
          Book a Free Site Survey
          <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
        </a>
        <a href="/solar-calculator.php" class="btn-outline bg-white/70 backdrop-blur">
          Calculate My Savings
          <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
        </a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>
