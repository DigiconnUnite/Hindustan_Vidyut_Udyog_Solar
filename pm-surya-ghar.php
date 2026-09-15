<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/solar-calc.php';
lead_handle(['source' => 'contact']);

$tariff = (float) setting('default_tariff', '8.0');

/**
 * Subsidy slabs, straight from solar_subsidy() so the table can never drift from
 * what the calculator quotes. `note` is editorial; the money is computed.
 */
$slabs = [
    [1.0, 'Small home, modest daytime use', '~120 units/month'],
    [2.0, 'Average 2 BHK household', '~240 units/month'],
    [3.0, 'Larger home — the full subsidy', '~360 units/month'],
    [5.0, 'Villa or high-consumption home', '~600 units/month'],
];

$pageTitle = 'PM Surya Ghar Muft Bijli Yojana 2026 — ₹78,000 Subsidy Guide | HVU Solar';
$metaDescription = 'Complete PM Surya Ghar Muft Bijli Yojana guide: subsidy slabs (₹30,000/kW up to 2 kW, ₹78,000 cap), eligibility, documents, step-by-step application, timelines and how to claim. We handle the paperwork.';

$schemeFaqs = [
    ['What is PM Surya Ghar Muft Bijli Yojana?',
     'It is a central government scheme launched on 15 February 2024 to put rooftop solar on one crore Indian homes. Eligible households get a direct capital subsidy paid into their bank account, and the scheme targets up to 300 free units of electricity a month for households that generate as much as they consume.'],
    ['How much subsidy do I get?',
     'The central financial assistance is ₹30,000 per kW for the first 2 kW, plus ₹18,000 per kW for capacity from 2 kW to 3 kW. The total is capped at ₹78,000. So a 1 kW system earns ₹30,000, a 2 kW system ₹60,000, and a 3 kW or larger system ₹78,000. Systems above 3 kW receive the same ₹78,000 — no more.'],
    ['Is the subsidy deducted from my quotation upfront?',
     'No. You pay the full system cost to the installer, and the subsidy is credited by Direct Benefit Transfer into your bank account after the system is installed, the net meter is fitted and the DISCOM inspection is complete. It typically lands within 30 to 45 days of the redemption request.'],
    ['Who is eligible?',
     'Any Indian citizen who owns a house with a roof suitable for solar, holds a valid electricity connection in their own name, and has not already claimed a subsidy for a rooftop solar system. The scheme is for residential consumers — commercial and industrial premises are not covered.'],
    ['Can tenants or flat owners apply?',
     'A tenant cannot apply alone, since the applicant must own the roof or hold written authorisation to install on it. Housing societies and residential welfare associations can apply for common facilities under a separate provision of the scheme, with subsidy of ₹18,000 per kW up to 500 kW.'],
    ['Does the subsidy apply to off-grid or hybrid systems?',
     'The central subsidy applies to grid-connected rooftop systems with net metering only. A pure off-grid system does not qualify. A hybrid system can qualify provided it is grid-connected and metered — but the battery portion is not subsidised.'],
    ['What if my system is larger than 3 kW?',
     'You can install any size your roof and consumption justify. The subsidy simply stops increasing beyond ₹78,000. A 5 kW or 10 kW system is often the right technical choice; you just fund the capacity above 3 kW yourself.'],
    ['How long does the whole process take?',
     'Typically three to six weeks from application to subsidy credit. Registration and DISCOM feasibility approval take one to two weeks, installation one to three days, and net meter installation plus inspection another one to two weeks. The subsidy then follows within 30 to 45 days.'],
    ['Do I need a specific type of panel?',
     'Yes. The modules must be ALMM-listed (the MNRE Approved List of Models and Manufacturers) and the installation must be done by a registered vendor. Using non-listed hardware disqualifies the subsidy claim entirely, which is why we do not offer it.'],
    ['Can I get a loan for the remaining amount?',
     'Yes. Collateral-free loans are available for residential rooftop systems up to 3 kW at concessional rates under the scheme, and standard solar loans cover larger systems. We finance the post-subsidy amount so you only borrow what you actually pay.'],
    ['What is net metering and do I need it?',
     'Net metering records the surplus your panels export to the grid and credits it against the units you draw at night. It is mandatory for a subsidised system — the DISCOM installs a bi-directional meter as part of the process, and we handle that application.'],
    ['Do states add their own subsidy on top?',
     'Some do. Several states offer a top-up on the central amount, which can meaningfully reduce your net cost. Haryana and Delhi terms change from time to time, so we confirm what currently applies to your address during the site survey.'],
];

