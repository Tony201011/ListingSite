<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingContentReport extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_UNDER_REVIEW = 'under_review';

    public const STATUS_MORE_INFORMATION_REQUIRED = 'more_information_required';

    public const STATUS_ACTION_TAKEN = 'action_taken';

    public const STATUS_CLOSED = 'closed';

    public const PRIORITY_HIGH = 'high';

    public const PRIORITY_MEDIUM = 'medium';

    public const PRIORITY_NORMAL = 'normal';

    protected $fillable = [
        'listing_id',
        'listing_url',
        'advertiser_name',
        'listing_phone',
        'listing_location',
        'category',
        'reporter_name',
        'reporter_email',
        'reporter_phone',
        'is_anonymous',
        'description',
        'uploaded_evidence',
        'is_urgent',
        'is_person_shown',
        'priority_level',
        'status',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'uploaded_evidence' => 'array',
            'is_urgent' => 'boolean',
            'is_person_shown' => 'boolean',
        ];
    }

    public static function categoryOptions(): array
    {
        return [
            'inappropriate_image_or_content' => 'Inappropriate image or content',
            'non_consensual_image_or_video' => 'Non-consensual image or video',
            'fake_profile_or_impersonation' => 'Fake profile / impersonation',
            'stolen_photos' => 'Stolen photos',
            'underage_or_age_concern' => 'Underage or age concern',
            'scam_or_fraudulent_activity' => 'Scam or fraudulent activity',
            'misleading_advertisement' => 'Misleading advertisement',
            'harassment_or_abuse' => 'Harassment or abuse',
            'privacy_violation' => 'Privacy violation',
            'illegal_or_prohibited_service' => 'Illegal or prohibited service',
            'review_abuse' => 'Review abuse',
            'copyright_complaint' => 'Copyright complaint',
            'other' => 'Other',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_MORE_INFORMATION_REQUIRED => 'More Information Required',
            self::STATUS_ACTION_TAKEN => 'Action Taken',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    public static function priorityOptions(): array
    {
        return [
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_NORMAL => 'Normal',
        ];
    }
}
