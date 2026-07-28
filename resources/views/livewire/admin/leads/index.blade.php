<?php

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $sourceFilter = '';
    public string $assignedFilter = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Lead::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'statusFilter', 'sourceFilter', 'assignedFilter'], strict: true)) {
            $this->resetPage();
        }
    }

    public function with(): array
    {
        $leads = Lead::query()
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%")
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->sourceFilter, fn ($q) => $q->where('source', $this->sourceFilter))
            ->when($this->assignedFilter, fn ($q) => $q->where('assigned_to', $this->assignedFilter))
            ->with('assignedStaff')
            ->latest()
            ->paginate(15);

        return [
            'leads' => $leads,
            'staffOptions' => User::whereIn('role', ['admin', 'staff'])->orderBy('name')->get(),
        ];
    }
}; ?>

<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Leads') }}</h2>
        <a href="{{ route('admin.leads.create') }}" wire:navigate
           class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
            {{ __('+ Add Lead') }}
        </a>
    </div>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search name or phone...') }}"
                       class="border-gray-300 rounded-md shadow-sm text-sm">

                <select wire:model.live="statusFilter" class="border-gray-300 rounded-md shadow-sm text-sm">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (LeadStatus::cases() as $status)
                        <option value="{{ $status->value }}">{{ ucfirst($status->value) }}</option>
                    @endforeach
                </select>

                <select wire:model.live="sourceFilter" class="border-gray-300 rounded-md shadow-sm text-sm">
                    <option value="">{{ __('All sources') }}</option>
                    @foreach (LeadSource::cases() as $source)
                        <option value="{{ $source->value }}">{{ ucfirst(str_replace('_', ' ', $source->value)) }}</option>
                    @endforeach
                </select>

                <select wire:model.live="assignedFilter" class="border-gray-300 rounded-md shadow-sm text-sm">
                    <option value="">{{ __('Anyone') }}</option>
                    @foreach ($staffOptions as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 uppercase text-xs">
                            <th class="px-3 py-2">{{ __('Name') }}</th>
                            <th class="px-3 py-2">{{ __('Phone') }}</th>
                            <th class="px-3 py-2">{{ __('City') }}</th>
                            <th class="px-3 py-2">{{ __('Source') }}</th>
                            <th class="px-3 py-2">{{ __('Status') }}</th>
                            <th class="px-3 py-2">{{ __('Owner') }}</th>
                            <th class="px-3 py-2">{{ __('Created') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($leads as $lead)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('admin.leads.show', $lead) }}'">
                                <td class="px-3 py-2 font-medium text-gray-900">{{ $lead->name }}</td>
                                <td class="px-3 py-2">{{ $lead->phone }}</td>
                                <td class="px-3 py-2">{{ $lead->city ?? '—' }}</td>
                                <td class="px-3 py-2">{{ ucfirst(str_replace('_', ' ', $lead->source->value)) }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                        @class([
                                            'bg-blue-100 text-blue-800' => $lead->status === \App\Enums\LeadStatus::New,
                                            'bg-yellow-100 text-yellow-800' => $lead->status === \App\Enums\LeadStatus::Contacted,
                                            'bg-purple-100 text-purple-800' => $lead->status === \App\Enums\LeadStatus::Qualified,
                                            'bg-green-100 text-green-800' => $lead->status === \App\Enums\LeadStatus::Converted,
                                            'bg-gray-100 text-gray-800' => $lead->status === \App\Enums\LeadStatus::Lost,
                                        ])">
                                        {{ ucfirst($lead->status->value) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">{{ $lead->assignedStaff?->name ?? '—' }}</td>
                                <td class="px-3 py-2 text-gray-500">{{ $lead->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-gray-500">{{ __('No leads found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $leads->links() }}
            </div>
        </div>
    </div>
</div>
