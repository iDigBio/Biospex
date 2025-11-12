<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Driver
    |--------------------------------------------------------------------------
    |
    | Laravel's queue API supports an assortment of back-ends via a single
    | API, giving you convenient access to each back-end using the same
    | syntax for each one. Here you may set the default queue driver.
    |
    | Supported: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'default' => env('LARAVEL_QUEUE_DRIVER', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
        ],

        'beanstalkd' => [ // default timeout 60 seconds
            'driver' => 'beanstalkd',
            'host' => 'localhost',
            'queue' => 'default',
            'retry_after' => 36000,
            'block_for' => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('AWS_QUEUE_URL'),
            'queue' => env('AWS_QUEUE'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'default',
            'expire' => 60,
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */

    'failed' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitored Queues
    |--------------------------------------------------------------------------
    |
    | These are the queues that will be monitored during deployment to check
    | if they have active jobs before restarting the queue system. This allows
    | for environment-specific queue names (e.g., dev prefixes in development).
    |
    */

    'monitored_queues' => [
        env('QUEUE_EXPORT', 'export'),
        env('QUEUE_GEOLOCATE', 'geolocate'),
        env('QUEUE_IMPORT', 'import'),
        env('QUEUE_OCR', 'ocr'),
        env('QUEUE_LAMBDA_OCR', 'lambda_ocr'),
        env('QUEUE_RECONCILE', 'reconcile'),
        env('QUEUE_SNS_RECONCILIATION', 'sns_reconciliation'),
        env('QUEUE_SNS_TESSERACT_OCR', 'sns_tesseract_ocr'),
        env('QUEUE_SERNEC_FILE', 'sernec_file'),
        env('QUEUE_SERNEC_ROW', 'sernec_row'),
    ],

];
