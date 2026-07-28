<?php

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $message = '';
    public bool $sent = false;

    public function send(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // No dedicated contact_messages table in scope (docs/02) — logged for
        // now; upgrade to a stored/emailed record if contact-form volume grows.
        Log::info('Contact form submission', [
            'name' => $this->name,
            'email' => $this->email,
            'message' => $this->message,
        ]);

        $this->reset(['name', 'email', 'message']);
        $this->sent = true;
    }
}; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Contact Us') }}</h1>
    <p class="text-gray-600 mb-10">{{ __('Have a question? Reach out and we\'ll get back to you.') }}</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-10">
        <div>
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-gray-500">{{ __('Phone') }}</dt>
                    <dd class="font-medium text-gray-900">{{ CompanySetting::get('contact_phone', '—') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">{{ __('Email') }}</dt>
                    <dd class="font-medium text-gray-900">{{ CompanySetting::get('contact_email', '—') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">{{ __('Address') }}</dt>
                    <dd class="font-medium text-gray-900">{{ CompanySetting::get('address', '—') }}</dd>
                </div>
            </dl>
        </div>

        <div>
            @if ($sent)
                <div class="bg-green-50 text-green-700 text-sm rounded-md p-4">{{ __('Thanks! We\'ll get back to you soon.') }}</div>
            @else
                <form wire:submit="send" class="space-y-4">
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input wire:model="name" id="name" class="block mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input wire:model="email" id="email" type="email" class="block mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="message" :value="__('Message')" />
                        <textarea wire:model="message" id="message" rows="4" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required></textarea>
                        <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    </div>
                    <x-primary-button>{{ __('Send Message') }}</x-primary-button>
                </form>
            @endif
        </div>
    </div>
</div>
