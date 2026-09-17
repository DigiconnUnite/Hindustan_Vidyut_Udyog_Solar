<?php
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/service-content.php';
$pageTitle = 'Solar Services — Survey, Installation, AMC & Upgrades | HVU Solar';
$metaDescription = 'Free site survey, turnkey rooftop installation, annual maintenance contracts and system upgrades. Certified technicians across Gurgaon and Delhi NCR.';

$services = db()->query('SELECT * FROM services ORDER BY sort_order')->fetchAll();

// Service images keyed by the `icon` column; falls back to a placeholder.
$serviceImages = [
  'clipboard' => '/assets/images/service/Solar_Site_Survey.png',
  'wrench'    => '/assets/images/service/Solar_Installation.png',
  'shield'    => '/assets/images/service/Solar_Maintenance_AMC.png',
  'arrow-up'  => '/assets/images/service/Solar_System_Upgrade.png',
];

$bannerTitle = 'Our Services';
$bannerSubtitle = 'From first survey to lifetime support, we handle every step of your solar journey.';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/page-banner.php';
?>

<section class="services-section">
  <div class="services-container">

    <?php foreach ($services as $i => $service): ?>
      <div class="service-row <?= $i % 2 === 1 ? 'service-row-reverse' : '' ?>">
        <?php
        $img = $serviceImages[$service['icon']]
          ?? 'https://placehold.co/600x450?text=' . urlencode($service['title']);
        ?>

        <!-- Image -->
        <div class="service-image-wrapper">
          <img
            src="<?= e($img) ?>"
            alt="<?= e($service['title']) ?>"
            loading="lazy"
            class="service-image">
        </div>

        <!-- Content -->
        <div class="service-content">
          <h2 class="service-title">
            <?= e($service['id']) ?>.
            <?= e($service['title']) ?>
          </h2>

          <p class="service-description">
            <?= e($service['description']) ?>
          </p>

          <?php if ($extra = service_content($service['icon'])): ?>
            <p class="service-extra">
              <?= e($extra['body']) ?>
            </p>

            <ul class="service-points">
              <?php foreach ($extra['points'] as $point): ?>
                <li class="service-point">
                  <span class="service-check">
                    <?= icon('check', 'h-5 w-5') ?>
                  </span>

                  <span class="service-point-text">
                    <?= e($point) ?>
                  </span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

  </div>
</section>

<?php require __DIR__ . '/components/closing-cta.php'; ?>

<?php require __DIR__ . '/components/footer.php'; ?>