// Application steps — shared by the HowTo schema and the rendered list below.
$steps = [
    ['Register on the national portal', 'Create an account at pmsuryaghar.gov.in with your state, DISCOM and consumer number, then verify with your mobile OTP.'],
    ['Submit the subsidy application', 'Fill in your details, upload your latest electricity bill and apply for the rooftop system size you want.'],
    ['Wait for DISCOM feasibility approval', 'Your DISCOM reviews the application and confirms the connection can take a rooftop system. This usually takes one to two weeks.'],
    ['Choose a registered vendor and install', 'Pick a registered vendor — HVU Solar is one — and get the system installed with ALMM-listed modules. Installation takes one to three days.'],
    ['Apply for net metering', 'Submit the net meter application with the installation details. The DISCOM installs a bi-directional meter.'],
    ['Get the inspection and commissioning certificate', 'The DISCOM inspects the installation and issues the commissioning certificate once it passes.'],
    ['Submit bank details and claim', 'Upload your cancelled cheque or passbook on the portal and raise the subsidy redemption request.'],
    ['Receive the subsidy', 'The central subsidy is credited to your bank account by DBT, typically within 30 to 45 days of the claim.'],
];

// FAQPage schema for the scheme questions — the highest-value rich result on the site.
$jsonLd = [[
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(fn($f) => [
        '@type' => 'Question',
        'name' => $f[0],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($f[1])],
    ], $schemeFaqs),
], [
    '@context' => 'https://schema.org',
    '@type' => 'HowTo',
    'name' => 'How to apply for PM Surya Ghar Muft Bijli Yojana subsidy',
    'description' => 'Step-by-step process to register, install and claim the central rooftop solar subsidy.',
    'totalTime' => 'P42D',
    'step' => array_map(fn($i, $s) => [
        '@type' => 'HowToStep',
        'position' => $i + 1,
        'name' => $s[0],
        'text' => $s[1],
    ], array_keys($steps), $steps),
]];

$bannerTitle = 'PM Surya Ghar Muft Bijli Yojana';
$bannerSubtitle = 'Up to ₹78,000 central subsidy on your rooftop solar system — and we handle the entire application.';
$bannerImage = '/assets/images/pm-surya-ghar-banner.png';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<!-- Key numbers at a glance -->
<section class="mx-auto container px-6 -mt-10 relative z-10">
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <?php foreach ([
        ['₹78,000', 'Maximum central subsidy', 'sun'],
        ['300 units', 'Free electricity target per month', 'package'],
        ['1 crore', 'Homes the scheme aims to cover', 'users'],
        ['30–45 days', 'Typical subsidy credit after claim', 'calendar'],
    ] as [$value, $label, $ico]): ?>
      <div class="card border border-gray-900 shadow-none bg-white">
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-700"><?= icon($ico, 'h-5 w-5') ?></span>
        <p class="mt-3 text-2xl font-extrabold text-gray-900"><?= e($value) ?></p>
        <p class="mt-1 text-sm text-gray-600"><?= e($label) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- What the scheme is -->
