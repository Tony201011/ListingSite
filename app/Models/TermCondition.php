<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected function content(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value
                ? str_replace('http://localhost', rtrim(config('app.url'), '/'), $value)
                : $value,
        );
    }
}
