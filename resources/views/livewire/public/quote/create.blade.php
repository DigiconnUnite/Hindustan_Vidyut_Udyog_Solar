<?php

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\RoofType;
use App\Models\Lead;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Notifications\NewLeadNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public string $city = '';
    public string $roof_type = '';
    public ?float $monthly_bill_estimate = null;

    public bool $submitted = false;

    public function submit(): void
    {
        $key = 'quote-request:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, maxAttempts: 5)) {
            $this->addError('form', __('Too many requests. Please try again later.'));

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'roof_type' => ['nullable', 'in:'.implode(',', array_column(RoofType::cases(), 'value'))],
            'monthly_bill_estimate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['roof_type'] = $validated['roof_type'] ?: null;

        RateLimiter::hit($key, decaySeconds: 3600);

        $lead = DB::transaction(function () use ($validated) {
            $lead = Lead::create($validated + [
                'source' => LeadSource::Website,
                'status' => LeadStatus::New,
            ]);

            QuoteRequest::create($validated + ['converted_lead_id' => $lead->id]);

            return $lead;
        });

        // Notify staff/admin of the new lead — FR-24. Queued via the database
        // driver (no Redis on shared hosting), drained by cron.
        Notification::send(
            User::whereIn('role', ['admin', 'staff'])->get(),
            new NewLeadNotification($lead)
        );

        $this->reset(['name', 'phone', 'email', 'address', 'city', 'roof_type', 'monthly_bill_estimate']);
        $this->submitted = true;
    }
}; ?>

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Get a Free Solar Quote') }}</h1>
    <p class="text-gray-600 mb-10">{{ __("Tell us about your home and we'll get back to you within 24 hours.") }}</p>

    @if ($submitted)
        <div class="bg-green-50 text-green-700 rounded-md p-6">
            {{ __("Thanks! We'll contact you within 24 hours.") }}
        </div>
    @else
        <form wire:submit="submit" class="bg-white border border-gray-100 rounded-lg shadow-sm p-6 space-y-4">
            <x-input-error :messages="$errors->get('form')" class="mb-2" />

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
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="address" :value="__('Address')" />
                    <x-text-input wire:model="address" id="address" class="block mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="roof_type" :value="__('Roof Type')" />
                    <select wire:model="roof_type" id="roof_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">{{ __('— Select —') }}</option>
                        @foreach (RoofType::cases() as $type)
                            <option value="{{ $type->value }}">{{ ucfirst(str_replace('_', ' ', $type->value)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="monthly_bill_estimate" :value="__('Monthly Bill (approx ₹)')" />
                    <x-text-input wire:model="monthly_bill_estimate" id="monthly_bill_estimate" type="number" step="0.01" class="block mt-1 w-full" />
                </div>
            </div>

            <x-primary-button class="w-full justify-center">{{ __('Request My Quote') }}</x-primary-button>
        </form>
    @endif
</div>
