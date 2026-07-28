<?php

use App\Enums\BlogStatus;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public BlogPost $post;

    public function mount(BlogPost $post): void
    {
        abort_unless($post->status === BlogStatus::Published, 404);
        $this->post = $post;
    }

    public function with(): array
    {
        return [
            'related' => BlogPost::where('status', BlogStatus::Published)
                ->where('id', '!=', $this->post->id)
                ->latest('published_at')
                ->limit(3)
                ->get(),
        ];
    }
}; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    @if ($post->cover_image_path)
        <img src="{{ Storage::disk('public')->url($post->cover_image_path) }}" class="w-full h-64 object-cover rounded-lg mb-8">
    @endif

    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $post->title }}</h1>
    <div class="text-sm text-gray-400 mb-8">{{ $post->author->name }} — {{ $post->published_at?->format('d M Y') }}</div>

    <div class="prose max-w-none text-gray-700">
        {!! nl2br(e($post->body)) !!}
    </div>

    @if ($related->isNotEmpty())
        <div class="mt-16 border-t pt-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('More Posts') }}</h2>
            <ul class="space-y-2">
                @foreach ($related as $r)
                    <li><a href="{{ route('blog.show', $r) }}" wire:navigate class="text-indigo-600 hover:underline">{{ $r->title }}</a></li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
