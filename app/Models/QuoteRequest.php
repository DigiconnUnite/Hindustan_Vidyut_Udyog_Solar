<?php

namespace App\Models;

use App\Enums\RoofType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name', 'phone', 'email', 'address', 'city',
    'roof_type', 'monthly_bill_estimate', 'converted_lead_id',
])]
class QuoteRequest extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'roof_type' => RoofType::class,
            'monthly_bill_estimate' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function convertedLead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'converted_lead_id');
    }
}
