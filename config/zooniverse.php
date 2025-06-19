<?php

return [
    'enabled' => env('ZOONIVERSE_ENABLED', true),
    'actor_id' => env('ZOONIVERSE_ACTOR_ID'),
    'project_url' => env('ZOONIVERSE_PROJECT_URL'),
    'reconcile_problem_regex' => env('ZOONIVERSE_RECONCILE_PROBLEM_REGEX'),
    'talk_api_uri' => env('ZOONIVERSE_TALK_API'),
    'new_expedition_notification' => [2 => 'ZooniverseNewExpedition'],
    'participate_url' => env('ZOONIVERSE_PARTICIPATE_URL'),
    // Skip api calls for expedition ids that cause issues
    'skip_api' => env('ZOONIVERSE_SKIP_API'),
    // Skip reconcile for expedition ids that cause issues
    'skip_reconcile' => env('ZOONIVERSE_SKIP_RECONCILE'),
    'pusher' => [
        'id' => env('ZOONIVERSE_PUSHER_ID'),
        'cluster' => env('ZOONIVERSE_PUSHER_CLUSTER'),
        'channel' => env('ZOONIVERSE_PUSHER_CHANNEL'),
    ],

    'directory' => [
        'parent' => env('ZOONIVERSE_DIRECTORY', 'zooniverse'),
        'classification' => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/classification',
        'reconciled' => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/reconciled',
        'reconciled-with-expert' => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/reconciled-with-expert',
        'reconciled-with-user' => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/reconciled-with-user',
        'summary' => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/summary',
        'transcript' => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/transcript',
        'explained' => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/explained',
        'lambda-reconciliation' => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/lambda-reconciliation',
        'lambda-ocr' => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/lambda-ocr',
    ],

    'file_types' => [
        'classification',
        'transcript',
        'reconciled',
        'summary',
    ],

    'search_urls' => [
        'eol' => env('ZOONIVERSE_EOL_SEARCH'),
        'mol' => env('ZOONIVERSE_MOL_SEARCH'),
        'idigbio' => env('ZOONIVERSE_IDIGBIO_SEARCH'),
    ],

    'csv_map' => [
        'subjectId' => 'id',
        'imageName' => 'id',
        'references' => ['occurrence' => 'references'],
        'scientificName' => ['occurrence' => 'scientificName'],
        'country' => ['occurrence' => 'country'],
        'stateProvince' => ['occurrence' => 'stateProvince'],
        'county' => ['occurrence' => 'county'],
        'eol' => ['occurrence' => 'scientificName'],
        'mol' => ['occurrence' => 'scientificName'],
        'idigbio' => ['occurrence' => 'scientificName'],
        '#institutionCode' => ['occurrence' => 'institutionCode'],
        '#collectionCode' => ['occurrence' => 'collectionCode'],
        '#catalogNumber' => ['occurrence' => 'catalogNumber'],
        '#occurrenceRecordId' => ['occurrence' => ['recordID', 'recordId']],
        '#occurrernceId' => ['occurrence' => 'id'],
        '#imageId' => 'imageId',
        '#expeditionId' => '',
        '#expeditionTitle' => '',
    ],

    'export_stages' => [
        'Building Queue', // 0
        'Processing Images', // 1
        'Building CSV', // 2
        'Compressing Export File', // 3
        'Creating Report', // 4
        'Deleting Working Files', // 5
    ],

    'panoptes' => [
        'client_id' => env('ZOONIVERSE_PANOPTES_CLIENT_ID'),
        'client_secret' => env('ZOONIVERSE_PANOPTES_CLIENT_SECRET'),
        'api_uri' => env('ZOONIVERSE_PANOPTES_URI'),
        'token_uri' => env('ZOONIVERSE_PANOPTES_TOKEN_URI'),
        'redirect_uri' => env('ZOONIVERSE_PANOPTES_REDIRECT_URI'),
        'scopes' => env('ZOONIVERSE_PANOPTES_SCOPES'),
    ],

    // See TranscriptionHelper.php
    'reserved_encoded' => [
        '_id',
        'classification_id',
        'workflow_id',
        'user_name',
        'create_date',
        'classification_started_at',
        'classification_finished_at',
        'updated_at',
        'created_at',
        'problem',
        'columns',
        'reviewed',
        'Country',
        'County',
        'Location',
    ],
    'mapped_transcription_fields' => [
        'province' => [
            'StateProvince',
            'State_Province',
            'State Province',
            'State/Province',
            'subject_stateProvince',
        ],
        'collector' => [
            'Collected By',
            'Collected_By',
            'CollectedBy',
            'Collected By (first collector only)',
            'subject_collectedBy',
        ],
        'taxon' => [
            'Scientific Name',
            'Scientific_Name',
            'ScientificName',
            'subject_scientificName',
        ],
    ],

    'reconcile' => [
        'file_path' => env('ZOONIVERSE_RECONCILE_FILE_PATH'),
        'python_path' => env('ZOONIVERSE_RECONCILE_PYTHON_PATH'),
    ],
];
