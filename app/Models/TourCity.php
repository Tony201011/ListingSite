<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourCity extends Model
{
    use SoftDeletes;

    protected $table = 'tour_cities';

    protected $fillable = ['name', 'state', 'country_code'];
}
