<?php

use App\Enums\BlogStatus;
use App\Models\BlogPost;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.public')] class extends Component
{
    use WithPagination;

    public function with(): array
    {
        return [
            'posts' => BlogPost::where('status', BlogStatus::Published)
                ->latest('published_at')
                ->paginate(9),
        ];
    }
}; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="text-3xl font-bold text-gray-900 mb-10">{{ __('Blog') }}</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($posts as $post)
            <a href="{{ route('blog.show', $post) }}" wire:navigate class="block bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition">
                @if ($post->cover_image_path)
                    <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($post->cover_image_path) }}" class="w-full h-40 object-cover">
                @endif
                <div class="p-5">
                    <div class="font-semibold text-gray-900">{{ $post->title }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $post->published_at?->format('d M Y') }}</div>
                </div>
            </a>
        @empty
            <p class="text-gray-500 text-sm">{{ __('No posts yet.') }}</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $posts->links() }}</div>
</div>
