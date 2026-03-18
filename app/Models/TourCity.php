<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourCity extends Model
{
    protected $table = 'tour_cities';

    protected $fillable = ['name', 'state', 'country_code'];
}
