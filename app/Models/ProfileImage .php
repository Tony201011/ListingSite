<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileImage extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'image_path',
        'thumbnail_path',
        'is_primary',           // added
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_primary' => 'boolean',   // cast to boolean
    ];

    /**
     * Get the user that owns the profile image.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
