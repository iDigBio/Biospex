<?php

return [

    'app_domain'       => env('APP_DOMAIN'),
    'app_current_path' => env('APP_CURRENT_PATH'),
    'app_server_user'  => env('APP_SERVER_USER'),
    'app_registration' => env('APP_REGISTRATION'),

    'expedition_size'    => env('EXPEDITION_SIZE'),
    'pusher_app_cluster' => env('PUSHER_APP_CLUSTER'),

    'api' => [
        'domain'  => env('API_DOMAIN'),
        'url'     => env('API_URL'),
        'version' => env('API_VERSION'),
        'token'   => env('API_TOKEN'),
    ],

    'admin' => [
        'group'    => env('ADMIN_GROUP', 'Admin'),
        'group_id' => env('ADMIN_GROUP_ID', 1),
        'user_id'  => env('ADMIN_USER_ID', 1),
    ],

    'aws' => [
        'access_key'                     => env('AWS_ACCESS_KEY'),
        'secret_key'                     => env('AWS_SECRET_ACCESS_KEY'),
        'default_region'                 => env('AWS_DEFAULT_REGION'),
        'lambda_export_function'         => env('AWS_LAMBDA_EXPORT_FUNCTION'),
        'lambda_export_count'            => env('AWS_LAMBDA_EXPORT_COUNT'),
        'lambda_export_delay'            => env('AWS_LAMBDA_EXPORT_DELAY'),
        'lambda_ocr_function'            => env('AWS_LAMBDA_OCR_FUNCTION'),
        'lambda_ocr_count'               => env('AWS_LAMBDA_OCR_COUNT'),
        'lambda_reconciliation_function' => env('AWS_LAMBDA_RECONCILIATION_FUNCTION'),
    ],

    'batch_dir'   => env('BATCH_DIR', 'batch'),
    'export_dir'  => env('EXPORT_DIR', 'export'),
    'import_dir'  => env('IMPORT_DIR', 'import'),
    'ocr_dir'     => env('OCR_DIR', 'ocr'),
    'report_dir'  => env('REPORT_DIR', 'report'),
    'scratch_dir' => env('SCRATCH_DIR', 'scratch'),

    'missing_project_logo'    => env('APP_URL').'/images/placeholders/project.png',
    'missing_expedition_logo' => env('APP_URL').'/images/placeholders/card-image-place-holder02.jpg',
    'missing_avatar_small'    => env('APP_URL').'/images/avatars/small/missing.png',
    'missing_avatar_medium'   => env('APP_URL').'/images/avatars/medium/missing.png',

    'image_process_file' => base_path(env('IMAGE_PROCESS_FILE')),

    'project_chart_series' => resource_path('json/projectChartSeries.json'),
    'project_chart_config' => resource_path('json/projectChartConfig.json'),

    'ocr_disable' => env('OCR_DISABLE', false),

    'poll_ocr_channel'               => env('POLL_OCR_CHANNEL'),
    'poll_export_channel'            => env('POLL_EXPORT_CHANNEL'),
    'poll_board_channel'             => env('POLL_BOARD_CHANNEL'),
    'poll_bingo_channel'             => env('POLL_BINGO_CHANNEL'),
    'poll_wedigbio_progress_channel' => env('POLL_WEDIGBIO_PROGRESS_CHANNEL'),

    'project_resources'   => [
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
    'queue'                 => [
        'num_procs'               => env('QUEUE_NUM_PROCS'),
        'chart'                   => env('QUEUE_CHART'),
        'classification'          => env('QUEUE_CLASSIFICATION'),
        'default'                 => env('QUEUE_DEFAULT'),
        'event'                   => env('QUEUE_EVENT'),
        'export'                  => env('QUEUE_EXPORT'),
        'geolocate'               => env('QUEUE_GEOLOCATE'),
        'import'                  => env('QUEUE_IMPORT'),
        'lambda_ocr'              => env('QUEUE_LAMBDA_OCR'),
        'biospex_event'           => env('QUEUE_BIOSPEX_EVENT'),
        'pusher_classification'   => env('QUEUE_PUSHER_CLASSIFICATION'),
        'pusher_handler'          => env('QUEUE_PUSHER_HANDLER'),
        'wedigbio_event'          => env('QUEUE_WEDIGBIO_EVENT'),
        'pusher_process'          => env('QUEUE_PUSHER_PROCESS'),
        'reconcile'               => env('QUEUE_RECONCILE'),
        'image_export_listener'   => env('QUEUE_IMAGE_EXPORT_LISTENER'),
        'reconciliation_listener' => env('QUEUE_RECONCILIATION_LISTENER'),
        'tesseract_ocr_listener'  => env('QUEUE_TESSERACT_OCR_LISTENER'),
        'workflow'                => env('QUEUE_WORKFLOW'),
    ],

    /* Images */
    /* Min and max logo and banner sizes used in Project model. Max Zoonviverse image. Thumb sizes. */
    'thumb_default_img'     => 'thumbs/default_thumb.png',
    'thumb_output_dir'      => 'thumbs',
    'thumb_width'           => 300,
    'thumb_height'          => 300,
    'logo'                  => '300x200',
    'banner'                => '1200x250',

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
    'ppsr'                  => [
        'ProjectGUID'             => ['private' => 'uuid'],
        'ProjectName'             => ['column' => 'title'],
        'ProjectDataProvider'     => ['value' => env('APP_NAME')],
        'ProjectDescription'      => ['column' => 'description_long'],
        'ProjectDateLastUpdated'  => ['date' => 'updated_at'],
        'ProjectContactName'      => ['column' => 'contact'],
        'ProjectContactEmail'     => ['column' => 'contact_email'],
        'ProjectStatus'           => [],
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
        'API_URL',
        'API_VERSION',
        'API_TOKEN',
        'APP_URL',
        'APP_ENV',
        'APP_DOMAIN',
        'APP_SERVER_USER',
        'APP_CURRENT_PATH',
        'PUSHER_APP_CLUSTER',
        'QUEUE_NUM_PROCS',
        'QUEUE_CHART',
        'QUEUE_CLASSIFICATION',
        'QUEUE_DEFAULT',
        'QUEUE_EVENT',
        'QUEUE_GEOLOCATE',
        'QUEUE_IMPORT',
        'QUEUE_EXPORT',
        'QUEUE_RECONCILE',
        'QUEUE_IMAGE_EXPORT_LISTENER',
        'QUEUE_RECONCILIATION_LISTENER',
        'QUEUE_TESSERACT_OCR_LISTENER',
        'QUEUE_WORKFLOW',
        'QUEUE_PUSHER_PROCESS',
        'QUEUE_LAMBDA_OCR',
        'QUEUE_BIOSPEX_EVENT',
        'QUEUE_PUSHER_CLASSIFICATION',
        'QUEUE_PUSHER_HANDLER',
        'QUEUE_WEDIGBIO_EVENT',
        'REDIS_HOST',
        'ZOONIVERSE_PUSHER_ID',
    ],
];