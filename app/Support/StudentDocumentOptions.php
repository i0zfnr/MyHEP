<?php

namespace App\Support;

class StudentDocumentOptions
{
    public const CATEGORIES = [
        'letters' => 'Letters',
        'receipts' => 'Receipts',
        'scholarship' => 'Scholarship',
        'official_notices' => 'Official Notices',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    public static function categoryLabel(string $category): string
    {
        return __(self::CATEGORIES[$category] ?? $category);
    }

    public static function statusLabel(string $status): string
    {
        return __(self::STATUSES[$status] ?? $status);
    }
}
