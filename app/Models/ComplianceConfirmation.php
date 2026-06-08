<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceConfirmation extends Model
{
    use HasFactory;

    public const TYPE_AGE_CONTENT_OWNERSHIP = 'age_content_ownership';

    public const TYPE_CONTENT_POLICY = 'content_policy';

    public const CONTEXT_SIGNUP = 'signup';

    public const CONTEXT_PROFILE_CREATION = 'profile_creation';

    protected $fillable = [
        'user_id',
        'provider_profile_id',
        'confirmation_type',
        'context',
        'accepted',
        'accepted_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'accepted' => 'boolean',
            'accepted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }
}
