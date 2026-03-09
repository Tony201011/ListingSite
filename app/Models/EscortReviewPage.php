<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EscortReviewPage extends Model
{
    protected $table = 'escort_review_pages';
    protected $fillable = ['content'];
    public $timestamps = false;
}
