<?php
require_once __DIR__ . '/config/helpers.php';

// Applications land in leads with source='career' so the existing admin inbox
// picks them up — a separate applicants table isn't worth it at this volume.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $back = '/careers.php#apply';
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $note = trim($_POST['message'] ?? '');

    if ($name === '' || $phone === '') {
        flash('error', 'Name and phone are required.');
        redirect($back);
    }

    $message = 'Application for: ' . ($role !== '' ? $role : 'Any open role')
        . ($note !== '' ? "\n\n" . $note : '');

    db()->prepare('INSERT INTO leads (name, phone, email, message, source) VALUES (?, ?, ?, ?, ?)')
        ->execute([$name, $phone, $email ?: null, $message, 'career']);

    flash('success', 'Thanks for applying. If your profile fits, our team will be in touch.');
    redirect($back);
}

$jobs = db()->query('SELECT * FROM job_openings WHERE is_active = 1 ORDER BY sort_order')->fetchAll();

$pageTitle = 'Careers — Join Our Solar Team in Gurgaon | HVU Solar';
$metaDescription = 'Open roles at Hindustan Vidyut Udyog Solar — installation technicians, survey engineers, sales and subsidy coordinators in Gurgaon, Haryana.';

$jsonLd = array_map(fn($j) => [
    '@context' => 'https://schema.org',
    '@type' => 'JobPosting',
    'title' => $j['title'],
    'description' => $j['description'],
    'employmentType' => strtoupper(str_replace('-', '_', (string) $j['employment_type'])),
    'datePosted' => $j['posted_on'],
    'hiringOrganization' => [
        '@type' => 'Organization',
        'name' => 'Hindustan Vidyut Udyog Solar',
        'sameAs' => APP_URL,
    ],
    'jobLocation' => [
        '@type' => 'Place',
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Gurgaon',
            'addressRegion' => 'Haryana',
            'addressCountry' => 'IN',
        ],
    ],
], $jobs);

$bannerTitle = 'Careers';
$bannerSubtitle = 'Build the grid that replaces the old one.';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="mx-auto container px-6 py-16">
  <div class="grid gap-10 lg:grid-cols-3">
    <div class="lg:col-span-2">
      <h2 class="text-3xl font-bold text-gray-900">Open positions</h2>
      <p class="mt-3 text-gray-600">
        We install rooftop solar across Delhi NCR. The work is hands-on, the systems are real, and
        every install measurably cuts someone's bill.
      </p>

      <div class="mt-8 space-y-4">
        <?php if (!$jobs): ?>
          <p class="card border border-gray-900 shadow-none text-gray-600">
            No openings listed right now — send your profile through the form and we'll keep it on file.
          </p>
        <?php endif; ?>

        <?php foreach ($jobs as $j): ?>
          <details class="card group border border-gray-900 shadow-none">
            <summary class="flex cursor-pointer items-start justify-between gap-4">
              <div>
                <h3 class="text-lg font-bold text-gray-900"><?= e($j['title']) ?></h3>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                  <?php foreach (array_filter([$j['department'], $j['location'], $j['employment_type'], $j['experience']]) as $tag): ?>
                    <span class="badge bg-primary-50 text-primary-700"><?= e($tag) ?></span>
                  <?php endforeach; ?>
                </div>
              </div>
              <span class="mt-1 shrink-0 text-primary-700 transition-transform group-open:rotate-90"><?= icon('arrow-right', 'h-5 w-5') ?></span>
            </summary>
            <p class="mt-4 text-sm leading-relaxed text-gray-600"><?= e($j['description']) ?></p>
            <a href="#apply" data-role="<?= e($j['title']) ?>" class="apply-link btn-outline mt-5 text-sm py-1">
              Apply for this role <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
            </a>
          </details>
        <?php endforeach; ?>
      </div>
    </div>

    <aside class="space-y-6">
      <div class="card border border-gray-900 shadow-none bg-primary-50">
        <h3 class="text-lg font-bold text-gray-900">Why work here</h3>
        <ul class="mt-4 space-y-3 text-sm text-gray-700">
          <?php foreach ([
              'Field training on live installations, not slide decks',
              'Provident fund, ESI and accident cover from day one',
              'Safety gear and tools provided',
              'Performance bonuses tied to completed installs',
              'A trade that is growing, not shrinking',
          ] as $perk): ?>
            <li class="flex items-start gap-2">
              <span class="mt-0.5 text-primary-600"><?= icon('shield', 'h-4 w-4') ?></span>
              <span><?= e($perk) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </aside>
  </div>
</section>

<section id="apply" class="bg-primary-50 py-16 scroll-mt-40">
  <div class="mx-auto container px-6">
    <div class="mx-auto max-w-2xl card border border-gray-900 shadow-none">
      <?php require __DIR__ . '/components/flash-message.php'; ?>
      <h2 class="text-2xl font-bold text-gray-900">Apply</h2>
      <p class="mt-1 text-sm text-gray-600">Tell us which role and a little about yourself. We read every application.</p>
      <form method="post" action="/careers.php#apply" class="mt-6 grid gap-4 sm:grid-cols-2">
        <?= csrf_field() ?>
        <div>
          <label for="c-name" class="text-sm font-medium text-primary-700">Your name *</label>
          <input type="text" id="c-name" name="name" required class="input mt-1">
        </div>
        <div>
          <label for="c-phone" class="text-sm font-medium text-primary-700">Phone *</label>
          <input type="tel" id="c-phone" name="phone" required pattern="[0-9+ ]{10,15}" class="input mt-1">
        </div>
        <div>
          <label for="c-email" class="text-sm font-medium text-primary-700">Email</label>
          <input type="email" id="c-email" name="email" class="input mt-1">
        </div>
        <div>
          <label for="c-role" class="text-sm font-medium text-primary-700">Role</label>
          <select id="c-role" name="role" class="input mt-1">
            <option value="">Any open role</option>
            <?php foreach ($jobs as $j): ?>
              <option value="<?= e($j['title']) ?>"><?= e($j['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="sm:col-span-2">
          <label for="c-message" class="text-sm font-medium text-primary-700">About you</label>
          <textarea id="c-message" name="message" rows="4" placeholder="Experience, qualifications, when you can start..." class="input mt-1"></textarea>
        </div>
        <div class="sm:col-span-2">
          <button type="submit" class="btn-primary bg-accent-500 text-ink hover:bg-accent-400">
            Submit application <span class="btn-icon"><?= icon('send', 'h-4 w-4') ?></span>
          </button>
        </div>
      </form>
      <p class="mt-4 text-xs text-gray-500">
        Prefer email? Send your CV to
        <a href="mailto:<?= e(setting('company_email', 'info@hvusolar.com')) ?>" class="font-medium text-primary-700 hover:text-primary-600"><?= e(setting('company_email', 'info@hvusolar.com')) ?></a>.
      </p>
    </div>
  </div>
</section>

<script>
// Clicking "Apply for this role" preselects that role in the form below.
document.querySelectorAll('.apply-link').forEach(function (link) {
  link.addEventListener('click', function () {
    var select = document.getElementById('c-role');
    if (select) select.value = link.dataset.role;
  });
});
</script>

<?php require __DIR__ . '/components/footer.php'; ?>
