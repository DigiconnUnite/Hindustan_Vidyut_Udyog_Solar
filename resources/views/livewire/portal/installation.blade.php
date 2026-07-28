<?php

use App\Enums\JobStage;
use App\Models\InstallationJob;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        return [
            'jobs' => InstallationJob::where('customer_id', auth()->id())
                ->with('activeTeamAssignments.user')
                ->latest()
                ->get(),
        ];
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Your Installation') }}</h2>
</x-slot>

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @forelse ($jobs as $job)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach (JobStage::ordered() as $stage)
                        @php
                            $stageIndex = array_search($stage, JobStage::ordered(), true);
                            $currentIndex = array_search($job->current_stage, JobStage::ordered(), true);
                        @endphp
                        <div class="flex items-center">
                            <span @class([
                                'w-3 h-3 rounded-full inline-block mr-2',
                                'bg-green-500' => $stageIndex < $currentIndex,
                                'bg-indigo-600' => $stageIndex === $currentIndex,
                                'bg-gray-300' => $stageIndex > $currentIndex,
                            ])></span>
                            <span @class([
                                'text-sm',
                                'text-gray-900 font-medium' => $stageIndex <= $currentIndex,
                                'text-gray-400' => $stageIndex > $currentIndex,
                            ])>{{ ucfirst(str_replace('_', ' ', $stage->value)) }}</span>
                            @if (! $loop->last)<span class="mx-2 text-gray-300">→</span>@endif
                        </div>
                    @endforeach
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm border-t pt-4">
                    <div>
                        <dt class="text-gray-500">{{ __('Address') }}</dt>
                        <dd class="font-medium">{{ $job->address }}, {{ $job->city }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Technician') }}</dt>
                        <dd class="font-medium">{{ $job->activeTeamAssignments->first()?->user->name ?? __('Not yet assigned') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Target Completion') }}</dt>
                        <dd class="font-medium">{{ $job->target_completion_date?->format('d M Y') ?? __('TBD') }}</dd>
                    </div>
                </dl>
            </div>
        @empty
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-500 text-sm">
                {{ __("You don't have an installation on record yet.") }}
            </div>
        @endforelse
    </div>
</div>
