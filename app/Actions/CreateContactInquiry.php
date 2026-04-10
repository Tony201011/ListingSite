<?php

namespace App\Actions;

use App\Models\ContactInquiry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateContactInquiry
{
    public function __construct(
        private SendContactInquiryEmail $sendContactInquiryEmail
    ) {}

    public function execute(array $validated): ContactInquiry
    {
        $inquiry = DB::transaction(function () use ($validated): ContactInquiry {
            return ContactInquiry::create([
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'] ?? null,
                'subject' => $validated['subject'] ?? null,
                'message' => $validated['message'] ?? null,
                'status' => 'pending',
                'is_read' => false,
            ]);
        });

        try {
            $this->sendContactInquiryEmail->execute($inquiry);
        } catch (Throwable $e) {
            Log::warning('Contact inquiry email dispatch failed after inquiry was saved.', [
                'contact_inquiry_id' => $inquiry->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $inquiry;
    }
}