<section class="mx-auto container px-6 py-16">
  <div class="grid gap-10 lg:grid-cols-[1fr_380px] items-start">
    <div>
      <h2 class="text-3xl font-bold text-gray-900">What is PM Surya Ghar Yojana?</h2>
      <p class="mt-4 text-gray-600 leading-relaxed">
        PM Surya Ghar: Muft Bijli Yojana is the Government of India's flagship rooftop solar scheme,
        launched on 15 February 2024 with an outlay of ₹75,021 crore. It aims to install rooftop solar
        on one crore households by 2027.
      </p>
      <p class="mt-4 text-gray-600 leading-relaxed">
        Eligible households receive a direct capital subsidy — up to ₹78,000 — credited straight to
        their bank account after installation. Combined with net metering, a correctly sized system
        can cut a household's electricity bill by 70–90%, and the scheme targets up to
        <strong class="text-gray-900">300 free units a month</strong> for homes that generate as much
        as they consume.
      </p>
      <p class="mt-4 text-gray-600 leading-relaxed">
        The subsidy is for <strong class="text-gray-900">grid-connected residential rooftop systems</strong>.
        Off-grid systems do not qualify, and commercial or industrial premises are covered by different
        schemes such as accelerated depreciation.
      </p>

      <div class="mt-8 flex flex-wrap gap-3">
        <a href="/solar-calculator.php" class="btn-primary bg-accent-500 text-ink hover:bg-accent-400">
          Check my subsidy <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
        </a>
        <a href="https://pmsuryaghar.gov.in/" target="_blank" rel="noopener" class="btn-outline">
          Official portal <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
        </a>
      </div>
    </div>

    <aside class="card border border-gray-900 shadow-none bg-primary-50">
      <h3 class="text-lg font-bold text-gray-900">Scheme at a glance</h3>
      <dl class="mt-4 divide-y divide-primary-100 text-sm">
        <?php foreach ([
            ['Launched', '15 February 2024'],
            ['Ministry', 'New & Renewable Energy (MNRE)'],
            ['Outlay', '₹75,021 crore'],
            ['Target', '1 crore households by 2027'],
            ['Applies to', 'Grid-connected residential rooftop'],
            ['Payment mode', 'Direct Benefit Transfer (DBT)'],
            ['Portal', 'pmsuryaghar.gov.in'],
            ['Helpline', '15555'],
        ] as [$k, $v]): ?>
          <div class="flex justify-between gap-4 py-2.5">
            <dt class="text-gray-600"><?= e($k) ?></dt>
            <dd class="text-right font-semibold text-gray-900"><?= e($v) ?></dd>
          </div>
        <?php endforeach; ?>
      </dl>
    </aside>
  </div>
</section>

