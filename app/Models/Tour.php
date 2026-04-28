<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Add this

class Tour extends Model
{
    use HasFactory, SoftDeletes; // Add SoftDeletes

    protected $fillable = [
        'user_id',
        'provider_profile_id',
        'city',
        'from',
        'to',
        'description',
        'enabled',
    ];

    protected $casts = [
        'from' => 'datetime',
        'to' => 'datetime',
        'enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
