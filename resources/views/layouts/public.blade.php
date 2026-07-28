<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>
        <meta name="description" content="{{ $description ?? \App\Models\CompanySetting::get('seo_default_description', 'Residential solar power installation.') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <header class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <a href="{{ route('home') }}" wire:navigate class="font-bold text-lg text-gray-900">
                        Hindustan Vidyut Udyog
                    </a>
                    <nav class="hidden sm:flex items-center gap-6 text-sm font-medium text-gray-600">
                        <a href="{{ route('about') }}" wire:navigate class="hover:text-gray-900">{{ __('About') }}</a>
                        <a href="{{ route('services') }}" wire:navigate class="hover:text-gray-900">{{ __('Services') }}</a>
                        <a href="{{ route('products') }}" wire:navigate class="hover:text-gray-900">{{ __('Products') }}</a>
                        <a href="{{ route('gallery') }}" wire:navigate class="hover:text-gray-900">{{ __('Gallery') }}</a>
                        <a href="{{ route('blog.index') }}" wire:navigate class="hover:text-gray-900">{{ __('Blog') }}</a>
                        <a href="{{ route('contact') }}" wire:navigate class="hover:text-gray-900">{{ __('Contact') }}</a>
                    </nav>
                    <div class="flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" wire:navigate class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Dashboard') }}</a>
                        @else
                            <a href="{{ route('login') }}" wire:navigate class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('Login') }}</a>
                        @endauth
                        <a href="{{ route('quote.create') }}" wire:navigate
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                            {{ __('Get a Quote') }}
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="bg-gray-900 text-gray-300 mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 sm:grid-cols-3 gap-8 text-sm">
                <div>
                    <div class="font-bold text-white mb-2">{{ __('Hindustan Vidyut Udyog') }}</div>
                    <p>{{ \App\Models\CompanySetting::get('address', __('Residential solar power installation.')) }}</p>
                </div>
                <div>
                    <div class="font-semibold text-white mb-2">{{ __('Contact') }}</div>
                    <p>{{ \App\Models\CompanySetting::get('contact_phone', '') }}</p>
                    <p>{{ \App\Models\CompanySetting::get('contact_email', '') }}</p>
                </div>
                <div>
                    <div class="font-semibold text-white mb-2">{{ __('Links') }}</div>
                    <ul class="space-y-1">
                        <li><a href="{{ route('services') }}" wire:navigate class="hover:text-white">{{ __('Services') }}</a></li>
                        <li><a href="{{ route('blog.index') }}" wire:navigate class="hover:text-white">{{ __('Blog') }}</a></li>
                        <li><a href="{{ route('contact') }}" wire:navigate class="hover:text-white">{{ __('Contact') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 py-4 text-center text-xs text-gray-500">
                &copy; {{ now()->year }} {{ __('Hindustan Vidyut Udyog. All rights reserved.') }}
            </div>
        </footer>
    </body>
</html>
