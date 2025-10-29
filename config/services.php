<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'mandrill' => [
        'secret' => '',
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
        'url' => env('RECAPTCHA_URL', 'https://www.google.com/recaptcha/api/siteverify'),
    ],

    // config/services.php
    'aws' => [
        'region' => env('AWS_REGION', 'us-east-2'),
        'credentials' => [
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
        ],
        'export_credentials' => [
            'key' => env('AWS_EXPORT_ACCESS_KEY_ID'),
            'secret' => env('AWS_EXPORT_SECRET_ACCESS_KEY'),
        ],
        // LAMBDA FUNCTION NAMES
        'lambda_export_function' => env('AWS_LAMBDA_EXPORT_FUNCTION', 'BiospexExportProcessor'),
        'lambda_reconciliation_function' => env('AWS_LAMBDA_RECONCILIATION_FUNCTION', 'labelReconciliations'),
        'lambda_ocr_function' => env('AWS_LAMBDA_OCR_FUNCTION', 'tesseractOcr'),

        // NEW KEYS
        'lambda_export_count' => env('AWS_LAMBDA_EXPORT_COUNT', 10),
        'lambda_qualifier' => env('AWS_LAMBDA_QUALIFIER', ''),
        'lambda_ocr_count' => env('AWS_LAMBDA_OCR_COUNT', 100),

        // SQS QUEUE NAMES
        'queue_image_tasks' => env('AWS_SQS_IMAGE_TASKS_QUEUE'),
        'queue_updates' => env('AWS_SQS_UPDATES_QUEUE'),
    ],
];
