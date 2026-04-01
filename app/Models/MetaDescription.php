<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetaDescription extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'meta_descriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'page_name',
        'meta_description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Scope a query to order by most recent.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to order by oldest.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    /**
     * Get the page name with proper formatting.
     */
    public function getDisplayPageNameAttribute(): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $this->page_name));
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->is_active
            ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
            : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100';
    }

    /**
     * Get the status text.
     */
    public function getStatusTextAttribute(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get the meta description for a specific page.
     */
    public static function getForPage(string $pageName): ?string
    {
        return self::where('page_name', $pageName)
            ->where('is_active', true)
            ->value('meta_description');
    }

    /**
     * Check if page has meta description.
     */
    public static function hasForPage(string $pageName): bool
    {
        return self::where('page_name', $pageName)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get the validation rules for meta description.
     */
    public static function validationRules(): array
    {
        return [
            'page_name' => 'required|string|max:255|unique:meta_descriptions,page_name',
            'meta_description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the update validation rules for meta description.
     */
    public static function updateValidationRules(int $id): array
    {
        return [
            'page_name' => 'required|string|max:255|unique:meta_descriptions,page_name,'.$id,
            'meta_description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
