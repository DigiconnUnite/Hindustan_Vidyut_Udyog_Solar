<?php

use App\Models\JobDocument;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        return [
            'documents' => JobDocument::whereHas('job', fn ($q) => $q->where('customer_id', auth()->id()))
                ->with('job')
                ->latest('uploaded_at')
                ->get(),
        ];
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('My Documents') }}</h2>
</x-slot>

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <ul class="divide-y divide-gray-100">
                @forelse ($documents as $doc)
                    <li class="py-3 flex justify-between items-center text-sm">
                        <a href="{{ Storage::disk('public')->url($doc->file_path) }}" target="_blank" class="text-indigo-600 hover:underline">
                            {{ ucfirst(str_replace('_', ' ', $doc->document_type->value)) }} — {{ $doc->description ?? basename($doc->file_path) }}
                        </a>
                        <span class="text-gray-400">{{ $doc->uploaded_at->format('d M Y') }}</span>
                    </li>
                @empty
                    <li class="py-3 text-sm text-gray-500">{{ __('No documents yet.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
