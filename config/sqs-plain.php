<?php

/**
 * List of plain SQS queues and their corresponding handling classes
 */
return [
    'handlers' => [
        'imageExportResult' => \App\Jobs\SqsImageExportResultJob::class,
    ],

    'default-handler' => App\Jobs\SqsDefaultHandlerJob::class
];