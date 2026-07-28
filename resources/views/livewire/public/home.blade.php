<?php

use App\Enums\BlogStatus;
use App\Models\BlogPost;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public function with(): array
    {
        return [
            'products' => Product::where('is_active', true)->orderBy('capacity_kw')->limit(3)->get(),
            'posts' => BlogPost::where('status', BlogStatus::Published)->latest('published_at')->limit(3)->get(),
        ];
    }
}; ?>

<div>
    {{-- Hero --}}
    <section class="bg-indigo-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
            <h1 class="text-4xl sm:text-5xl font-bold text-white tracking-tight">
                {{ __('Switch to Solar. Save Every Month.') }}
            </h1>
            <p class="mt-4 text-lg text-indigo-100 max-w-2xl mx-auto">
                {{ __('Hindustan Vidyut Udyog installs reliable residential rooftop solar systems — from site survey to handover.') }}
            </p>
            <a href="{{ route('quote.create') }}" wire:navigate
               class="inline-flex items-center mt-8 px-6 py-3 bg-white border border-transparent rounded-md font-semibold text-sm text-indigo-700 uppercase tracking-widest hover:bg-indigo-50">
                {{ __('Get a Free Quote') }}
            </a>
        </div>
    </section>

    {{-- Why us --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
            <div>
                <div class="text-3xl font-bold text-indigo-600">7</div>
                <div class="text-sm text-gray-600 mt-1">{{ __('Stage tracked installation, start to handover') }}</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-indigo-600">100%</div>
                <div class="text-sm text-gray-600 mt-1">{{ __('Transparent progress updates for every customer') }}</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-indigo-600">₹0</div>
                <div class="text-sm text-gray-600 mt-1">{{ __('Obligation to get a quote') }}</div>
            </div>
        </div>
    </section>

    {{-- Featured products --}}
    @if ($products->isNotEmpty())
        <section class="bg-gray-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-8">{{ __('Popular Systems') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    @foreach ($products as $product)
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            @if ($product->image_path)
                                <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" class="w-full h-40 object-cover">
                            @else
                                <div class="w-full h-40 bg-indigo-100"></div>
                            @endif
                            <div class="p-4">
                                <div class="font-semibold text-gray-900">{{ $product->name }}</div>
                                <div class="text-sm text-gray-500">{{ $product->capacity_kw }} kW</div>
                                @if ($product->price_indicative)
                                    <div class="text-sm text-indigo-600 mt-1">{{ __('From') }} ₹{{ number_format($product->price_indicative) }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('products') }}" wire:navigate class="inline-block mt-8 text-sm font-medium text-indigo-600 hover:underline">{{ __('View all products →') }}</a>
            </div>
        </section>
    @endif

    {{-- Recent blog posts --}}
    @if ($posts->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">{{ __('From the Blog') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                @foreach ($posts as $post)
                    <a href="{{ route('blog.show', $post) }}" wire:navigate class="block bg-white border border-gray-100 rounded-lg shadow-sm p-4 hover:shadow-md transition">
                        <div class="font-semibold text-gray-900">{{ $post->title }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $post->published_at?->format('d M Y') }}</div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- CTA banner --}}
    <section class="bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
            <h2 class="text-xl font-semibold text-white">{{ __('Ready to go solar?') }}</h2>
            <a href="{{ route('quote.create') }}" wire:navigate
               class="inline-flex items-center mt-4 px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-500">
                {{ __('Request Your Quote') }}
            </a>
        </div>
    </section>
</div>
