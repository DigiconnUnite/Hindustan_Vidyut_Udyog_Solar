<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public bool $showCreateForm = false;
    public string $name = '';
    public string $email = '';
    public string $role = 'staff';

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
    }

    public function createUser(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,staff,technician'],
        ]);

        // Provisioned accounts get a random password; the user resets it via
        // the standard "forgot password" flow before first login.
        User::create($validated + ['password' => Str::random(32)]);

        $this->reset(['name', 'email', 'showCreateForm']);
        $this->role = 'staff';

        session()->flash('status', __('User created. They should use "Forgot password" to set their password.'));
    }

    public function toggleActive(int $userId): void
    {
        if ($userId === auth()->id()) {
            return;
        }

        $user = User::findOrFail($userId);
        $user->update(['is_active' => ! $user->is_active]);
    }

    public function with(): array
    {
        return ['users' => User::whereIn('role', ['admin', 'staff', 'technician'])->orderBy('name')->get()];
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Users & Roles') }}</h2>
</x-slot>

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if (session('status'))
            <div class="bg-green-50 text-green-700 text-sm rounded-md p-4">{{ session('status') }}</div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr class="text-left text-gray-500 uppercase text-xs">
                        <th class="px-3 py-2">{{ __('Name') }}</th>
                        <th class="px-3 py-2">{{ __('Email') }}</th>
                        <th class="px-3 py-2">{{ __('Role') }}</th>
                        <th class="px-3 py-2">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-3 py-2 text-gray-500">{{ $user->email }}</td>
                            <td class="px-3 py-2">{{ ucfirst($user->role->value) }}</td>
                            <td class="px-3 py-2">
                                <button wire:click="toggleActive({{ $user->id }})" type="button" @disabled($user->id === auth()->id())
                                    class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium disabled:opacity-50 {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $user->is_active ? __('Active') : __('Inactive') }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            @if (! $showCreateForm)
                <button wire:click="$set('showCreateForm', true)" type="button" class="text-sm text-indigo-600 hover:underline">{{ __('+ Add User') }}</button>
            @else
                <form wire:submit="createUser" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input wire:model="email" id="email" type="email" class="block mt-1 w-full" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="role" :value="__('Role')" />
                            <select wire:model="role" id="role" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                <option value="admin">{{ __('Admin') }}</option>
                                <option value="staff">{{ __('Staff') }}</option>
                                <option value="technician">{{ __('Technician') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <x-primary-button>{{ __('Create User') }}</x-primary-button>
                        <button type="button" wire:click="$set('showCreateForm', false)" class="text-sm text-gray-600 self-center">{{ __('Cancel') }}</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
