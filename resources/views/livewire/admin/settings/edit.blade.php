<?php

use App\Models\CompanySetting;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $contact_phone = '';
    public string $contact_email = '';
    public string $address = '';
    public string $facebook_url = '';
    public string $instagram_url = '';
    public string $seo_default_title = '';
    public string $seo_default_description = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        foreach (['contact_phone', 'contact_email', 'address', 'facebook_url', 'instagram_url', 'seo_default_title', 'seo_default_description'] as $key) {
            $this->$key = CompanySetting::get($key, '');
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'seo_default_title' => ['nullable', 'string', 'max:255'],
            'seo_default_description' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($validated as $key => $value) {
            CompanySetting::set($key, $value ?: null);
        }

        session()->flash('status', __('Settings saved.'));
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Company Settings') }}</h2>
</x-slot>

<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

        @if (session('status'))
            <div class="bg-green-50 text-green-700 text-sm rounded-md p-4 mb-6">{{ session('status') }}</div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <form wire:submit="save" class="space-y-4">
                <div>
                    <x-input-label for="contact_phone" :value="__('Contact Phone')" />
                    <x-text-input wire:model="contact_phone" id="contact_phone" class="block mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="contact_email" :value="__('Contact Email')" />
                    <x-text-input wire:model="contact_email" id="contact_email" type="email" class="block mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="address" :value="__('Address')" />
                    <textarea wire:model="address" id="address" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                </div>
                <div>
                    <x-input-label for="facebook_url" :value="__('Facebook URL')" />
                    <x-text-input wire:model="facebook_url" id="facebook_url" class="block mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="instagram_url" :value="__('Instagram URL')" />
                    <x-text-input wire:model="instagram_url" id="instagram_url" class="block mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="seo_default_title" :value="__('SEO Default Title')" />
                    <x-text-input wire:model="seo_default_title" id="seo_default_title" class="block mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="seo_default_description" :value="__('SEO Default Description')" />
                    <textarea wire:model="seo_default_description" id="seo_default_description" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                </div>

                <x-primary-button>{{ __('Save Settings') }}</x-primary-button>
            </form>
        </div>
    </div>
</div>
