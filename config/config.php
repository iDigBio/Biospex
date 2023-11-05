<?php

return [

    /**
     * Site variables
     */
    'app_env'         => env('APP_ENV'),
    'app_domain'      => env('APP_DOMAIN'),
    'app_url'         => env('APP_URL'),
    'api_domain'      => env('API_DOMAIN'),
    'api_url'         => env('API_URL'),
    'api_version'     => env('API_VERSION'),
    'api_token'       => env('API_TOKEN'),
    'current_path'    => env('CURRENT_PATH'),
    'server_user'     => env('SERVER_USER'),
    'registration'    => env('REGISTRATION'),
    'expedition_size' => env('EXPEDITION_SIZE'),

    'admin_group'    => env('ADMIN_GROUP', 'Admin'),
    'admin_group_id' => env('ADMIN_GROUP_ID', 1),
    'admin_user_id'  => env('ADMIN_USER_ID', 1),

    'aws_access_key'             => env('AWS_ACCESS_KEY'),
    'aws_secret_key'             => env('AWS_SECRET_ACCESS_KEY'),
    'aws_default_region'         => env('AWS_DEFAULT_REGION'),
    'aws_lambda_export_img_url'  => env('AWS_LAMBDA_EXPORT_IMG_URL'),
    'aws_lambda_export_function' => env('AWS_LAMBDA_EXPORT_FUNCTION'),
    'aws_lambda_count'           => env('AWS_LAMBDA_COUNT'),
    'aws_lambda_delay'           => env('AWS_LAMBDA_DELAY'),

    'batch_dir'   => env('BATCH_DIR', 'batch'),
    'export_dir'  => env('EXPORT_DIR', 'export'),
    'import_dir'  => env('IMPORT_DIR', 'import'),
    'report_dir'  => env('REPORT_DIR', 'report'),
    'scratch_dir' => env('SCRATCH_DIR', 'scratch'),

    // Zooniverse related
    'zooniverse' => [
        'actor_title' => env('ZOONIVERSE_ACTOR_TITLE'),
        'actor_id'      => env('ZOONIVERSE_ACTOR_ID'),
    ],

    'zooniverse_dir' => [
        'parent'         => env('ZOONIVERSE_DIR', 'zooniverse'),
        'classification' => env('ZOONIVERSE_DIR', 'zooniverse').'/classification',
        'reconcile'      => env('ZOONIVERSE_DIR', 'zooniverse').'/reconcile', // normal reconcile
        'reconciled'     => env('ZOONIVERSE_DIR', 'zooniverse').'/reconciled', // expert review
        'summary'        => env('ZOONIVERSE_DIR', 'zooniverse').'/summary',
        'transcript'     => env('ZOONIVERSE_DIR', 'zooniverse').'/transcript',
        'explained'      => env('ZOONIVERSE_DIR', 'zooniverse').'/explained',
    ],

    'nfn_file_types'              => [
        'classification',
        'transcript',
        'reconcile',
        'summary',
        'reconciled_with_expert_opinion',
    ],
    'nfn_reconcile_problem_regex' => env('NFN_RECONCILE_PROBLEM_REGEX'),

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

    'talk_api_uri' => env('ZOONIVERSE_TALK_API'),

    'nfnSearch' => [
        'eol'     => env('NFN_EOL_SEARCH'),
        'mol'     => env('NFN_MOL_SEARCH'),
        'idigbio' => env('NFN_IDIGBIO_SEARCH'),
    ],

    'nfnNotify'                 => [2 => 'NewNfnPanoptesProject'],
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
    // End Zooniverse related

    // GeoLocate related
    'geolocate' => [
        'actor_title' => env('GEOLOCATE_ACTOR_TITLE'),
        'actor_id'    => env('GEOLOCATE_ACTOR_ID'),
        'workflow_id' => env('GEOLOCATE_WORKFLOW_ID'),
        'fields_file' => resource_path('json/geolocate-fields.json'),
        'api'         => [
            'geolocate_uname'        => env('GEOLOCATE_UNAME'),
            'geolocate_token'        => env('GEOLOCATE_TOKEN'),
            'geolocate_stats_uri'    => env('GEOLOCATE_STATS_URI'),
            'geolocate_download_uri' => env('GEOLOCATE_DOWNLOAD_URI'),
        ],
        'dir'         => [
            'parent' => env('GEOLOCATE_DIR', 'geolocate'),
            'export' => env('GEOLOCATE_DIR', 'geolocate').'/export',
            'kml' => env('GEOLOCATE_DIR', 'geolocate').'/kml',
        ],
    ],
    // End GeoLocate related

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

    'poll_ocr_channel'               => env('POLL_OCR_CHANNEL'),
    'poll_export_channel'            => env('POLL_EXPORT_CHANNEL'),
    'poll_board_channel'             => env('POLL_BOARD_CHANNEL'),
    'poll_bingo_channel'             => env('POLL_BINGO_CHANNEL'),
    'poll_wedigbio_progress_channel' => env('POLL_WEDIGBIO_PROGRESS_CHANNEL'),

    'project_resources' => [
        'Website URL',
        'Video URL',
        'File Download',
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
        'geolocate'             => env('QUEUE_GEOLOCATE'),
        'import'                => env('QUEUE_IMPORT'),
        'lambda'                => env('QUEUE_LAMBDA'),
        'ocr'                   => env('QUEUE_OCR'),
        'biospex_event'         => env('QUEUE_BIOSPEX_EVENT'),
        'pusher_classification' => env('QUEUE_PUSHER_CLASSIFICATION'),
        'pusher_handler'        => env('QUEUE_PUSHER_HANDLER'),
        'wedigbio_event'        => env('QUEUE_WEDIGBIO_EVENT'),
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

    'wedigbio_start_date' => env('WEDIGBIO_START_DATE'),
    'wedigbio_end_date'   => env('WEDIGBIO_END_DATE'),

    'deployment_fields' => [
        'APP_URL',
        'APP_ENV',
        'APP_DOMAIN',
        'SERVER_USER',
        'CURRENT_PATH',
        'REDIS_HOST',
        'API_URL',
        'API_VERSION',
        'API_TOKEN',
        'NUM_PROCS',
        'QUEUE_CHART',
        'QUEUE_CLASSIFICATION',
        'QUEUE_DEFAULT',
        'QUEUE_EVENT',
        'QUEUE_GEOLOCATE',
        'QUEUE_IMPORT',
        'QUEUE_EXPORT',
        'QUEUE_RECONCILE',
        'QUEUE_SNS_IMAGE',
        'QUEUE_WORKFLOW',
        'QUEUE_OCR',
        'QUEUE_PUSHER_PROCESS',
        'QUEUE_LAMBDA',
        'QUEUE_BIOSPEX_EVENT',
        'QUEUE_PUSHER_CLASSIFICATION',
        'QUEUE_PUSHER_HANDLER',
        'QUEUE_WEDIGBIO_EVENT',
    ],
];