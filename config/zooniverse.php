<?php

return [
    'actor_id'                 => env('ZOONIVERSE_ACTOR_ID'),
    'project_url'              => env('ZOONIVERSE_PROJECT_URL'),
    'reconcile_problem_regex'  => env('ZOONIVERSE_RECONCILE_PROBLEM_REGEX'),
    'talk_api_uri'             => env('ZOONIVERSE_TALK_API'),
    'new_project_notification' => [2 => 'ZooniverseNewProject'],
    'participate_url'          => env('ZOONIVERSE_PARTICIPATE_URL'),
    // Skip api calls for expedition ids that cause issues
    'skip_api'                 => env('ZOONIVERSE_SKIP_API'),
    // Skip reconcile for expedition ids that cause issues
    'skip_reconcile'           => env('ZOONIVERSE_SKIP_RECONCILE'),
    'pusher_id'                => env('ZOONIVERSE_PUSHER_ID'),

    'directory' => [
        'parent'                 => env('ZOONIVERSE_DIRECTORY', 'zooniverse'),
        'classification'         => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/classification',
        'reconciled'             => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/reconciled',
        'reconciled-with-expert' => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/reconciled-with-expert',
        'reconciled-with-user'   => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/reconciled-with-user',
        'summary'                => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/summary',
        'transcript'             => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/transcript',
        'explained'              => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/explained',
        'lambda-reconciliation'  => env('ZOONIVERSE_DIRECTORY', 'zooniverse').'/lambda-reconciliation',
    ],

    'file_types' => [
        'classification',
        'transcript',
        'reconciled',
        'summary',
    ],

    'image_export' => [
        'image_width' => 1500,
        'image_height' => 1500,
        'image_quality' => 96,
    ],

    'search_urls' => [
        'eol'     => env('ZOONIVERSE_EOL_SEARCH'),
        'mol'     => env('ZOONIVERSE_MOL_SEARCH'),
        'idigbio' => env('ZOONIVERSE_IDIGBIO_SEARCH'),
    ],

    'csv_map' => [
        'subjectId'           => '_id',
        'imageName'           => '_id',
        'references'          => ['occurrence' => 'references'],
        'scientificName'      => ['occurrence' => 'scientificName'],
        'country'             => ['occurrence' => 'country'],
        'stateProvince'       => ['occurrence' => 'stateProvince'],
        'county'              => ['occurrence' => 'county'],
        'eol'                 => ['occurrence' => 'scientificName'],
        'mol'                 => ['occurrence' => 'scientificName'],
        'idigbio'             => ['occurrence' => 'scientificName'],
        '#institutionCode'    => ['occurrence' => 'institutionCode'],
        '#collectionCode'     => ['occurrence' => 'collectionCode'],
        '#catalogNumber'      => ['occurrence' => 'catalogNumber'],
        '#occurrenceRecordId' => ['occurrence' => ['recordID', 'recordId']],
        '#occurrernceId'      => ['occurrence' => 'id'],
        '#imageId'            => 'id',
        '#expeditionId'       => '',
        '#expeditionTitle'    => '',
    ],

    'export_stages' => [
        'Building File Queue', // 0
        'Building Image Requests', // 1
        'Processing Image Requests', // 2
        'Checking Image Process', // 3
        'Building CSV', // 4
        'Compressing Export File', // 5
        'Creating Report', // 6
        'Deleting Working Files', // 7
    ],

    'panoptes'                    => [
        'client_id'     => env('ZOONIVERSE_PANOPTES_CLIENT_ID'),
        'client_secret' => env('ZOONIVERSE_PANOPTES_CLIENT_SECRET'),
        'api_uri'       => env('ZOONIVERSE_PANOPTES_URI'),
        'token_uri'     => env('ZOONIVERSE_PANOPTES_TOKEN_URI'),
        'redirect_uri'  => env('ZOONIVERSE_PANOPTES_REDIRECT_URI'),
        'scopes'        => env('ZOONIVERSE_PANOPTES_SCOPES'),
    ],

    // See TranscriptionHelper.php
    'reserved_encoded'            => [
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
        'province'  => [
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
        'taxon'     => [
            'Scientific Name',
            'Scientific_Name',
            'ScientificName',
            'subject_scientificName',
        ],
    ],

    'reconcile' => [
        'file_path'   => env('ZOONIVERSE_RECONCILE_FILE_PATH'),
        'python_path' => env('ZOONIVERSE_RECONCILE_PYTHON_PATH'),
    ],
];