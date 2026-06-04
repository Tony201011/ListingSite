<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_profile_id',
        'provider',
        'provider_checkout_id',
        'provider_transaction_id',
        'credit_package_id',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'receipt_url',
        'credits',
        'bonus_credits',
        'amount',
        'tax_amount',
        'currency',
        'status',
        'invoice_name',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'credits' => 'integer',
        'bonus_credits' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function creditPackage(): BelongsTo
    {
        return $this->belongsTo(CreditPackage::class, 'credit_package_id');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function getFormattedAmountAttribute(): string
    {
        return '$'.number_format($this->amount, 2);
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d M Y');
    }

    public function getMonthAttribute(): string
    {
        return $this->created_at->format('Y-m');
    }

    public function getNormalizedReceiptUrlAttribute(): ?string
    {
        $url = trim((string) ($this->receipt_url ?? ''));

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '//')) {
            $url = 'https:'.$url;
        }

        if ($this->isHttpUrl($url)) {
            return $url;
        }

        if (str_contains($url, '://')) {
            return null;
        }

        $urlWithScheme = 'https://'.$url;

        if ($this->isHttpUrl($urlWithScheme)) {
            return $urlWithScheme;
        }

        return null;
    }

    private function isHttpUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower(parse_url($url, PHP_URL_SCHEME) ?? '');

        return in_array($scheme, ['http', 'https'], true);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(PurchaseComplaint::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'payment_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'payment_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'payment_id');
    }

    public function getTotalCreditsAttribute(): int
    {
        return (int) $this->credits + (int) $this->bonus_credits;
    }

    public function getPaymentReferenceAttribute(): ?string
    {
        return $this->provider_transaction_id
            ?: $this->provider_checkout_id
            ?: $this->stripe_payment_intent_id
            ?: $this->stripe_session_id;
    }
}
