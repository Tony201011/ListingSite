<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'logo_type',
        'logo_path',
        'logo_max_width',
        'logo_max_height',
        'brand_primary',
        'brand_accent',
        'enable_top_bar',
        'top_left_items',
        'top_right_links',
        'enable_search',
        'action_links',
        'main_nav_links',
        'mobile_extra_links',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'logo_type' => 'string',
            'logo_path' => 'string',
            'logo_max_width' => 'integer',
            'logo_max_height' => 'integer',
            'enable_top_bar' => 'boolean',
            'top_left_items' => 'array',
            'top_right_links' => 'array',
            'enable_search' => 'boolean',
            'action_links' => 'array',
            'main_nav_links' => 'array',
            'mobile_extra_links' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
