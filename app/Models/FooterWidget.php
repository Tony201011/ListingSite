<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FooterWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_description',
        'badges',
        'navigation_heading',
        'navigation_links',
        'advertisers_heading',
        'advertisers_links',
        'legal_heading',
        'legal_links',
        'instagram_url',
        'twitter_url',
        'facebook_url',
        'enable_brand_widget',
        'enable_navigation_widget',
        'enable_advertisers_widget',
        'enable_legal_widget',
        'enable_promo_section',
        'promo_heading',
        'promo_description',
        'promo_button_one_label',
        'promo_button_one_url',
        'promo_button_two_label',
        'promo_button_two_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'badges' => 'array',
            'navigation_links' => 'array',
            'advertisers_links' => 'array',
            'legal_links' => 'array',
            'enable_brand_widget' => 'boolean',
            'enable_navigation_widget' => 'boolean',
            'enable_advertisers_widget' => 'boolean',
            'enable_legal_widget' => 'boolean',
            'enable_promo_section' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
