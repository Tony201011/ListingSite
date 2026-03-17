<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProfileMessage extends Model
{
        protected $fillable = [
        'user_id',
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
