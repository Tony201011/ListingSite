<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUsPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'support_heading',
        'response_time',
        'support_email',
        'category_label',
        'enable_name_field',
        'enable_email_field',
        'enable_subject_field',
        'enable_message_field',
        'enable_map',
        'map_latitude',
        'map_longitude',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'enable_name_field' => 'boolean',
            'enable_email_field' => 'boolean',
            'enable_subject_field' => 'boolean',
            'enable_message_field' => 'boolean',
            'enable_map' => 'boolean',
            'map_latitude' => 'decimal:7',
            'map_longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }
}
