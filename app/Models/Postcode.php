<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Postcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'postcode',
        'suburb',
        'state',
        'latitude',
        'longitude',
        'postcode_type',
        'electoral_district',
    ];
}
