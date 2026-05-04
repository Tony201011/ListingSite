<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ListingReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'provider_listing_id',
        'reporter_name',
        'reporter_email',
        'reason',
        'description',
        'status',
        'is_read',
        'admin_reply',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'replied_at' => 'datetime',
        ];
    }

    public function providerListing(): BelongsTo
    {
        return $this->belongsTo(ProviderListing::class);
    }
}
