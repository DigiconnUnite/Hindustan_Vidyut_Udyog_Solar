<?php

use App\Models\InstallationJob;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        return [
            'jobs' => InstallationJob::query()
                ->whereHas('activeTeamAssignments', fn ($q) => $q->where('user_id', auth()->id()))
                ->with('customer')
                ->latest()
                ->get(),
        ];
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('My Jobs') }}</h2>
</x-slot>

<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="space-y-3">
                @forelse ($jobs as $job)
                    <a href="{{ route('admin.jobs.show', $job) }}" wire:navigate
                       class="block border border-gray-200 rounded-lg p-4 hover:border-indigo-400 hover:shadow-sm transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-medium text-gray-900">{{ $job->customer->name }}</div>
                                <div class="text-sm text-gray-500">{{ $job->address }}, {{ $job->city }}</div>
                                <div class="text-sm text-gray-500">{{ $job->customer->phone }}</div>
                            </div>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ ucfirst(str_replace('_', ' ', $job->current_stage->value)) }}
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No jobs assigned to you yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
