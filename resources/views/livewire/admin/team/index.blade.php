<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
        return [
            'team' => User::whereIn('role', ['staff', 'technician'])
                ->withCount(['jobTeamAssignments as active_job_count' => fn ($q) => $q->whereNull('removed_at')])
                ->orderBy('name')
                ->get(),
        ];
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Team') }}</h2>
</x-slot>

<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr class="text-left text-gray-500 uppercase text-xs">
                        <th class="px-3 py-2">{{ __('Name') }}</th>
                        <th class="px-3 py-2">{{ __('Role') }}</th>
                        <th class="px-3 py-2">{{ __('Email') }}</th>
                        <th class="px-3 py-2">{{ __('Active Jobs') }}</th>
                        <th class="px-3 py-2">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($team as $member)
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-900">{{ $member->name }}</td>
                            <td class="px-3 py-2">{{ ucfirst($member->role->value) }}</td>
                            <td class="px-3 py-2 text-gray-500">{{ $member->email }}</td>
                            <td class="px-3 py-2">{{ $member->active_job_count }}</td>
                            <td class="px-3 py-2">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $member->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $member->is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
