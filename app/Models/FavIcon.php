<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FavIcon extends Model
{
    use HasFactory;

    protected $table = 'fav_icons';

    protected $fillable = [
        'icon_path', // Path or URL to the favicon file
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
