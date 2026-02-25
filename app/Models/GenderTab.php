<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenderTab extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'slug',
        'sort_order',
        'is_active',
    ];
}
