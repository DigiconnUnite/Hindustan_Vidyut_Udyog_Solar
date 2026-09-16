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
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.css">
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    html,
    body {
      height: 100%;
      overflow: hidden;
    }

    #admin-sidebar {
      position: fixed;
      inset: 0 auto 0 0;
      z-index: 30;
      height: 100dvh;
      width: 5rem;
      transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .admin-main {
      height: 100dvh;
      margin-left: 5rem;
      overflow: hidden;
      transition: margin-left 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #admin-sidebar .sidebar-label {
      display: none;
    }

    #admin-sidebar .sidebar-link {
      gap: 0;
      justify-content: center;
    }

    #admin-sidebar .sidebar-brand {
      padding-left: 0.5rem;
      padding-right: 0.5rem;
    }

    #admin-sidebar.sidebar-expanded {
      width: 16rem !important;
    }

    #admin-sidebar.sidebar-expanded .sidebar-label {
      display: inline;
    }

    #admin-sidebar.sidebar-expanded .sidebar-link {
      gap: 0.75rem;
      justify-content: flex-start;
    }

    #admin-sidebar.sidebar-expanded .sidebar-brand {
      padding-left: 0.75rem;
      padding-right: 0.75rem;
    }

    #admin-sidebar.sidebar-collapsed {
      width: 5rem !important;
    }

    #admin-sidebar.sidebar-collapsed .sidebar-label {
      display: none;
    }

    #admin-sidebar.sidebar-collapsed .sidebar-link {
      gap: 0;
      justify-content: center;
    }

    /* On compact screens the sidebar overlays the page instead of resizing it. */
    #sidebar-backdrop {
      display: none;
    }

    @media (max-width: 1023px) {
      #admin-sidebar.sidebar-expanded {
        z-index: 50;
        box-shadow: 4px 0 16px rgba(0, 0, 0, 0.18);
      }

      #admin-sidebar.sidebar-expanded + #sidebar-backdrop {
        position: fixed;
        inset: 0;
        z-index: 40;
        display: block;
        border: 0;
        background: rgba(15, 23, 42, 0.45);
        cursor: default;
      }
    }

    /* Desktop starts expanded; tablet and mobile start collapsed. */
    @media (min-width: 1024px) {
      #admin-sidebar {
        width: 16rem;
      }

      #admin-sidebar .sidebar-label {
        display: inline;
      }

      #admin-sidebar .sidebar-link {
        gap: 0.75rem;
        justify-content: flex-start;
      }

      #admin-sidebar .sidebar-brand {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
      }

      .admin-main,
      #admin-sidebar.sidebar-expanded ~ .admin-main {
        margin-left: 16rem;
      }

      #admin-sidebar.sidebar-collapsed ~ .admin-main {
        margin-left: 5rem;
      }
    }

  </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased">
  <div class="flex h-screen overflow-hidden">

    <!-- ================= SIDEBAR ================= -->
    <aside
      id="admin-sidebar"
      class="flex shrink-0 flex-col bg-gray-900 text-gray-300">

      <!-- Logo + Toggle -->
      <div class="sidebar-brand flex items-center justify-between px-3 py-3 border-b border-gray-700">
        <div class="flex min-w-0 items-center gap-2 font-bold text-white">
          <img
            src="/assets/images/hvul-logo.png"
            alt="HVU Solar Logo"
            class="h-8 w-8 shrink-0 rounded-full object-contain" />

          <span
            id="sidebar-logo-text"
            class="sidebar-label whitespace-nowrap overflow-hidden transition-all duration-300 ease-in-out">
            HVU Solar
          </span>
        </div>

        <button
          type="button"
          id="sidebar-toggle"
          title="Collapse sidebar"
          aria-label="Collapse sidebar"
          class="ml-2 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-gray-400 transition-all duration-300 hover:bg-gray-800 hover:text-white">
          <i class="fa-solid fa-angle-left transition-transform duration-300 ease-in-out"></i>
        </button>
      </div>


      <!-- ================= NAVIGATION ================= -->
      <nav class="flex-1 space-y-1 px-3" style="padding-top: 20px;">
        <?php foreach ($navItems as $href => [$label, $iconName]): ?>
          <a
            href="<?= e($href) ?>"
            title="<?= e($label) ?>"
            class="sidebar-link flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
            <?= $currentScript === $href
              ? 'bg-primary-600 text-white'
              : 'hover:bg-gray-800' ?>">

            <?= icon($iconName, 'h-4 w-4 shrink-0') ?>

            <span class="sidebar-label whitespace-nowrap">
              <?= e($label) ?>
            </span>
          </a>
        <?php endforeach; ?>
      </nav>


      <!-- ================= LOGOUT ================= -->
      <div class="px-3 pb-5">
        <form method="post" action="/admin/logout.php">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="logout">
          <button
            type="submit"
          title="Logout"
            class="sidebar-link flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition hover:bg-gray-800">
            <?= icon('log-out', 'h-4 w-4 shrink-0') ?>
            <span class="sidebar-label whitespace-nowrap overflow-hidden transition-all duration-300 ease-in-out">
              Logout
            </span>
          </button>
        </form>
      </div>
    </aside>
    <button
      id="sidebar-backdrop"
      type="button"
      aria-label="Close sidebar">
    </button>


    <!-- ================= MAIN CONTENT ================= -->
    <div class="admin-main flex min-w-0 flex-1 flex-col">

      <!-- Header -->
      <header class="flex items-center justify-end border-b border-gray-100 bg-white px-6 py-4">
        <div class="flex items-center gap-3 text-sm">
          <span class="text-gray-500">
            <?= e($user['name'] ?? '') ?>
          </span>

          <span class="badge bg-primary-50 text-primary-700">
            <?= e(ucfirst($user['role'] ?? '')) ?>
          </span>
        </div>
      </header>

      <!-- Main -->
      <main class="min-h-0 flex-1 overflow-y-auto p-6">
        <?php require __DIR__ . '/flash-message.php'; ?>


        <script>
          document.addEventListener('DOMContentLoaded', function() {

            const sidebar = document.getElementById('admin-sidebar');
            const toggle = document.getElementById('sidebar-toggle');
            const backdrop = document.getElementById('sidebar-backdrop');
            const compactBreakpoint = window.matchMedia('(max-width: 1023px)');

            if (!sidebar || !toggle) return;

            const chevron = toggle.querySelector('i');


            /* ================= COLLAPSE ================= */

            function setSidebarState(isCollapsed, persist) {

              sidebar.classList.toggle('sidebar-collapsed', isCollapsed);
              sidebar.classList.toggle('sidebar-expanded', !isCollapsed);
              toggle.setAttribute('aria-expanded', String(!isCollapsed));

              if (chevron) {
                chevron.style.transform = isCollapsed ? 'rotate(180deg)' : 'rotate(0deg)';
              }

              toggle.setAttribute('title', isCollapsed ? 'Expand sidebar' : 'Collapse sidebar');
              toggle.setAttribute('aria-label', isCollapsed ? 'Expand sidebar' : 'Collapse sidebar');

              if (persist) {
                localStorage.setItem(
                  compactBreakpoint.matches ? 'sidebarCollapsedCompact' : 'sidebarCollapsedDesktop',
                  String(isCollapsed)
                );
              }
            }


            /* ================= TOGGLE ================= */

            toggle.addEventListener('click', function() {

              setSidebarState(!sidebar.classList.contains('sidebar-collapsed'), true);

            });

            if (backdrop) {
              backdrop.addEventListener('click', function() {
                setSidebarState(true, true);
              });
            }


            /* ================= RESTORE STATE ================= */

            function restoreSidebarState() {
              const storageKey = compactBreakpoint.matches ? 'sidebarCollapsedCompact' : 'sidebarCollapsedDesktop';
              const savedState = localStorage.getItem(storageKey);
              const defaultCollapsed = compactBreakpoint.matches;

              setSidebarState(savedState === null ? defaultCollapsed : savedState === 'true', false);
            }

            restoreSidebarState();
            compactBreakpoint.addEventListener('change', restoreSidebarState);

          });
        </script>
