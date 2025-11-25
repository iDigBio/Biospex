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
        // LAMBDA FUNCTION NAMES
        'lambda_export_function' => env('AWS_LAMBDA_EXPORT_FUNCTION', 'BiospexExportProcessor'),
        'lambda_reconciliation_function' => env('AWS_LAMBDA_RECONCILIATION_FUNCTION', 'labelReconciliations'),
        'lambda_ocr_function' => env('AWS_LAMBDA_OCR_FUNCTION', 'tesseractOcr'),

        // NEW KEYS
        'csv_export_count' => env('AWS_CSV_EXPORT_COUNT', 1000),
        'lambda_qualifier' => env('AWS_LAMBDA_QUALIFIER', ''),
        'lambda_ocr_count' => env('AWS_LAMBDA_OCR_COUNT', 100),

        // SQS QUEUE NAMES
        'queues' => [
            'batch_trigger' => env('AWS_SQS_BATCH_TRIGGER'),
            'batch_update' => env('AWS_SQS_BATCH_UPDATE'),
            'export_image_tasks_dlq' => env('AWS_SQS_EXPORT_IMAGE_TASKS_DLQ'),
            'export_image_tasks' => env('AWS_SQS_EXPORT_IMAGE_TASKS'),
            'export_update' => env('AWS_SQS_EXPORT_UPDATE'),
            'export_zip_trigger' => env('AWS_SQS_EXPORT_ZIP_TRIGGER'),
            'reconcile_trigger' => env('AWS_SQS_RECONCILE_TRIGGER'),
            'reconcile_update' => env('AWS_SQS_RECONCILE_UPDATE'),
            'ocr_trigger' => env('AWS_SQS_OCR_TRIGGER'),
            'ocr_update' => env('AWS_SQS_OCR_UPDATE'),
            'ocr_dlq' => env('AWS_SQS_OCR_DLQ'),
        ],

        'batch_idle_grace' => env('AWS_BATCH_IDLE_GRACE', 1800),
        'export_idle_grace' => env('AWS_EXPORT_IDLE_GRACE', 300),
        'ocr_idle_grace' => env('AWS_OCR_IDLE_GRACE', 300),
        'reconcile_idle_grace' => env('AWS_RECONCILE_IDLE_GRACE', 1800),
        'zip_threshold' => env('AWS_ZIP_THRESHOLD', 8000),
    ],
];
