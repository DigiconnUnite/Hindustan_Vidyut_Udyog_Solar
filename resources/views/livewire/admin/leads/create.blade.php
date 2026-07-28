<?php

use App\Enums\LeadSource;
use App\Enums\RoofType;
use App\Models\Lead;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public string $city = '';
    public string $roof_type = '';
    public ?float $monthly_bill_estimate = null;
    public string $source = 'phone';
    public string $notes = '';

    public function mount(): void
    {
        Gate::authorize('create', Lead::class);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'roof_type' => ['nullable', 'in:'.implode(',', array_column(RoofType::cases(), 'value'))],
            'monthly_bill_estimate' => ['nullable', 'numeric', 'min:0'],
            'source' => ['required', 'in:'.implode(',', array_column(LeadSource::cases(), 'value'))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['roof_type'] = $validated['roof_type'] ?: null;

        $lead = Lead::create($validated + ['status' => 'new']);

        session()->flash('status', __('Lead created.'));

        $this->redirect(route('admin.leads.show', $lead), navigate: true);
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Lead') }}</h2>
</x-slot>

<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input wire:model="name" id="name" class="block mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="phone" :value="__('Phone')" />
                        <x-text-input wire:model="phone" id="phone" class="block mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="email" :value="__('Email (optional)')" />
                        <x-text-input wire:model="email" id="email" type="email" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="city" :value="__('City')" />
                        <x-text-input wire:model="city" id="city" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('city')" class="mt-2" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="address" :value="__('Address')" />
                        <x-text-input wire:model="address" id="address" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="roof_type" :value="__('Roof Type')" />
                        <select wire:model="roof_type" id="roof_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">{{ __('— Select —') }}</option>
                            @foreach (RoofType::cases() as $type)
                                <option value="{{ $type->value }}">{{ ucfirst(str_replace('_', ' ', $type->value)) }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('roof_type')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="monthly_bill_estimate" :value="__('Monthly Bill (approx ₹)')" />
                        <x-text-input wire:model="monthly_bill_estimate" id="monthly_bill_estimate" type="number" step="0.01" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('monthly_bill_estimate')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="source" :value="__('Source')" />
                        <select wire:model="source" id="source" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                            @foreach (LeadSource::cases() as $src)
                                <option value="{{ $src->value }}">{{ ucfirst(str_replace('_', ' ', $src->value)) }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('source')" class="mt-2" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="notes" :value="__('Notes')" />
                        <textarea wire:model="notes" id="notes" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.leads.index') }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-900 self-center">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Create Lead') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
