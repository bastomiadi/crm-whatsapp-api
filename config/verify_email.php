<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email Verification Enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether email verification is required for new
    | user registrations. When enabled, users must verify their email
    | address before they can login. When disabled, users can login
    | immediately after registration.
    |
    | Default: false
    |
    */
    'enabled' => env('EMAIL_VERIFICATION_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Verify Email Route
    |--------------------------------------------------------------------------
    |
    | The route name for email verification.
    |
    */
    'verify_route' => 'verification.verify',

    /*
    |--------------------------------------------------------------------------
    | Resend Verification Route
    |--------------------------------------------------------------------------
    |
    | The route name for resending verification email.
    |
    */
    'resend_route' => 'verification.resend',

];