<!-- Subsidy slabs -->
<section class="bg-primary-50/60 py-16">
  <div class="mx-auto container px-6">
    <div class="mx-auto max-w-2xl text-center">
      <h2 class="text-3xl font-bold text-gray-900">How much subsidy will you get?</h2>
      <p class="mt-3 text-gray-600">
        ₹30,000 per kW for the first 2 kW, then ₹18,000 per kW for the third — capped at ₹78,000.
        Paid by DBT into your bank account after commissioning.
      </p>
    </div>

    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      <?php foreach ($slabs as [$kw, $note, $generates]):
          $subsidy = solar_subsidy($kw);
          $gross = (int) round($kw * SOLAR_COST_PER_KW);
          $capped = $subsidy >= 78000;
      ?>
        <div class="card border border-gray-900 shadow-none <?= $capped && $kw == 3.0 ? 'ring-2 ring-accent-500 relative' : '' ?>">
          <?php if ($capped && $kw == 3.0): ?>
            <span class="absolute -top-3 left-6 badge bg-accent-500 text-ink font-bold">Best value</span>
          <?php endif; ?>
          <p class="text-sm font-semibold text-primary-700"><?= e(rtrim(rtrim(number_format($kw, 1), '0'), '.')) ?> kW system</p>
          <p class="mt-2 text-3xl font-extrabold text-gray-900"><?= e(inr($subsidy)) ?></p>
          <p class="text-xs text-gray-500">central subsidy</p>

          <dl class="mt-5 space-y-2 border-t border-gray-100 pt-4 text-sm">
            <div class="flex justify-between"><dt class="text-gray-600">System cost</dt><dd class="font-medium text-gray-900"><?= e(inr($gross)) ?></dd></div>
            <div class="flex justify-between"><dt class="text-gray-600">You pay</dt><dd class="font-bold text-primary-700"><?= e(inr($gross - $subsidy)) ?></dd></div>
            <div class="flex justify-between"><dt class="text-gray-600">Generates</dt><dd class="font-medium text-gray-900"><?= e($generates) ?></dd></div>
          </dl>

          <p class="mt-4 text-xs leading-relaxed text-gray-600"><?= e($note) ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mx-auto mt-8 max-w-3xl rounded-2xl border border-accent-400 bg-accent-500/10 px-6 py-4">
      <p class="text-sm leading-relaxed text-gray-800">
        <strong class="text-gray-900">The cap matters.</strong> A 3 kW system earns the full ₹78,000,
        and anything larger earns exactly the same. If your consumption justifies 5 kW or 10 kW,
        install it — just fund the capacity above 3 kW yourself. Some states add a top-up on the
        central amount; we confirm what applies to your address during the survey.
      </p>
    </div>

    <p class="mt-8 text-center">
      <a href="/solar-calculator.php" class="btn-primary bg-accent-500 text-ink hover:bg-accent-400">
        Calculate my exact subsidy <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
      </a>
    </p>
  </div>
</section>

<!-- Eligibility -->
<section class="mx-auto container px-6 py-16">
  <div class="mx-auto max-w-2xl text-center">
    <h2 class="text-3xl font-bold text-gray-900">Am I eligible?</h2>
    <p class="mt-3 text-gray-600">Four conditions decide it. All four must hold.</p>
  </div>

  <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
    <?php foreach ([
        ['Indian citizen', 'You apply in your own name as an Indian citizen. One subsidy per household.'],
        ['You own the roof', 'You own the property, or hold written authorisation to install on the roof. Tenants cannot apply alone.'],
        ['Valid electricity connection', 'An active domestic connection with your DISCOM, in the applicant\'s name, matching the installation address.'],
        ['No prior subsidised system', 'No rooftop solar for which a central subsidy has already been claimed on this connection.'],
    ] as [$title, $desc]): ?>
      <div class="card border border-gray-900 shadow-none">
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-primary-50 text-primary-700"><?= icon('shield', 'h-5 w-5') ?></span>
        <h3 class="mt-4 font-semibold text-gray-900"><?= e($title) ?></h3>
        <p class="mt-2 text-sm leading-relaxed text-gray-600"><?= e($desc) ?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Who is NOT covered — saves wasted enquiries -->
  <div class="mx-auto mt-10 max-w-3xl card border border-gray-900 shadow-none">
    <h3 class="font-bold text-gray-900">Not covered by this scheme</h3>
    <ul class="mt-4 grid gap-2 sm:grid-cols-2 text-sm text-gray-600">
      <?php foreach ([
          'Commercial and industrial premises',
          'Pure off-grid systems with no grid connection',
          'Battery storage (the panels qualify, the battery does not)',
          'Properties with an existing subsidised solar system',
      ] as $item): ?>
        <li class="flex items-start gap-2">
          <span class="mt-1 shrink-0 text-gray-400"><?= icon('x', 'h-3.5 w-3.5') ?></span>
          <span><?= e($item) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
    <p class="mt-4 text-sm text-gray-600">
      Running a business or a housing society?
      <a href="/contact.php" class="font-semibold text-primary-700 hover:text-primary-600">Talk to us</a> —
      accelerated depreciation and the group-housing provision may still apply.
    </p>
  </div>
