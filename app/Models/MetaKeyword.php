<?php
// app/Models/MetaKeyword.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetaKeyword extends Model
{
    use SoftDeletes;




    // Accessor for form: convert comma-separated string to array
    public function getMetaKeywordAttribute($value)
    {
        return $value ? array_map('trim', explode(',', $value)) : [];
    }

    // Mutator for form: convert array to comma-separated string
    public function setMetaKeywordAttribute($value)
    {
        $this->attributes['meta_keyword'] = is_array($value) ? implode(',', $value) : $value;
    }
    use SoftDeletes;

    protected $table = 'meta_keywords';

    protected $fillable = [
        'page_name',
        'meta_keyword',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // Scope for active keywords
    // public function scopeActive($query)
    // {
    //     return $query->where('is_active', true);
    // }

    // // Scope for specific page
    // public function scopeForPage($query, $pageName)
    // {
    //     return $query->where('page_name', $pageName);
    // }

    // // Get keywords as array
    // public function getKeywordsArrayAttribute(): array
    // {
    //     return $this->meta_keyword
    //         ? array_map('trim', explode(',', $this->meta_keyword))
    //         : [];
    // }

    // // Get formatted keywords for display
    // public function getFormattedKeywordsAttribute(): string
    // {
    //     return implode(', ', $this->keywords_array);
    // }
}
