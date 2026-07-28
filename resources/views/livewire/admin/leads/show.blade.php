<?php

use App\Enums\JobStage;
use App\Enums\LeadStatus;
use App\Enums\Role;
use App\Models\InstallationJob;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Lead $lead;

    public string $status = '';
    public string $assigned_to = '';
    public string $notes = '';

    // Convert-to-job fields
    public bool $showConvertForm = false;
    public string $convertAddress = '';
    public string $convertCity = '';
    public ?float $convertCapacity = null;

    public function mount(Lead $lead): void
    {
        Gate::authorize('view', $lead);

        $this->lead = $lead;
        $this->status = $lead->status->value;
        $this->assigned_to = (string) $lead->assigned_to;
        $this->notes = (string) $lead->notes;
        $this->convertAddress = $lead->address ?? '';
        $this->convertCity = $lead->city ?? '';
    }

    public function updateDetails(): void
    {
        Gate::authorize('update', $this->lead);

        $validated = $this->validate([
            'status' => ['required', 'in:'.implode(',', array_column(LeadStatus::cases(), 'value'))],
            'assigned_to' => ['nullable'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->lead->update([
            'status' => $validated['status'],
            'assigned_to' => $validated['assigned_to'] !== '' ? $validated['assigned_to'] : null,
            'notes' => $validated['notes'],
        ]);

        session()->flash('status', __('Lead updated.'));
    }

    public function convertToJob(): void
    {
        Gate::authorize('convert', $this->lead);

        $validated = $this->validate([
            'convertAddress' => ['required', 'string', 'max:255'],
            'convertCity' => ['nullable', 'string', 'max:100'],
            'convertCapacity' => ['nullable', 'numeric', 'min:0'],
        ]);

        $job = DB::transaction(function () use ($validated) {
            // Reuse an existing customer account by email/phone match, otherwise create one.
            $customer = null;

            if ($this->lead->email) {
                $customer = User::where('email', $this->lead->email)->first();
            }

            if (! $customer) {
                $customer = User::create([
                    'name' => $this->lead->name,
                    'email' => $this->lead->email ?? Str::slug($this->lead->name).'-'.$this->lead->id.'@placeholder.hvu.test',
                    'phone' => $this->lead->phone,
                    'password' => Str::random(32),
                    'role' => Role::Customer,
                ]);
            }

            $job = InstallationJob::create([
                'lead_id' => $this->lead->id,
                'customer_id' => $customer->id,
                'address' => $validated['convertAddress'],
                'city' => $validated['convertCity'],
                'system_capacity_kw' => $validated['convertCapacity'],
                'current_stage' => JobStage::Lead,
            ]);

            $job->stageHistory()->create([
                'stage' => JobStage::Lead,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'notes' => 'Job created from lead conversion.',
            ]);

            $this->lead->update([
                'status' => LeadStatus::Converted,
                'converted_job_id' => $job->id,
            ]);

            return $job;
        });

        session()->flash('status', __('Lead converted to installation job.'));

        $this->redirect(route('admin.jobs.show', $job), navigate: true);
    }

    public function with(): array
    {
        return [
            'staffOptions' => User::whereIn('role', ['admin', 'staff'])->orderBy('name')->get(),
        ];
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Lead') }}: {{ $lead->name }}</h2>
</x-slot>

<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if (session('status'))
            <div class="bg-green-50 text-green-700 text-sm rounded-md p-4">{{ session('status') }}</div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="font-semibold text-gray-800 mb-4">{{ __('Contact Info') }}</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500">{{ __('Phone') }}</dt><dd class="font-medium">{{ $lead->phone }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Email') }}</dt><dd class="font-medium">{{ $lead->email ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Address') }}</dt><dd class="font-medium">{{ $lead->address ?? '—' }}, {{ $lead->city ?? '' }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Roof Type') }}</dt><dd class="font-medium">{{ $lead->roof_type ? ucfirst(str_replace('_', ' ', $lead->roof_type->value)) : '—' }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Monthly Bill (approx)') }}</dt><dd class="font-medium">{{ $lead->monthly_bill_estimate ? '₹'.number_format($lead->monthly_bill_estimate, 2) : '—' }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Source') }}</dt><dd class="font-medium">{{ ucfirst(str_replace('_', ' ', $lead->source->value)) }}</dd></div>
            </dl>
        </div>

        @if ($lead->status !== \App\Enums\LeadStatus::Converted)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-4">{{ __('Manage Lead') }}</h3>
                <form wire:submit="updateDetails" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select wire:model="status" id="status" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach (LeadStatus::cases() as $s)
                                    <option value="{{ $s->value }}">{{ ucfirst($s->value) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="assigned_to" :value="__('Assigned To')" />
                            <select wire:model="assigned_to" id="assigned_to" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">{{ __('Unassigned') }}</option>
                                @foreach ($staffOptions as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea wire:model="notes" id="notes" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                    </div>
                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-4">{{ __('Convert to Installation Job') }}</h3>

                @if (! $showConvertForm)
                    <button wire:click="$set('showConvertForm', true)" type="button"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                        {{ __('Convert to Installation Job') }}
                    </button>
                @else
                    <form wire:submit="convertToJob" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <x-input-label for="convertAddress" :value="__('Installation Address')" />
                                <x-text-input wire:model="convertAddress" id="convertAddress" class="block mt-1 w-full" required />
                                <x-input-error :messages="$errors->get('convertAddress')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="convertCity" :value="__('City')" />
                                <x-text-input wire:model="convertCity" id="convertCity" class="block mt-1 w-full" />
                            </div>
                            <div>
                                <x-input-label for="convertCapacity" :value="__('System Capacity (kW)')" />
                                <x-text-input wire:model="convertCapacity" id="convertCapacity" type="number" step="0.01" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('convertCapacity')" class="mt-2" />
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <x-primary-button>{{ __('Confirm Conversion') }}</x-primary-button>
                            <button type="button" wire:click="$set('showConvertForm', false)" class="text-sm text-gray-600 self-center">{{ __('Cancel') }}</button>
                        </div>
                    </form>
                @endif
            </div>
        @else
            <div class="bg-green-50 text-green-800 text-sm rounded-md p-4">
                {{ __('This lead has been converted.') }}
                <a href="{{ route('admin.jobs.show', $lead->converted_job_id) }}" wire:navigate class="underline font-medium">{{ __('View the installation job →') }}</a>
            </div>
        @endif
    </div>
</div>
