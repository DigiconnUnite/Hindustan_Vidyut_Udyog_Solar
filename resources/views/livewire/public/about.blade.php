<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component {}; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ __('About Us') }}</h1>
    <div class="prose max-w-none text-gray-700 space-y-4">
        <p>{{ __('Hindustan Vidyut Udyog installs residential rooftop solar power systems, guiding homeowners from the first site survey through final handover.') }}</p>
        <p>{{ __('Our team handles every stage in-house — site assessment, system design, government paperwork, installation, and inspection — so you always know exactly where your project stands.') }}</p>
    </div>
</div>
