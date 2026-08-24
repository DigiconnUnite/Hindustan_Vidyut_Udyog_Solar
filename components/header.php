<?php
require_once __DIR__ . '/../config/seo.php';

$pageTitle ??= 'Hindustan Vidyut Udyog Solar — Rooftop Solar Installation in Gurgaon';
$metaDescription ??= 'Rooftop solar installation with PM Surya Ghar subsidy assistance in Gurgaon & Delhi NCR. Free site survey, panels, inverters, batteries, AMC. Get a free quote.';
$ogImage ??= '/assets/images/hero-image-1.png';
$canonical = seo_canonical();
// Pages may set $jsonLd to an array of extra schema.org graphs (Product, FAQPage, ...).
$jsonLd = array_merge([seo_organization_schema()], $jsonLd ?? []);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($metaDescription) ?>">
  <meta name="robots" content="index, follow, max-image-preview:large">
  <link rel="canonical" href="<?= e($canonical) ?>">
  <meta name="theme-color" content="#1d5c32">
  <link rel="icon" href="/assets/images/hvul-logo.png" type="image/png">

  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Hindustan Vidyut Udyog Solar">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($metaDescription) ?>">
  <meta property="og:url" content="<?= e($canonical) ?>">
  <meta property="og:image" content="<?= e(str_starts_with($ogImage, 'http') ? $ogImage : APP_URL . $ogImage) ?>">
  <meta property="og:locale" content="en_IN">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($pageTitle) ?>">
  <meta name="twitter:description" content="<?= e($metaDescription) ?>">
  <meta name="twitter:image" content="<?= e(str_starts_with($ogImage, 'http') ? $ogImage : APP_URL . $ogImage) ?>">

  <?php foreach ($jsonLd as $schema): ?>
    <?= seo_json_ld($schema) ?>
  <?php endforeach; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    #preloader{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;
      background:#fff;transition:opacity .4s ease}
    #preloader.done{opacity:0;pointer-events:none}
    #preloader .ring{width:56px;height:56px;border:4px solid #dcefe0;border-top-color:#2f8f4e;
      border-radius:50%;animation:preloader-spin .8s linear infinite}
    @keyframes preloader-spin{to{transform:rotate(360deg)}}
    @media (prefers-reduced-motion:reduce){#preloader .ring{animation-duration:2s}}
  </style>
</head>
<body class="bg-white  text-gray-800 antialiased">
<div id="preloader" role="status" aria-label="Loading"><div class="ring"></div></div>
<script>
  addEventListener('load', function () {
    var p = document.getElementById('preloader');
    p.classList.add('done');
    setTimeout(function () { p.remove(); }, 400);
  });
</script>
<?php require __DIR__ . '/nav.php'; ?>
<div id="header-spacer" class="h-[116px] md:h-[165px] transition-[height] duration-300"></div>
