<?php

use App\Enums\DocumentType;
use App\Models\JobDocument;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public')] class extends Component
{
    public function with(): array
    {
        return [
            'photos' => JobDocument::where('document_type', DocumentType::CompletionPhoto)
                ->latest('uploaded_at')
                ->limit(24)
                ->get(),
        ];
    }
}; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Completed Installations') }}</h1>
    <p class="text-gray-600 mb-10">{{ __('A look at some of our recent residential solar installations.') }}</p>

    @if ($photos->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach ($photos as $photo)
                <a href="{{ Storage::disk('public')->url($photo->file_path) }}" target="_blank" class="block aspect-square rounded-lg overflow-hidden bg-gray-100">
                    <img src="{{ Storage::disk('public')->url($photo->file_path) }}" class="w-full h-full object-cover hover:scale-105 transition" loading="lazy">
                </a>
            @endforeach
        </div>
    @else
        <p class="text-gray-500 text-sm">{{ __('Gallery coming soon.') }}</p>
    @endif
</div>
