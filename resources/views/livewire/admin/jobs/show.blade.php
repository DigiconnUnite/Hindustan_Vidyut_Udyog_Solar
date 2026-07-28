<?php

use App\Enums\DocumentType;
use App\Enums\JobStage;
use App\Enums\JobTeamRole;
use App\Models\InstallationJob;
use App\Models\User;
use App\Notifications\JobStageChangedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public InstallationJob $job;

    public string $stageNote = '';
    public bool $showTeamForm = false;
    public string $newTeamUserId = '';
    public string $newTeamRole = 'technician';

    public $newDocumentFile = null;
    public string $newDocumentType = 'site_photo';
    public string $newDocumentDescription = '';

    public function mount(InstallationJob $job): void
    {
        Gate::authorize('view', $job);
        $this->job = $job;
    }

    public function advanceStage(): void
    {
        $nextStage = $this->job->current_stage->next();

        if ($nextStage === null) {
            $this->addError('stage', __('This job has already reached the final stage.'));

            return;
        }

        Gate::authorize('advanceStage', [$this->job, $nextStage]);

        DB::transaction(function () use ($nextStage) {
            $this->job->stageHistory()->create([
                'stage' => $nextStage,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'notes' => $this->stageNote ?: null,
            ]);

            $this->job->update([
                'current_stage' => $nextStage,
                'actual_completion_date' => $nextStage === JobStage::Handover ? now()->toDateString() : $this->job->actual_completion_date,
            ]);
        });

        $this->stageNote = '';
        $this->job->refresh();

        // Notify the customer of the stage change — FR-25, queued (database driver).
        $this->job->customer->notify(new JobStageChangedNotification($this->job));

        session()->flash('status', __('Stage advanced to :stage.', ['stage' => ucfirst(str_replace('_', ' ', $nextStage->value))]));
    }

    public function addTeamMember(): void
    {
        Gate::authorize('manageTeam', $this->job);

        $validated = $this->validate([
            'newTeamUserId' => ['required', 'exists:users,id'],
            'newTeamRole' => ['required', 'in:'.implode(',', array_column(JobTeamRole::cases(), 'value'))],
        ]);

        $this->job->teamAssignments()->create([
            'user_id' => $validated['newTeamUserId'],
            'role_on_job' => $validated['newTeamRole'],
            'assigned_at' => now(),
        ]);

        $this->reset(['newTeamUserId', 'showTeamForm']);
        $this->job->refresh();
    }

    public function removeTeamMember(int $assignmentId): void
    {
        Gate::authorize('manageTeam', $this->job);

        $this->job->teamAssignments()->whereKey($assignmentId)->update(['removed_at' => now()]);
        $this->job->refresh();
    }

    public function uploadDocument(): void
    {
        Gate::authorize('uploadDocument', $this->job);

        $validated = $this->validate([
            'newDocumentFile' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'newDocumentType' => ['required', 'in:'.implode(',', array_column(DocumentType::cases(), 'value'))],
            'newDocumentDescription' => ['nullable', 'string', 'max:255'],
        ]);

        $path = $validated['newDocumentFile']->store("jobs/{$this->job->id}", 'public');

        $this->job->documents()->create([
            'uploaded_by' => auth()->id(),
            'file_path' => $path,
            'document_type' => $validated['newDocumentType'],
            'description' => $validated['newDocumentDescription'] ?: null,
            'uploaded_at' => now(),
        ]);

        $this->reset(['newDocumentFile', 'newDocumentDescription']);
        $this->job->refresh();

        session()->flash('status', __('Document uploaded.'));
    }

    public function with(): array
    {
        return [
            'stageHistory' => $this->job->stageHistory()->with('changedBy')->get(),
            'activeTeam' => $this->job->activeTeamAssignments()->with('user')->get(),
            'documents' => $this->job->documents()->with('uploadedBy')->latest('uploaded_at')->get(),
            'availableTeamUsers' => User::whereIn('role', ['staff', 'technician'])->orderBy('name')->get(),
            'canManageTeam' => Gate::allows('manageTeam', $this->job),
            'canUploadDocument' => Gate::allows('uploadDocument', $this->job),
            'canAdvanceStage' => $this->job->current_stage->next() !== null
                && Gate::allows('advanceStage', [$this->job, $this->job->current_stage->next()]),
        ];
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Job') }} #{{ $job->id }} — {{ $job->customer->name }}
        <span class="ml-2 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
            {{ ucfirst(str_replace('_', ' ', $job->current_stage->value)) }}
        </span>
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if (session('status'))
            <div class="bg-green-50 text-green-700 text-sm rounded-md p-4">{{ session('status') }}</div>
        @endif

        {{-- Stage timeline --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex flex-wrap gap-2 mb-6">
                @foreach (JobStage::ordered() as $stage)
                    @php
                        $stageIndex = array_search($stage, JobStage::ordered(), true);
                        $currentIndex = array_search($job->current_stage, JobStage::ordered(), true);
                    @endphp
                    <div class="flex items-center">
                        <span @class([
                            'w-3 h-3 rounded-full inline-block mr-2',
                            'bg-green-500' => $stageIndex < $currentIndex,
                            'bg-indigo-600' => $stageIndex === $currentIndex,
                            'bg-gray-300' => $stageIndex > $currentIndex,
                        ])></span>
                        <span @class([
                            'text-sm',
                            'text-gray-900 font-medium' => $stageIndex <= $currentIndex,
                            'text-gray-400' => $stageIndex > $currentIndex,
                        ])>{{ ucfirst(str_replace('_', ' ', $stage->value)) }}</span>
                        @if (! $loop->last)<span class="mx-2 text-gray-300">→</span>@endif
                    </div>
                @endforeach
            </div>

            @if ($canAdvanceStage)
                <form wire:submit="advanceStage" class="flex flex-wrap items-end gap-3 border-t pt-4">
                    <div class="flex-1 min-w-[16rem]">
                        <x-input-label for="stageNote" :value="__('Note (optional)')" />
                        <x-text-input wire:model="stageNote" id="stageNote" class="block mt-1 w-full" />
                    </div>
                    <x-primary-button>
                        {{ __('Advance to :stage', ['stage' => ucfirst(str_replace('_', ' ', $job->current_stage->next()?->value ?? ''))]) }}
                    </x-primary-button>
                </form>
                <x-input-error :messages="$errors->get('stage')" class="mt-2" />
            @endif

            <div class="mt-6 border-t pt-4">
                <h4 class="text-sm font-semibold text-gray-600 mb-2">{{ __('History') }}</h4>
                <ul class="space-y-1 text-sm">
                    @foreach ($stageHistory as $entry)
                        <li class="text-gray-600">
                            <span class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $entry->stage->value)) }}</span>
                            — {{ $entry->changedBy->name }} — {{ $entry->changed_at->format('d M Y, H:i') }}
                            @if ($entry->notes) <span class="text-gray-400">— {{ $entry->notes }}</span> @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Team panel --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-4">{{ __('Team') }}</h3>
                <ul class="space-y-2 mb-4">
                    @forelse ($activeTeam as $assignment)
                        <li class="flex justify-between items-center text-sm">
                            <span>{{ $assignment->user->name }} <span class="text-gray-400">({{ ucfirst(str_replace('_', ' ', $assignment->role_on_job->value)) }})</span></span>
                            @if ($canManageTeam)
                                <button wire:click="removeTeamMember({{ $assignment->id }})" wire:confirm="{{ __('Remove from job?') }}" class="text-red-600 text-xs hover:underline">{{ __('Remove') }}</button>
                            @endif
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">{{ __('No one assigned yet.') }}</li>
                    @endforelse
                </ul>

                @if ($canManageTeam)
                    @if (! $showTeamForm)
                        <button wire:click="$set('showTeamForm', true)" type="button" class="text-sm text-indigo-600 hover:underline">{{ __('+ Assign someone') }}</button>
                    @else
                        <form wire:submit="addTeamMember" class="flex flex-wrap gap-2 items-end">
                            <select wire:model="newTeamUserId" class="border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="">{{ __('— Select —') }}</option>
                                @foreach ($availableTeamUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->value }})</option>
                                @endforeach
                            </select>
                            <select wire:model="newTeamRole" class="border-gray-300 rounded-md shadow-sm text-sm">
                                @foreach (JobTeamRole::cases() as $role)
                                    <option value="{{ $role->value }}">{{ ucfirst(str_replace('_', ' ', $role->value)) }}</option>
                                @endforeach
                            </select>
                            <x-primary-button>{{ __('Assign') }}</x-primary-button>
                        </form>
                        <x-input-error :messages="$errors->get('newTeamUserId')" class="mt-2" />
                    @endif
                @endif
            </div>

            {{-- Documents panel --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-4">{{ __('Documents') }}</h3>
                <ul class="space-y-2 mb-4">
                    @forelse ($documents as $doc)
                        <li class="text-sm flex justify-between items-center">
                            <a href="{{ Storage::disk('public')->url($doc->file_path) }}" target="_blank" class="text-indigo-600 hover:underline">
                                {{ ucfirst(str_replace('_', ' ', $doc->document_type->value)) }} — {{ $doc->description ?? basename($doc->file_path) }}
                            </a>
                            <span class="text-gray-400 text-xs">{{ $doc->uploadedBy->name }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">{{ __('No documents uploaded yet.') }}</li>
                    @endforelse
                </ul>

                @if ($canUploadDocument)
                    <form wire:submit="uploadDocument" class="space-y-2 border-t pt-4">
                        <div class="flex flex-wrap gap-2">
                            <select wire:model="newDocumentType" class="border-gray-300 rounded-md shadow-sm text-sm">
                                @foreach (\App\Enums\DocumentType::cases() as $type)
                                    <option value="{{ $type->value }}">{{ ucfirst(str_replace('_', ' ', $type->value)) }}</option>
                                @endforeach
                            </select>
                            <input type="file" wire:model="newDocumentFile" class="text-sm">
                        </div>
                        <x-text-input wire:model="newDocumentDescription" placeholder="{{ __('Description (optional)') }}" class="block w-full text-sm" />
                        <div wire:loading wire:target="newDocumentFile" class="text-xs text-gray-500">{{ __('Uploading...') }}</div>
                        <x-input-error :messages="$errors->get('newDocumentFile')" class="mt-1" />
                        <x-primary-button>{{ __('Upload') }}</x-primary-button>
                    </form>
                @endif
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="font-semibold text-gray-800 mb-2">{{ __('Job Details') }}</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div><dt class="text-gray-500">{{ __('Address') }}</dt><dd class="font-medium">{{ $job->address }}, {{ $job->city }}</dd></div>
                <div><dt class="text-gray-500">{{ __('System Capacity') }}</dt><dd class="font-medium">{{ $job->system_capacity_kw ? $job->system_capacity_kw.' kW' : '—' }}</dd></div>
                <div><dt class="text-gray-500">{{ __('Customer Phone') }}</dt><dd class="font-medium">{{ $job->customer->phone ?? '—' }}</dd></div>
            </dl>
        </div>
    </div>
</div>
