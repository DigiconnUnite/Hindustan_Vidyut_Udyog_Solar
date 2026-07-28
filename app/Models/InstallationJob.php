<?php

namespace App\Models;

use App\Enums\JobStage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'lead_id', 'customer_id', 'address', 'city', 'system_capacity_kw',
    'current_stage', 'assigned_staff_id', 'start_date',
    'target_completion_date', 'actual_completion_date', 'notes',
])]
class InstallationJob extends Model
{
    protected function casts(): array
    {
        return [
            'current_stage' => JobStage::class,
            'system_capacity_kw' => 'decimal:2',
            'start_date' => 'date',
            'target_completion_date' => 'date',
            'actual_completion_date' => 'date',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function stageHistory(): HasMany
    {
        return $this->hasMany(JobStageHistory::class, 'job_id')->orderBy('changed_at');
    }

    public function teamAssignments(): HasMany
    {
        return $this->hasMany(JobTeamAssignment::class, 'job_id');
    }

    public function activeTeamAssignments(): HasMany
    {
        return $this->teamAssignments()->whereNull('removed_at');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(JobDocument::class, 'job_id');
    }
}
