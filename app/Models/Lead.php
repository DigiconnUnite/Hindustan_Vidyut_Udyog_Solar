<?php

namespace App\Models;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\RoofType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'name', 'phone', 'email', 'address', 'city', 'roof_type',
    'monthly_bill_estimate', 'source', 'status', 'assigned_to', 'notes',
    'converted_job_id',
])]
class Lead extends Model
{
    protected function casts(): array
    {
        return [
            'roof_type' => RoofType::class,
            'source' => LeadSource::class,
            'status' => LeadStatus::class,
            'monthly_bill_estimate' => 'decimal:2',
        ];
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedJob(): BelongsTo
    {
        return $this->belongsTo(InstallationJob::class, 'converted_job_id');
    }

    public function quoteRequest(): HasOne
    {
        return $this->hasOne(QuoteRequest::class, 'converted_lead_id');
    }
}
