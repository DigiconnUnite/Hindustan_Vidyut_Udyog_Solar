<?php

use App\Enums\JobStage;
use App\Models\InstallationJob;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $technicianFilter = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', InstallationJob::class);
    }

    public function with(): array
    {
        $jobs = InstallationJob::query()
            ->when($this->technicianFilter, fn ($q) => $q->whereHas(
                'activeTeamAssignments',
                fn ($q) => $q->where('user_id', $this->technicianFilter)
            ))
            ->with('customer')
            ->latest()
            ->get()
            ->groupBy(fn (InstallationJob $job) => $job->current_stage->value);

        return [
            'jobsByStage' => $jobs,
            'technicianOptions' => User::where('role', 'technician')->orderBy('name')->get(),
        ];
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Installation Jobs') }}</h2>
</x-slot>

<div class="py-12">
    <div class="max-w-[100rem] mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

            <div class="mb-6 max-w-xs">
                <select wire:model.live="technicianFilter" class="border-gray-300 rounded-md shadow-sm text-sm w-full">
                    <option value="">{{ __('All technicians') }}</option>
                    @foreach ($technicianOptions as $tech)
                        <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-7 gap-3 overflow-x-auto">
                @foreach (JobStage::ordered() as $stage)
                    <div class="bg-gray-50 rounded-lg p-3 min-w-[9rem]">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">
                            {{ ucfirst(str_replace('_', ' ', $stage->value)) }}
                            <span class="text-gray-400">({{ $jobsByStage->get($stage->value, collect())->count() }})</span>
                        </h4>
                        <div class="space-y-2">
                            @foreach ($jobsByStage->get($stage->value, collect()) as $job)
                                <a href="{{ route('admin.jobs.show', $job) }}" wire:navigate
                                   class="block bg-white border border-gray-200 rounded-md p-2 text-xs hover:border-indigo-400 hover:shadow-sm transition">
                                    <div class="font-medium text-gray-900">Job #{{ $job->id }}</div>
                                    <div class="text-gray-500 truncate">{{ $job->customer->name }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
