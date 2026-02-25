<?php
// app/Models/MetaDescription.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'is_active'
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
     * Get the truncated description for display.
     *
     * @param int $length
     * @return string
     */
    // public function getTruncatedDescriptionAttribute(int $length = 100): string
    // {
    //     if (empty($this->meta_description)) {
    //         return '';
    //     }

    //     return strlen($this->meta_description) > $length
    //         ? substr($this->meta_description, 0, $length) . '...'
    //         : $this->meta_description;
    // }

    /**
     * Get the description word count.
     *
     * @return int
     */
    // public function getWordCountAttribute(): int
    // {
    //     if (empty($this->meta_description)) {
    //         return 0;
    //     }

    //     return str_word_count($this->meta_description);
    // }

    /**
     * Get the description character count.
     *
     * @return int
     */
    // public function getCharacterCountAttribute(): int
    // {
    //     if (empty($this->meta_description)) {
    //         return 0;
    //     }

    //     return strlen($this->meta_description);
    // }

    /**
     * Check if description is within SEO limits.
     * SEO best practice: 150-160 characters
     *
     * @return bool
     */
    // public function getIsSeoOptimalAttribute(): bool
    // {
    //     $length = $this->character_count;
    //     return $length >= 150 && $length <= 160;
    // }

    /**
     * Get SEO recommendation based on length.
     *
     * @return string
     */
    // public function getSeoRecommendationAttribute(): string
    // {
    //     $length = $this->character_count;

    //     if ($length < 150) {
    //         return 'Description is too short. Aim for 150-160 characters.';
    //     } elseif ($length > 160) {
    //         return 'Description is too long. Aim for 150-160 characters.';
    //     } elseif ($length >= 150 && $length <= 160) {
    //         return 'Perfect! Description length is optimal for SEO.';
    //     }

    //     return 'Aim for 150-160 characters for best SEO results.';
    // }

    /**
     * Get the SEO score (0-100).
     *
     * @return int
     */
    // public function getSeoScoreAttribute(): int
    // {
    //     $score = 0;
    //     $length = $this->character_count;

    //     // Length score (40 points)
    //     if ($length >= 150 && $length <= 160) {
    //         $score += 40;
    //     } elseif ($length >= 140 && $length <= 170) {
    //         $score += 30;
    //     } elseif ($length >= 120 && $length <= 180) {
    //         $score += 20;
    //     } elseif ($length >= 100 && $length <= 200) {
    //         $score += 10;
    //     }

    //     // Content score (60 points)
    //     if (!empty($this->meta_description)) {
    //         // Has keywords from the page name (basic check)
    //         $pageWords = str_replace(['-', '_'], ' ', $this->page_name);
    //         $pageWordsArray = explode(' ', $pageWords);

    //         foreach ($pageWordsArray as $word) {
    //             if (strlen($word) > 2 && stripos($this->meta_description, $word) !== false) {
    //                 $score += 15;
    //                 break;
    //             }
    //         }

    //         // Capitalized first letter
    //         if (!empty($this->meta_description) && ctype_upper(substr($this->meta_description, 0, 1))) {
    //             $score += 10;
    //         }

    //         // Has proper ending
    //         if (in_array(substr($this->meta_description, -1), ['.', '!', '?'])) {
    //             $score += 10;
    //         }
    //     }

    //     return min($score, 100);
    // }

    /**
     * Get the SEO score class for styling.
     *
     * @return string
     */
    // public function getSeoScoreClassAttribute(): string
    // {
    //     $score = $this->seo_score;

    //     if ($score >= 80) {
    //         return 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100';
    //     } elseif ($score >= 60) {
    //         return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100';
    //     } elseif ($score >= 40) {
    //         return 'bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100';
    //     } else {
    //         return 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100';
    //     }
    // }

    /**
     * Scope a query to only include active descriptions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public function scopeActive($query)
    // {
    //     return $query->where('is_active', true);
    // }

    /**
     * Scope a query to only include inactive descriptions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public function scopeInactive($query)
    // {
    //     return $query->where('is_active', false);
    // }

    /**
     * Scope a query to filter by page name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $pageName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public function scopeForPage($query, string $pageName)
    // {
    //     return $query->where('page_name', $pageName);
    // }

    /**
     * Scope a query to filter by multiple page names.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $pageNames
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public function scopeForPages($query, array $pageNames)
    // {
    //     return $query->whereIn('page_name', $pageNames);
    // }

    /**
     * Scope a query to search by description or page name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public function scopeSearch($query, string $search)
    // {
    //     return $query->where(function ($q) use ($search) {
    //         $q->where('page_name', 'like', "%{$search}%")
    //           ->orWhere('meta_description', 'like', "%{$search}%");
    //     });
    // }

    /**
     * Scope a query to get descriptions with optimal SEO length.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public function scopeOptimalSeo($query)
    // {
    //     return $query->whereRaw('LENGTH(meta_description) BETWEEN 150 AND 160');
    // }

    /**
     * Scope a query to get descriptions that are too short.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public function scopeTooShort($query)
    // {
    //     return $query->whereRaw('LENGTH(meta_description) < 150');
    // }

    /**
     * Scope a query to get descriptions that are too long.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public function scopeTooLong($query)
    // {
    //     return $query->whereRaw('LENGTH(meta_description) > 160');
    // }

    /**
     * Scope a query to order by most recent.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to order by oldest.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    /**
     * Get the page name with proper formatting.
     *
     * @return string
     */
    public function getDisplayPageNameAttribute(): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $this->page_name));
    }

    /**
     * Get the status badge class.
     *
     * @return string
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->is_active
            ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
            : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100';
    }

    /**
     * Get the status text.
     *
     * @return string
     */
    public function getStatusTextAttribute(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get the meta description for a specific page.
     *
     * @param string $pageName
     * @return string|null
     */
    public static function getForPage(string $pageName): ?string
    {
        return self::where('page_name', $pageName)
            ->where('is_active', true)
            ->value('meta_description');
    }

    /**
     * Get all active meta descriptions grouped by page.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllActiveGrouped(): \Illuminate\Database\Eloquent\Collection
    {
        return self::active()
            ->latest()
            ->get()
            ->groupBy('page_name');
    }

    /**
     * Check if page has meta description.
     *
     * @param string $pageName
     * @return bool
     */
    public static function hasForPage(string $pageName): bool
    {
        return self::where('page_name', $pageName)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get SEO statistics.
     *
     * @return array
     */
    public static function getSeoStats(): array
    {
        $total = self::count();
        $active = self::active()->count();
        $optimal = self::optimalSeo()->count();
        $tooShort = self::tooShort()->count();
        $tooLong = self::tooLong()->count();

        return [
            'total' => $total,
            'active' => $active,
            'active_percentage' => $total > 0 ? round(($active / $total) * 100) : 0,
            'optimal' => $optimal,
            'optimal_percentage' => $total > 0 ? round(($optimal / $total) * 100) : 0,
            'too_short' => $tooShort,
            'too_long' => $tooLong,
            'needs_improvement' => $tooShort + $tooLong,
        ];
    }

    /**
     * Get the validation rules for meta description.
     *
     * @return array
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
     *
     * @param int $id
     * @return array
     */
    public static function updateValidationRules(int $id): array
    {
        return [
            'page_name' => 'required|string|max:255|unique:meta_descriptions,page_name,' . $id,
            'meta_description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
