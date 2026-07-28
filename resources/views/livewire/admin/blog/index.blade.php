<?php

use App\Models\BlogPost;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->isStaff(), 403);
    }

    public function with(): array
    {
        return ['posts' => BlogPost::with('author')->latest()->get()];
    }
}; ?>

<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Blog') }}</h2>
        <a href="{{ route('admin.blog.create') }}" wire:navigate
           class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
            {{ __('+ New Post') }}
        </a>
    </div>
</x-slot>

<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr class="text-left text-gray-500 uppercase text-xs">
                        <th class="px-3 py-2">{{ __('Title') }}</th>
                        <th class="px-3 py-2">{{ __('Status') }}</th>
                        <th class="px-3 py-2">{{ __('Author') }}</th>
                        <th class="px-3 py-2">{{ __('Published') }}</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($posts as $post)
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-900">{{ $post->title }}</td>
                            <td class="px-3 py-2">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $post->status->value === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                    {{ ucfirst($post->status->value) }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-gray-500">{{ $post->author->name }}</td>
                            <td class="px-3 py-2 text-gray-500">{{ $post->published_at?->format('d M Y') ?? '—' }}</td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ route('admin.blog.edit', $post) }}" wire:navigate class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">{{ __('No posts yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
