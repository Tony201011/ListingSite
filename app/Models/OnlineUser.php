<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineUser extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'online',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'online' => 'string', // Since it's an ENUM column, we treat it as string
    ];

    /**
     * Get the user that owns this online status.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
