<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'Hindustan Vidyut Udyog Solar') ?></title>
  <meta name="description" content="Residential solar panel installation — site survey, installation, maintenance and AMC by Hindustan Vidyut Udyog Solar.">
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
