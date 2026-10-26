<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductActivity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'activity_type',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new \LogicException('Product activities are immutable.');
        });
    }
}
