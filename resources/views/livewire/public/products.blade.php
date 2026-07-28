<?php

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public function with(): array
    {
        return ['products' => Product::where('is_active', true)->orderBy('capacity_kw')->get()];
    }
}; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Our Systems') }}</h1>
    <p class="text-gray-600 mb-10">{{ __('Residential solar systems sized for every home.') }}</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($products as $product)
            <div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden flex flex-col">
                @if ($product->image_path)
                    <img src="{{ Storage::disk('public')->url($product->image_path) }}" class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 bg-indigo-100"></div>
                @endif
                <div class="p-5 flex-1 flex flex-col">
                    <div class="font-semibold text-lg text-gray-900">{{ $product->name }}</div>
                    <div class="text-sm text-gray-500 mb-2">{{ $product->capacity_kw }} kW</div>
                    <p class="text-sm text-gray-600 flex-1">{{ $product->description }}</p>
                    @if ($product->price_indicative)
                        <div class="text-indigo-600 font-semibold mt-3">{{ __('From') }} ₹{{ number_format($product->price_indicative) }}</div>
                    @endif
                    <a href="{{ route('quote.create') }}" wire:navigate
                       class="inline-flex items-center justify-center mt-4 px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                        {{ __('Get a Quote') }}
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
