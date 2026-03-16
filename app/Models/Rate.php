<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'incall',
        'outcall',
        'extra',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
