<?php

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public Product $product;

    public string $name = '';
    public ?float $capacity_kw = null;
    public string $description = '';
    public ?float $price_indicative = null;
    public bool $is_active = true;
    public $image = null;

    public function mount(Product $product): void
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->isStaff(), 403);

        $this->product = $product;
        $this->name = $product->name;
        $this->capacity_kw = (float) $product->capacity_kw;
        $this->description = (string) $product->description;
        $this->price_indicative = $product->price_indicative ? (float) $product->price_indicative : null;
        $this->is_active = $product->is_active;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'capacity_kw' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
            'price_indicative' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($this->image) {
            $validated['image_path'] = $this->image->store('products', 'public');
        }
        unset($validated['image']);

        $this->product->update($validated + ['is_active' => $this->is_active]);

        session()->flash('status', __('Product updated.'));

        $this->redirect(route('admin.products.index'), navigate: true);
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Product') }}</h2>
</x-slot>

<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <form wire:submit="save" class="space-y-4">
                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input wire:model="name" id="name" class="block mt-1 w-full" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="capacity_kw" :value="__('Capacity (kW)')" />
                    <x-text-input wire:model="capacity_kw" id="capacity_kw" type="number" step="0.01" class="block mt-1 w-full" required />
                    <x-input-error :messages="$errors->get('capacity_kw')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea wire:model="description" id="description" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                </div>
                <div>
                    <x-input-label for="price_indicative" :value="__('Indicative Price (₹)')" />
                    <x-text-input wire:model="price_indicative" id="price_indicative" type="number" step="0.01" class="block mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="image" :value="__('Replace Image')" />
                    @if ($product->image_path)
                        <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" class="h-16 mt-1 rounded">
                    @endif
                    <input type="file" wire:model="image" id="image" class="block mt-1 text-sm">
                    <x-input-error :messages="$errors->get('image')" class="mt-2" />
                </div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="is_active" class="rounded border-gray-300">
                    <span class="text-sm text-gray-700">{{ __('Active (visible on public site)') }}</span>
                </label>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.products.index') }}" wire:navigate class="text-sm text-gray-600 self-center">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
