<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactInquiryReply extends Model
{
    protected $fillable = [
        'contact_inquiry_id',
        'message',
        'email_status',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function contactInquiry(): BelongsTo
    {
        return $this->belongsTo(ContactInquiry::class);
    }
}
