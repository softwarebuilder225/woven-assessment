<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investor extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'name',
        'age',
    ];

    protected function casts(): array
    {
        return [
            'external_id' => 'integer',
            'age' => 'integer',
        ];
    }

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }
}
