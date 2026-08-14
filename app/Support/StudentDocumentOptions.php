<?php

namespace App\Support;

class StudentDocumentOptions
{
    public const CATEGORIES = [
        'insurance_payment' => 'Insurance Payment',
        'tuition_fee_payment' => 'Tuition Fee Payment',
        'dormitory_fee_payment' => 'Dormitory Fee Payment',
        'msp_fee_payment' => 'MSP Fee Payment',
        'student_activity_contribution' => 'Student Activity and Welfare Contribution',
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

    public static function categoriesForAdmin(?string $role): array
    {
        return $role === 'discipline_admin'
            ? array_intersect_key(self::CATEGORIES, ['insurance_payment' => true])
            : self::CATEGORIES;
    }

    public static function adminCanAccessCategory(?string $role, string $category): bool
    {
        return array_key_exists($category, self::categoriesForAdmin($role));
    }
}
