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
        'user_id',
        'description',
        'incall',
        'outcall',
        'extra',
        'group_id', // add this
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group()
        {
            return $this->belongsTo(RateGroup::class, 'group_id');
        }
}
