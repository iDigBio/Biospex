<?php

return [

    /**
     * Site variables
     */
    'app_domain'      => env('APP_DOMAIN'),
    'api_domain'      => env('API_DOMAIN'),
    'api_url'         => env('API_URL'),
    'api_version'     => env('API_VERSION'),
    'api_token'       => env('API_TOKEN'),
    'current_path'    => env('CURRENT_PATH'),
    'server_user'     => env('SERVER_USER'),
    'registration'    => env('REGISTRATION'),
    'expedition_size' => env('EXPEDITION_SIZE'),
    'echo_id'         => env('ECHO_ID'),
    'echo_key'        => env('ECHO_KEY'),
    'echo_port'       => env('ECHO_PORT'),
    'echo_ssl_crt'    => env('ECHO_SSL_CRT'),
    'echo_ssl_key'    => env('ECHO_SSL_KEY'),

    'admin_group'    => env('ADMIN_GROUP', 'Admin'),
    'admin_group_id' => env('ADMIN_GROUP_ID', 1),

    'aws_access_key'             => env('AWS_ACCESS_KEY'),
    'aws_secret_key'             => env('AWS_SECRET_ACCESS_KEY'),
    'aws_default_region'         => env('AWS_DEFAULT_REGION'),
    'aws_lambda_export_img_url'  => env('AWS_LAMBDA_EXPORT_IMG_URL'),
    'aws_lambda_export_function' => env('AWS_LAMBDA_EXPORT_FUNCTION'),
    'aws_lambda_count'           => env('AWS_LAMBDA_COUNT'),
    'aws_lambda_delay'           => env('AWS_LAMBDA_DELAY'),
    'aws_efs_lambda_dir'         => env('AWS_EFS_LAMBDA_DIR'),
    'aws_efs_batch_dir'          => env('AWS_EFS_BATCH_DIR'),

    'charts_dir'                   => 'public/charts',
    'scratch_dir'                  => 'scratch',
    'export_dir'                   => 'exports',
    'reports_dir'                  => 'reports',
    'import_dir'                   => 'subjects',
    'nfn_downloads_dir'            => env('NFN_DOWNLOADS_DIR', 'nfndownloads'),
    'nfn_downloads_classification' => env('NFN_DOWNLOADS_DIR', 'nfndownloads').'/classification',
    'nfn_downloads_reconcile'      => env('NFN_DOWNLOADS_DIR', 'nfndownloads').'/reconcile',
    'nfn_downloads_reconciled'     => env('NFN_DOWNLOADS_DIR', 'nfndownloads').'/reconciled',
    'nfn_downloads_summary'        => env('NFN_DOWNLOADS_DIR', 'nfndownloads').'/summary',
    'nfn_downloads_transcript'     => env('NFN_DOWNLOADS_DIR', 'nfndownloads').'/transcript',
    'nfn_downloads_explained'      => env('NFN_DOWNLOADS_DIR', 'nfndownloads').'/explained',
    'nfn_file_types'               => [
        'classification',
        'transcript',
        'reconcile',
        'summary',
        'reconciled_with_expert_opinion',
    ],
    'nfn_reconcile_problem_regex'  => env('NFN_RECONCILE_PROBLEM_REGEX'),

    'missing_project_logo'    => env('APP_URL').'/images/placeholders/project.png',
    'missing_expedition_logo' => env('APP_URL').'/images/placeholders/card-image-place-holder02.jpg',
    'missing_avatar_small'    => env('APP_URL').'/images/avatars/small/missing.png',
    'missing_avatar_medium'   => env('APP_URL').'/images/avatars/medium/missing.png',

    'image_process_file' => base_path(env('IMAGE_PROCESS_FILE')),

    'python_path'    => env('RECONCILIATIONS_PATH').'/venv/bin/python',
    'reconcile_path' => env('RECONCILIATIONS_PATH').'/reconcile.py',

    'project_chart_series' => resource_path('json/projectChartSeries.json'),
    'project_chart_config' => resource_path('json/projectChartConfig.json'),

    'ocr_disable' => env('OCR_DISABLE', false),

    'poll_ocr_channel'        => env('POLL_OCR_CHANNEL'),
    'poll_export_channel'     => env('POLL_EXPORT_CHANNEL'),
    'poll_scoreboard_channel' => env('POLL_SCOREBOARD_CHANNEL'),
    'poll_bingo_channel'      => env('POLL_BINGO_CHANNEL'),

    'project_resources' => [
        'Website URL',
        'Video URL',
        'File Download',
    ],

    'export_stages'       => [
        'Building File Queue', // 0
        'Building Image Requests', // 1
        'Processing Image Requests', // 2
        'Checking Image Process', // 3
        'Building CSV', // 4
        'Compressing Export File', // 5
        'Creating Report', // 6
        'Deleting Working Files', // 7
    ],

    /**
     * iDigBio api query url
     */
    'recordset_url'       => 'https://beta-api.idigbio.org/v2/download/?rq={"recordset":"RECORDSET_ID"}',

    /**
     * DCA import row types for multimedia.
     */
    'dwcRequiredRowTypes' => [
        'http://rs.tdwg.org/ac/terms/multimedia',
        'http://rs.gbif.org/terms/1.0/image',
        'http://rs.tdwg.org/dwc/terms/occurrence',
    ],

    'dwcRequiredFields' => [
        'core'      => ['id'],
        'extension' => [
            'coreid'     => [],
            'accessURI'  => ['http://rs.tdwg.org/ac/terms/accessURI'],
            'identifier' => [
                'http://purl.org/dc/terms/identifier',
                'http://rs.tdwg.org/ac/terms/providerManagedID',
                'http://portal.idigbio.org/terms/uuid',
                'http://portal.idigbio.org/terms/recordId',
            ],
        ],
    ],

    'dwcTranscriptFields' => [
        'stateProvince'  => 'state_province',
        'StateProvince'  => 'state_province',
        'State/Province' => 'state_province',
        'State Province' => 'state_province',
        'State_Province' => 'state_province',
        'State'          => 'state_province',
        'County'         => 'county',
        'subject_county' => 'county',
    ],

    'dwcOccurrenceFields'   => [
        'stateProvince'  => 'state_province',
        'State_Province' => 'state_province',
        'State Province' => 'state_province',
        'State/Province' => 'state_province',
        'State'          => 'state_province',
        'County'         => 'county',
    ],

    /* Beanstalk Queues */
    'num_procs'             => env('NUM_PROCS'),
    'queues'                => [
        'chart'                 => env('QUEUE_CHART'),
        'classification'        => env('QUEUE_CLASSIFICATION'),
        'default'               => env('QUEUE_DEFAULT'),
        'event'                 => env('QUEUE_EVENT'),
        'export'                => env('QUEUE_EXPORT'),
        'import'                => env('QUEUE_IMPORT'),
        'lambda'                => env('QUEUE_LAMBDA'),
        'ocr'                   => env('QUEUE_OCR'),
        'pusher_transcriptions' => env('QUEUE_PUSHER_TRANSCRIPTIONS'),
        'pusher_process'        => env('QUEUE_PUSHER_PROCESS'),
        'reconcile'             => env('QUEUE_RECONCILE'),
        'sns_image'             => env('QUEUE_SNS_IMAGE'),
        'workflow'              => env('QUEUE_WORKFLOW'),
    ],

    /* Images */
    /* Min and max logo and banner sizes used in Project model. Max Zoonviverse image. Thumb sizes. */
    'thumb_default_img'     => 'thumbs/default_thumb.png',
    'thumb_output_dir'      => 'thumbs',
    'thumb_width'           => 300,
    'thumb_height'          => 300,
    'logo'                  => '300x200',
    'banner'                => '1200x250',
    'nfn_image_width'       => 1500,
    'nfn_image_height'      => 1500,

    /**
     * Columns used in select statement for grid.
     */
    'defaultGridVisible'    => [
        'id',
        'exported',
        'accessURI',
        'ocr',
    ],
    'defaultSubGridVisible' => [
        'id',
        'institutionCode',
        'scientificName',
        'recordId',
    ],

    'nfnWorkflows' => [
        env('NFN_WORKFLOWS_1'),
        env('NFN_WORKFLOWS_2'),
    ],

    'nfnActors'                 => env('NFN_ACTORS'),
    'nfnNotify'                 => [1 => 'NewNfnLegacyProject', 2 => 'NewNfnPanoptesProject'],
    'nfnSkipApi'                => env('NFN_SKIP_API'),
    // Skip csv creation for expedition ids that cause issues
    'nfnSkipReconcile'          => env('NFN_SKIP_RECONCILE'),
    // Skip csv reconciliation for expedition ids that cause issues
    'nfnTranscriptionsComplete' => env('NFN_TRANSCRIPTIONS_COMPLETE', 3),
    'nfn_participate_url'       => env('NFN_PARTICIPATE_URL'),
    'nfn_project_url'           => env('NFN_PROJECT_URL'),

    'nfnCsvMap' => [
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

    'panoptes' => [
        'clientId'     => env('PANOPTES_CLIENT_ID'),
        'clientSecret' => env('PANOPTES_CLIENT_SECRET'),
        'apiUri'       => env('PANOPTES_URI'),
        'tokenUri'     => env('PANOPTES_TOKEN_URI'),
        'redirectUri'  => env('PANOPTES_REDIRECT_URI'),
        'scopes'       => env('PANOPTES_SCOPES'),
    ],

    'talk_api_uri' => env('ZOONIVERSE_TALK_API'),

    'nfnSearch' => [
        'eol'     => env('NFN_EOL_SEARCH'),
        'mol'     => env('NFN_MOL_SEARCH'),
        'idigbio' => env('NFN_IDIGBIO_SEARCH'),
    ],

    /**
     * Default advertise fields for PPSR_CORE
     */
    'ppsr'      => [
        'ProjectGUID'             => ['private' => 'uuid'],
        'ProjectName'             => ['column' => 'title'],
        'ProjectDataProvider'     => ['value' => env('APP_NAME')],
        'ProjectDescription'      => ['column' => 'description_long'],
        'ProjectDateLastUpdated'  => ['date' => 'updated_at'],
        'ProjectContactName'      => ['column' => 'contact'],
        'ProjectContactEmail'     => ['column' => 'contact_email'],
        'ProjectStatus'           => ['column' => 'status'],
        'ProjectOrganization'     => ['column' => 'organization'],
        'ProjectVolunteerSupport' => ['column' => 'incentives'],
        'ProjectURL'              => ['url' => 'slug'],
        'ProjectFacebook'         => ['column' => 'facebook'],
        'ProjectTwitter'          => ['column' => 'twitter'],
        'ProjectKeywords'         => ['array' => ['keywords', 'geographic_scope', 'temporal_scope']],
        'fieldOfScience'          => [],
        'participationType'       => [],
        'participantEducation'    => ['column' => 'language_skills'],
        'fundingSource'           => ['column' => 'funding_source'],
        'projectBlog'             => ['column' => 'blog_url'],
        'projectImage'            => ['url' => 'logo'],
    ],

    'status_select' => [
        'starting' => 'Starting',
        'active'   => 'Active',
        'complete' => 'Complete',
        'hiatus'   => 'Hiatus',
    ],

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
];