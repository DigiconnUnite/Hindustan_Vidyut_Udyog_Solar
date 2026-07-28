<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['job_id', 'uploaded_by', 'file_path', 'document_type', 'description', 'uploaded_at'])]
class JobDocument extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'uploaded_at' => 'datetime',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(InstallationJob::class, 'job_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
