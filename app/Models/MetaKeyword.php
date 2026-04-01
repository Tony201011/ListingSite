<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetaKeyword extends Model
{
    use SoftDeletes;

    protected $table = 'meta_keywords';

    protected $fillable = [
        'page_name',
        'meta_keyword',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

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
}
