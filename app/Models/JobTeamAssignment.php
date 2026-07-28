<?php

namespace App\Models;

use App\Enums\JobTeamRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['job_id', 'user_id', 'role_on_job', 'assigned_at', 'removed_at'])]
class JobTeamAssignment extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'role_on_job' => JobTeamRole::class,
            'assigned_at' => 'datetime',
            'removed_at' => 'datetime',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(InstallationJob::class, 'job_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
