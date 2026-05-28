<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FavIcon extends Model
{
    use HasFactory;

    protected $table = 'fav_icons';

    protected $fillable = [
        'icon_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (FavIcon $favIcon): void {
            if ($favIcon->is_active) {
                DB::transaction(function () use ($favIcon): void {
                    static::where('id', '!=', $favIcon->id ?? 0)->update(['is_active' => false]);
                });
            }
        });
    }

    public function getMimeType(): string
    {
        $ext = strtolower(pathinfo($this->icon_path, PATHINFO_EXTENSION));

        return match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            default => 'image/x-icon',
        };
    }

    public function getPublicUrl(): string
    {
        if ($this->icon_path && Storage::disk('public')->exists($this->icon_path)) {
            return Storage::disk('public')->url($this->icon_path);
        }

        return asset('favicon.ico');
    }
}