</section>

<!-- Application process -->
<section class="bg-gray-50 py-16">
  <div class="mx-auto container px-6">
    <div class="mx-auto max-w-2xl text-center">
      <h2 class="text-3xl font-bold text-gray-900">How to apply, step by step</h2>
      <p class="mt-3 text-gray-600">
        Eight steps from registration to subsidy in your account. We run steps 3 to 8 on your behalf.
      </p>
    </div>

    <ol class="mx-auto mt-12 max-w-3xl space-y-4">
      <?php foreach ($steps as $i => [$title, $body]): ?>
        <li class="card flex gap-5 border border-gray-900 shadow-none">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-500 font-bold text-ink"><?= sprintf('%02d', $i + 1) ?></span>
          <div>
            <h3 class="font-bold text-gray-900"><?= e($title) ?></h3>
            <p class="mt-1 text-sm leading-relaxed text-gray-600"><?= e($body) ?></p>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>

    <div class="mx-auto mt-10 max-w-3xl rounded-2xl bg-ink px-8 py-7 text-center">
      <p class="text-lg font-semibold text-white">We do the paperwork, not you.</p>
      <p class="mt-2 text-sm text-gray-300">
        As a registered vendor we handle the portal application, DISCOM liaison, net metering and the
        subsidy claim. You sign where needed and we keep you posted at each stage.
      </p>
      <a href="/contact.php" class="btn-primary mt-6 bg-accent-500 text-ink hover:bg-accent-400">
        Start my application <span class="btn-icon"><?= icon('arrow-right', 'h-4 w-4') ?></span>
      </a>
    </div>
  </div>
</section>

<!-- Documents + timeline -->
<section class="mx-auto container px-6 py-16">
  <div class="grid gap-10 lg:grid-cols-2">
    <div>
      <h2 class="text-2xl font-bold text-gray-900">Documents you'll need</h2>
      <p class="mt-2 text-sm text-gray-600">Keep digital copies ready — the portal takes uploads.</p>
      <ul class="mt-6 space-y-3">
        <?php foreach ([
            ['Aadhaar card', 'Identity proof for the applicant'],
            ['Latest electricity bill', 'Must show the consumer number and be in the applicant\'s name'],
            ['Proof of property ownership', 'Sale deed, property tax receipt or allotment letter'],
            ['Bank passbook or cancelled cheque', 'Where the subsidy will be credited'],
            ['Passport-size photograph', 'For the portal application'],
            ['Roof authorisation letter', 'Only if the roof is jointly owned or you are not the sole owner'],
        ] as [$doc, $why]): ?>
          <li class="flex items-start gap-3 rounded-xl border border-gray-200 p-4">
            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600"><?= icon('clipboard', 'h-4 w-4') ?></span>
            <div>
              <p class="font-semibold text-gray-900 text-sm"><?= e($doc) ?></p>
              <p class="mt-0.5 text-xs text-gray-500"><?= e($why) ?></p>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div>
      <h2 class="text-2xl font-bold text-gray-900">How long each stage takes</h2>
      <p class="mt-2 text-sm text-gray-600">Typical timeline for a residential rooftop in Delhi NCR.</p>
      <ol class="mt-6 space-y-0">
        <?php
        $timeline = [
            ['Portal registration & application', 'Same day'],
            ['DISCOM feasibility approval', '1–2 weeks'],
            ['Site survey & system design', '1–2 days'],
            ['Installation & commissioning', '1–3 days'],
            ['Net meter installation', '1–2 weeks'],
            ['DISCOM inspection & certificate', '3–7 days'],
            ['Subsidy credited by DBT', '30–45 days'],
        ];
        $last = count($timeline) - 1;
        foreach ($timeline as $i => [$stage, $duration]): ?>
          <li class="relative flex gap-4 <?= $i === $last ? '' : 'pb-6' ?>">
            <?php if ($i !== $last): ?>
              <span class="absolute left-[11px] top-6 h-full w-0.5 bg-primary-100" aria-hidden="true"></span>
            <?php endif; ?>
            <span class="relative z-10 mt-1 h-6 w-6 shrink-0 rounded-full border-4 border-primary-500 bg-white"></span>
            <div class="flex flex-1 flex-wrap items-baseline justify-between gap-2">
              <p class="font-semibold text-gray-900"><?= e($stage) ?></p>
              <span class="badge bg-primary-50 text-primary-700"><?= e($duration) ?></span>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>

      <div class="mt-6 rounded-2xl bg-primary-50 p-5">
        <p class="text-sm font-semibold text-gray-900">Total: about 3 to 6 weeks</p>
        <p class="mt-1 text-sm text-gray-600">
          From application to a working system. The subsidy follows 30–45 days after the claim is raised.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- Common mistakes -->
