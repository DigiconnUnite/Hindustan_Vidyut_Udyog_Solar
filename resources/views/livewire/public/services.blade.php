<?php

use App\Enums\JobStage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public function with(): array
    {
        return [
            'stages' => [
                'lead' => __('We understand your requirements and roof details.'),
                'site_survey' => __('Our team visits your site to assess roof, shading, and electrical setup.'),
                'quotation' => __('You receive a detailed, transparent quote for your system.'),
                'agreement' => __('We finalize the agreement and handle subsidy paperwork.'),
                'installation' => __('Panels, inverter, and wiring are installed by our certified technicians.'),
                'inspection' => __('The system is tested and inspected for safety and performance.'),
                'handover' => __('Your system goes live, and you get full documentation.'),
            ],
        ];
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Our Installation Process') }}</h1>
    <p class="text-gray-600 mb-10">{{ __('Every installation follows the same tracked, transparent process.') }}</p>

    <ol class="space-y-6">
        @foreach (JobStage::ordered() as $stage)
            <li class="flex gap-4">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-semibold">
                    {{ $loop->iteration }}
                </div>
                <div>
                    <div class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $stage->value)) }}</div>
                    <div class="text-sm text-gray-600">{{ $stages[$stage->value] }}</div>
                </div>
            </li>
        @endforeach
    </ol>
</div>
