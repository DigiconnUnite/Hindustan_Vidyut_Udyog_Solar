<?php

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->isStaff(), 403);
    }

    public function toggleActive(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $product->update(['is_active' => ! $product->is_active]);
    }

    public function with(): array
    {
        return ['products' => Product::orderBy('capacity_kw')->get()];
    }
}; ?>

<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Products') }}</h2>
        <a href="{{ route('admin.products.create') }}" wire:navigate
           class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
            {{ __('+ Add Product') }}
        </a>
    </div>
</x-slot>

<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr class="text-left text-gray-500 uppercase text-xs">
                        <th class="px-3 py-2">{{ __('Name') }}</th>
                        <th class="px-3 py-2">{{ __('Capacity') }}</th>
                        <th class="px-3 py-2">{{ __('Indicative Price') }}</th>
                        <th class="px-3 py-2">{{ __('Active') }}</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($products as $product)
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-900">{{ $product->name }}</td>
                            <td class="px-3 py-2">{{ $product->capacity_kw }} kW</td>
                            <td class="px-3 py-2">{{ $product->price_indicative ? '₹'.number_format($product->price_indicative) : '—' }}</td>
                            <td class="px-3 py-2">
                                <button wire:click="toggleActive({{ $product->id }})" type="button"
                                    class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $product->is_active ? __('Active') : __('Inactive') }}
                                </button>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ route('admin.products.edit', $product) }}" wire:navigate class="text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">{{ __('No products yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
