<?php

namespace App\Http;

class ValidationPatterns
{
    // String patterns
    const STRING_255 = 'required|string|max:255';
    const STRING_500 = 'required|string|max:500';
    const STRING_1000 = 'required|string|max:1000';
    const OPTIONAL_STRING_255 = 'nullable|string|max:255';
    const OPTIONAL_STRING_500 = 'nullable|string|max:500';
    const OPTIONAL_STRING_1000 = 'nullable|string|max:1000';

    // Email patterns
    const EMAIL = 'required|string|lowercase|email|max:255';
    const UNIQUE_EMAIL = 'required|string|lowercase|email|max:255|unique:users,email';
    const OPTIONAL_EMAIL = 'nullable|string|lowercase|email|max:255';

    // Password patterns
    const PASSWORD = ['required', 'confirmed'];
    const PASSWORD_UPDATE = ['nullable', 'confirmed'];

    // Date patterns
    const DATE = 'required|date';
    const DATE_AFTER_TODAY = 'required|date|after:today';
    const DATE_AFTER_OR_EQUAL_TODAY = 'required|date|after_or_equal:today';
    const OPTIONAL_DATE = 'nullable|date';

    // Numeric patterns
    const POSITIVE_INTEGER = 'required|integer|min:1';
    const NON_NEGATIVE_INTEGER = 'required|integer|min:0';
    const OPTIONAL_POSITIVE_INTEGER = 'nullable|integer|min:1';

    // ID patterns
    const EXISTING_USER_ID = 'required|exists:users,id';
    const OPTIONAL_EXISTING_USER_ID = 'nullable|exists:users,id';
    const EXISTING_ORGANIZATIONAL_UNIT_ID = 'required|exists:organizational_units,id';
    const ALLOWED_ORGANIZATIONAL_UNIT_ID = 'required|exists:organizational_units,id|in:3,4,6,7';

    // Enum patterns
    const LEAVE_TYPE = 'required|in:VACATION,SICK,PERSONAL,MATERNITY,PATERNITY,OTHER';
    const LEAVE_STATUS = 'required|in:PENDING,APPROVED,REJECTED,CANCELLED';
    const USER_ROLE = 'required|in:ADMIN_IT,HR,MANAGER,EMPLOYEE';

    // Boolean patterns
    const BOOLEAN = 'required|boolean';
    const OPTIONAL_BOOLEAN = 'nullable|boolean';

    /**
     * Get password validation rules with custom requirements
     */
    public static function password(array $customRules = []): array
    {
        return array_merge(['required', 'confirmed'], $customRules);
    }

    /**
     * Get date validation with custom after date
     */
    public static function dateAfter(string $date): string
    {
        return "required|date|after:{$date}";
    }

    /**
     * Get unique email validation for specific user (for updates)
     */
    public static function uniqueEmailExcept(int $userId): string
    {
        return "required|string|lowercase|email|max:255|unique:users,email,{$userId}";
    }

    /**
     * Get organizational unit validation for specific allowed IDs
     */
    public static function allowedOrganizationalUnits(array $allowedIds): string
    {
        return 'required|exists:organizational_units,id|in:' . implode(',', $allowedIds);
    }
}