<section class="bg-gray-50 py-16">
  <div class="mx-auto container px-6">
    <div class="mx-auto max-w-2xl text-center">
      <h2 class="text-3xl font-bold text-gray-900">Mistakes that cost people their subsidy</h2>
      <p class="mt-3 text-gray-600">Every one of these is avoidable, and every one of them we see.</p>
    </div>

    <div class="mt-10 grid gap-6 md:grid-cols-3">
      <?php foreach ([
          ['Installing before approval', 'Starting the installation before DISCOM feasibility approval comes through can void the claim entirely. Wait for the approval.'],
          ['Non-ALMM modules', 'Cheaper panels outside the MNRE approved list disqualify the subsidy. The saving up front costs far more than it saves.'],
          ['Unregistered installer', 'The work must be done by a vendor registered on the national portal, or the claim will not be processed.'],
          ['Name mismatch', 'The electricity connection, the property proof and the bank account must all be in the applicant\'s name. A mismatch stalls the claim.'],
          ['Skipping net metering', 'A subsidised system must be grid-connected with a bi-directional meter. No net meter, no subsidy.'],
          ['Oversizing for the subsidy', 'Going past 3 kW does not increase the subsidy. Size the system to your consumption, not to the scheme.'],
      ] as [$title, $body]): ?>
        <div class="card border border-gray-900 shadow-none">
          <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-red-50 text-red-600"><?= icon('x', 'h-5 w-5') ?></span>
          <h3 class="mt-4 font-semibold text-gray-900"><?= e($title) ?></h3>
          <p class="mt-2 text-sm leading-relaxed text-gray-600"><?= e($body) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php
$faqEyebrow = 'Scheme FAQ';
$faqTitle = 'PM Surya Ghar questions, answered';
$faqIntro = 'Subsidy amounts, eligibility edge cases, timelines and what disqualifies a claim.';
$faqItems = $schemeFaqs;
$faqFooter = '<a href="/contact.php" class="btn-primary bg-accent-500 text-ink hover:bg-accent-400">Ask us your question <span class="btn-icon">' . icon('arrow-right', 'h-4 w-4') . '</span></a>';
require __DIR__ . '/components/faq-section.php';
?>

<?php require __DIR__ . '/components/consultation-cta.php'; ?>

<!-- <section class="mx-auto container px-6 pb-16">
  <p class="mx-auto max-w-2xl text-center text-xs leading-relaxed text-gray-500">
    Subsidy amounts, eligibility and process are set by the Government of India under PM Surya Ghar:
    Muft Bijli Yojana and may change without notice. Figures on this page reflect the scheme as
    published at <a href="https://pmsuryaghar.gov.in/" target="_blank" rel="noopener" class="underline hover:text-gray-700">pmsuryaghar.gov.in</a>.
    HVU Solar assists with the application; final approval and disbursement rest with your DISCOM and
    the national portal. Always verify current terms on the official portal before committing.
  </p>
</section> -->

<?php require __DIR__ . '/components/footer.php'; ?>
