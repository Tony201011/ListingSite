<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HideShowProfile extends Model
{
    protected $table = 'hide_show_profiles';

    protected $fillable = [
        'user_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string', // Since it's an ENUM, we treat it as string
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
