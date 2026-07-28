<?php

use App\Enums\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Public marketing site — see docs/04-api-backend-contract.md §1.1
Volt::route('/', 'public.home')->name('home');
Volt::route('/about', 'public.about')->name('about');
Volt::route('/services', 'public.services')->name('services');
Volt::route('/products', 'public.products')->name('products');
Volt::route('/gallery', 'public.gallery')->name('gallery');
Volt::route('/contact', 'public.contact')->name('contact');
Volt::route('/get-a-quote', 'public.quote.create')->name('quote.create');
Volt::route('/blog', 'public.blog.index')->name('blog.index');
Volt::route('/blog/{post:slug}', 'public.blog.show')->name('blog.show');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Single named "dashboard" route that redirects to the role-specific home,
// so shared Breeze views (nav bar, verify-email) can keep using route('dashboard').
Route::get('dashboard', function () {
    return redirect()->to(match (Auth::user()->role) {
        Role::Admin, Role::Staff => route('admin.dashboard', absolute: false),
        Role::Technician => route('technician.jobs.index', absolute: false),
        Role::Customer => route('portal.installation', absolute: false),
    });
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin & Staff — see docs/04-api-backend-contract.md §1.3
Route::middleware(['auth', 'verified', 'role:admin,staff'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

        Volt::route('/leads', 'admin.leads.index')->name('leads.index');
        Volt::route('/leads/create', 'admin.leads.create')->name('leads.create');
        Volt::route('/leads/{lead}', 'admin.leads.show')->name('leads.show');

        Volt::route('/jobs', 'admin.jobs.index')->name('jobs.index');

        Volt::route('/team', 'admin.team.index')->name('team.index');
        Volt::route('/products', 'admin.products.index')->name('products.index');
        Volt::route('/products/create', 'admin.products.create')->name('products.create');
        Volt::route('/products/{product}/edit', 'admin.products.edit')->name('products.edit');
        Volt::route('/blog', 'admin.blog.index')->name('blog.index');
        Volt::route('/blog/create', 'admin.blog.create')->name('blog.create');
        Volt::route('/blog/{post}/edit', 'admin.blog.edit')->name('blog.edit');
        Volt::route('/users', 'admin.users.index')->name('users.index');
        Volt::route('/settings', 'admin.settings.edit')->name('settings.edit');
    });

// Job detail is shared by admin/staff (full access) and technician (own assigned
// jobs only, sequential stage advance) — InstallationJobPolicy enforces the split.
// See docs/04-api-backend-contract.md §1.3-1.4.
Volt::route('/admin/jobs/{job}', 'admin.jobs.show')
    ->middleware(['auth', 'verified', 'role:admin,staff,technician'])
    ->name('admin.jobs.show');

// Technician — see docs/04-api-backend-contract.md §1.4
Route::middleware(['auth', 'verified', 'role:technician'])
    ->prefix('technician')
    ->name('technician.')
    ->group(function () {
        Volt::route('/jobs', 'technician.jobs.index')->name('jobs.index');
    });

// Customer portal — see docs/04-api-backend-contract.md §1.5
Route::middleware(['auth', 'verified', 'role:customer'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function () {
        Volt::route('/installation', 'portal.installation')->name('installation');
        Volt::route('/documents', 'portal.documents')->name('documents');
    });

require __DIR__.'/auth.php';
