<?php

use App\Enums\BlogStatus;
use App\Models\BlogPost;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public BlogPost $post;

    public string $title = '';
    public string $slug = '';
    public string $body = '';
    public string $status = 'draft';
    public $cover_image = null;

    public function mount(BlogPost $post): void
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->isStaff(), 403);

        $this->post = $post;
        $this->title = $post->title;
        $this->slug = $post->slug;
        $this->body = $post->body;
        $this->status = $post->status->value;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:blog_posts,slug,'.$this->post->id],
            'body' => ['required', 'string'],
            'status' => ['required', 'in:'.implode(',', array_column(BlogStatus::cases(), 'value'))],
            'cover_image' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($this->cover_image) {
            $validated['cover_image_path'] = $this->cover_image->store('blog', 'public');
        }
        unset($validated['cover_image']);

        $wasPublished = $this->post->status === BlogStatus::Published;
        $nowPublished = $validated['status'] === 'published';

        $this->post->update($validated + [
            'published_at' => (! $wasPublished && $nowPublished) ? now() : $this->post->published_at,
        ]);

        session()->flash('status', __('Post updated.'));

        $this->redirect(route('admin.blog.index'), navigate: true);
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Blog Post') }}</h2>
</x-slot>

<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <form wire:submit="save" class="space-y-4">
                <div>
                    <x-input-label for="title" :value="__('Title')" />
                    <x-text-input wire:model="title" id="title" class="block mt-1 w-full" required />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="slug" :value="__('Slug')" />
                    <x-text-input wire:model="slug" id="slug" class="block mt-1 w-full" required />
                    <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="cover_image" :value="__('Replace Cover Image')" />
                    @if ($post->cover_image_path)
                        <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($post->cover_image_path) }}" class="h-16 mt-1 rounded">
                    @endif
                    <input type="file" wire:model="cover_image" id="cover_image" class="block mt-1 text-sm">
                    <x-input-error :messages="$errors->get('cover_image')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="body" :value="__('Body')" />
                    <textarea wire:model="body" id="body" rows="10" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    <x-input-error :messages="$errors->get('body')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="status" :value="__('Status')" />
                    <select wire:model="status" id="status" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                        @foreach (BlogStatus::cases() as $s)
                            <option value="{{ $s->value }}">{{ ucfirst($s->value) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.blog.index') }}" wire:navigate class="text-sm text-gray-600 self-center">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
