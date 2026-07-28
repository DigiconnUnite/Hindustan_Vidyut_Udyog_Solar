<?php

namespace App\Models;

use App\Enums\JobStage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['job_id', 'stage', 'changed_by', 'notes', 'changed_at'])]
class JobStageHistory extends Model
{
    protected $table = 'job_stage_history';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'stage' => JobStage::class,
            'changed_at' => 'datetime',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(InstallationJob::class, 'job_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
