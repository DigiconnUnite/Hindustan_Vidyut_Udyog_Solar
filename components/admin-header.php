<?php
require_once __DIR__ . '/icon.php';
/** Expects $pageTitle and $user (from require_login()) to be set by the including page. */
$navItems = [
    '/admin/index.php' => ['Dashboard', 'layout-dashboard'],
    '/admin/jobs.php' => ['Jobs', 'wrench'],
    '/admin/team.php' => ['Team', 'users'],
    '/admin/leads.php' => ['Leads', 'inbox'],
    '/admin/products.php' => ['Products', 'package'],
    '/admin/projects.php' => ['Projects', 'clipboard'],
    '/admin/services.php' => ['Services', 'settings'],
    '/admin/subscribers.php' => ['Subscribers', 'mail'],
];
$currentScript = '/admin/' . basename($_SERVER['SCRIPT_NAME']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'Admin') ?> — HVU Solar Admin</title>
  <link rel="icon" href="/assets/images/hvul-logo.png" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
<div class="flex min-h-screen">
  <aside class="hidden md:flex md:w-64 flex-col bg-gray-900 text-gray-300">
    <div class="flex items-center gap-2 px-6 py-5 font-bold text-white">
      <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-primary-500"><?= icon('sun', 'h-4 w-4') ?></span>
      HVU Solar
    </div>
    <nav class="flex-1 px-3 space-y-1">
      <?php foreach ($navItems as $href => [$label, $iconName]): ?>
        <a href="<?= e($href) ?>"
           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium <?= $currentScript === $href ? 'bg-primary-600 text-white' : 'hover:bg-gray-800' ?>">
          <?= icon($iconName, 'h-4 w-4') ?><?= e($label) ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="px-3 pb-5">
      <a href="/admin/logout.php" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-gray-800">
        <?= icon('log-out', 'h-4 w-4') ?>Logout
      </a>
    </div>
  </aside>

  <div class="flex-1 flex flex-col min-w-0">
    <header class="flex items-center justify-between bg-white border-b border-gray-100 px-6 py-4">
      <h1 class="text-lg font-semibold text-gray-900"><?= e($pageTitle ?? '') ?></h1>
      <div class="flex items-center gap-3 text-sm">
        <span class="text-gray-500"><?= e($user['name'] ?? '') ?></span>
        <span class="badge bg-primary-50 text-primary-700"><?= e(ucfirst($user['role'] ?? '')) ?></span>
      </div>
    </header>
    <main class="flex-1 p-6">
      <?php require __DIR__ . '/flash-message.php'; ?>
