<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use MassPrunable;

    protected $fillable = [
        'code',
        'content',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'content' => 'encrypted',
        ];
    }

    public function prunable(): \Illuminate\Database\Eloquent\Builder
    {
        return static::query()->where('expires_at', '<=', now());
    }
}
