<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'capacity_kw', 'description', 'image_path', 'price_indicative', 'is_active'])]
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'capacity_kw' => 'decimal:2',
            'price_indicative' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
