<?php

return [

    'app_domain' => env('APP_DOMAIN'),
    'app_current_path' => env('APP_CURRENT_PATH'),
    'app_server_user' => env('APP_SERVER_USER'),
    'app_registration' => env('APP_REGISTRATION'),
    'supervisor_group' => env('SUPERVISOR_GROUP'), // For supervisor: biospex, dev-biospex

    'db_log' => env('DB_LOG', false),

    'expedition_size' => env('EXPEDITION_SIZE'),

    'reverb_debug' => env('REVERB_DEBUG', false),

    'api' => [
        'domain' => env('API_DOMAIN'),
        'url' => env('API_URL'),
        'version' => env('API_VERSION'),
        'token' => env('API_TOKEN'),
    ],

    'admin' => [
        'group' => env('ADMIN_GROUP', 'Admin'),
        'group_id' => env('ADMIN_GROUP_ID', 1),
        'user_id' => env('ADMIN_USER_ID', 1),
    ],

    'aws' => [
        'access_key' => env('AWS_ACCESS_KEY'),
        'secret_key' => env('AWS_SECRET_ACCESS_KEY'),
        'default_region' => env('AWS_DEFAULT_REGION'),
        'lambda_export_function' => env('AWS_LAMBDA_EXPORT_FUNCTION'),
        'lambda_reconciliation_function' => env('AWS_LAMBDA_RECONCILIATION_FUNCTION'),
        'lambda_ocr_function' => env('AWS_LAMBDA_OCR_FUNCTION'),
        'lambda_export_count' => env('AWS_LAMBDA_EXPORT_COUNT'),
        'lambda_qualifier' => env('AWS_LAMBDA_QUALIFIER'),
        'lambda_ocr_count' => env('AWS_LAMBDA_OCR_COUNT'),
    ],

    'batch_dir' => env('BATCH_DIR', 'batch'),
    'export_dir' => env('EXPORT_DIR', 'export'),
    'import_dir' => env('IMPORT_DIR', 'import'),
    'report_dir' => env('REPORT_DIR', 'report'),
    'scratch_dir' => env('SCRATCH_DIR', 'scratch'),

    'missing_project_logo' => env('APP_URL').'/images/placeholders/project.png',
    'missing_expedition_logo' => env('APP_URL').'/images/placeholders/card-image-place-holder02.jpg',
    'missing_avatar_small' => env('APP_URL').'/images/avatars/small/missing.png',
    'missing_avatar_medium' => env('APP_URL').'/images/avatars/medium/missing.png',

    'uploads' => [
        'project_logos' => env('UPLOAD_PROJECT_LOGOS', 'uploads/projects/logos'),
        'expedition_logos' => env('UPLOAD_EXPEDITION_LOGOS', 'uploads/expeditions/logos'),
        'expedition_logos_medium' => env('UPLOAD_EXPEDITION_LOGOS_MEDIUM', 'uploads/expeditions/logos/medium'),
        'expedition_logos_original' => env('UPLOAD_EXPEDITION_LOGOS_ORIGINAL', 'uploads/expeditions/logos/original'),
        'profile_avatars' => env('UPLOAD_PROFILE_AVATARS', 'uploads/profiles/avatars'),
        'profile_avatars_small' => env('UPLOAD_PROFILE_AVATARS_SMALL', 'uploads/profiles/avatars/small'),
        'profile_avatars_medium' => env('UPLOAD_PROFILE_AVATARS_MEDIUM', 'uploads/profiles/avatars/medium'),
        'profile_avatars_original' => env('UPLOAD_PROFILE_AVATARS_ORIGINAL', 'uploads/profiles/avatars/original'),
        'resources' => env('UPLOAD_RESOURCES', 'uploads/resources'),
        'project_resources_base' => env('UPLOAD_PROJECT_RESOURCES_BASE', 'uploads/project-resources'),
        'project_resources_downloads' => env('UPLOAD_PROJECT_RESOURCES_DOWNLOADS', 'uploads/project-resources/downloads'),
        'project-assets' => env('UPLOAD_PROJECT_ASSETS', 'uploads/project-assets'),
        'site-assets' => env('UPLOAD_SITE_ASSETS', 'uploads/site-assets'),
    ],

    'project_chart_series' => resource_path('json/projectChartSeries.json'),
    'project_chart_config' => resource_path('json/projectChartConfig.json'),

    // Whether OCR is enabled for overnight scripts.
    'ocr_enabled' => env('OCR_ENABLED', true),

    'poll_ocr_channel' => env('POLL_OCR_CHANNEL'),
    'poll_export_channel' => env('POLL_EXPORT_CHANNEL'),
    'poll_scoreboard_channel' => env('POLL_SCOREBOARD_CHANNEL'),
    'poll_bingo_channel' => env('POLL_BINGO_CHANNEL'),
    'poll_wedigbio_progress_channel' => env('POLL_WEDIGBIO_PROGRESS_CHANNEL'),

    'project_assets' => [
        'Website URL',
        'Video URL',
        'File Download',
    ],

    /**
     * iDigBio api query url
     */
    'recordset_url' => 'https://beta-api.idigbio.org/v2/download/?rq={"recordset":"RECORDSET_ID"}',

    /**
     * DCA import row types for multimedia.
     */
    'dwcRequiredRowTypes' => [
        'http://rs.tdwg.org/ac/terms/multimedia',
        'http://rs.gbif.org/terms/1.0/image',
        'http://rs.tdwg.org/dwc/terms/occurrence',
    ],

    'dwcRequiredFields' => [
        'core' => ['id'],
        'extension' => [
            'coreid' => [],
            'accessURI' => ['http://rs.tdwg.org/ac/terms/accessURI'],
            'identifier' => [
                'http://purl.org/dc/terms/identifier',
                'http://rs.tdwg.org/ac/terms/providerManagedID',
                'http://portal.idigbio.org/terms/uuid',
                'http://portal.idigbio.org/terms/recordId',
            ],
        ],
    ],

    /**
     * Darwin Core import thresholds for memory management
     */
    'dwc_import_thresholds' => [
        'file_size_mb' => env('DWC_FILE_SIZE_THRESHOLD_MB', 30),
        'row_count' => env('DWC_ROW_COUNT_THRESHOLD', 25000),
    ],

    'dwcTranscriptFields' => [
        'stateProvince' => 'state_province',
        'StateProvince' => 'state_province',
        'State/Province' => 'state_province',
        'State Province' => 'state_province',
        'State_Province' => 'state_province',
        'State' => 'state_province',
        'County' => 'county',
        'subject_county' => 'county',
    ],

    'dwcOccurrenceFields' => [
        'stateProvince' => 'state_province',
        'State_Province' => 'state_province',
        'State Province' => 'state_province',
        'State/Province' => 'state_province',
        'State' => 'state_province',
        'County' => 'county',
    ],

    'darwin_core' => [
        'use_batch_processing' => env('DWC_USE_BATCH_PROCESSING', false),
    ],

    /* Beanstalk Queues */
    'queue' => [
        'custom_procs' => env('QUEUE_CUSTOM_PROCS'),
        'num_procs' => env('QUEUE_NUM_PROCS'),
        'chart' => env('QUEUE_CHART'),
        'classification' => env('QUEUE_CLASSIFICATION'),
        'default' => env('QUEUE_DEFAULT'),
        'event' => env('QUEUE_EVENT'),
        'export' => env('QUEUE_EXPORT'),
        'geolocate' => env('QUEUE_GEOLOCATE'),
        'import' => env('QUEUE_IMPORT'),
        'ocr' => env('QUEUE_OCR'),
        'lambda_ocr' => env('QUEUE_LAMBDA_OCR'),
        'biospex_event' => env('QUEUE_BIOSPEX_EVENT'),
        'pusher_classification' => env('QUEUE_PUSHER_CLASSIFICATION'),
        'pusher_handler' => env('QUEUE_PUSHER_HANDLER'),
        'wedigbio_event' => env('QUEUE_WEDIGBIO_EVENT'),
        'pusher_process' => env('QUEUE_PUSHER_PROCESS'),
        'reconcile' => env('QUEUE_RECONCILE'),
        'sns_image_export' => env('QUEUE_SNS_IMAGE_EXPORT'),
        'sns_reconciliation' => env('QUEUE_SNS_RECONCILIATION'),
        'sns_tesseract_ocr' => env('QUEUE_SNS_TESSERACT_OCR'),
        'workflow' => env('QUEUE_WORKFLOW'),
        'sernec_file' => env('QUEUE_SERNEC_FILE'),
        'sernec_row' => env('QUEUE_SERNEC_ROW'),
    ],

    /* Images */
    /* Min and max logo and banner sizes used in Project model. Max Zoonviverse image. Thumb sizes. */
    'thumb_default_img' => 'thumbs/default_thumb.png',
    'thumb_output_dir' => 'thumbs',
    'thumb_width' => 300,
    'thumb_height' => 300,
    'logo' => '300x200',
    'banner' => '1200x250',

    /**
     * Columns used in select statement for grid.
     */
    'defaultGridVisible' => [
        'imageId',
        'exported',
        'accessURI',
        'ocr',
    ],
    'defaultSubGridVisible' => [
        'imageId',
        'institutionCode',
        'scientificName',
        'recordId',
    ],

    /**
     * Default advertise fields for PPSR_CORE
     */
    'ppsr' => [
        'ProjectGUID' => ['private' => 'uuid'],
        'ProjectName' => ['column' => 'title'],
        'ProjectDataProvider' => ['value' => config('app.name')],
        'ProjectDescription' => ['column' => 'description_long'],
        'ProjectDateLastUpdated' => ['date' => 'updated_at'],
        'ProjectContactName' => ['column' => 'contact'],
        'ProjectContactEmail' => ['column' => 'contact_email'],
        'ProjectStatus' => [],
        'ProjectOrganization' => ['column' => 'organization'],
        'ProjectVolunteerSupport' => ['column' => 'incentives'],
        'ProjectURL' => ['url' => 'slug'],
        'ProjectFacebook' => ['column' => 'facebook'],
        'ProjectTwitter' => ['column' => 'twitter'],
        'ProjectKeywords' => ['array' => ['keywords', 'geographic_scope', 'temporal_scope']],
        'fieldOfScience' => [],
        'participationType' => [],
        'participantEducation' => ['column' => 'language_skills'],
        'fundingSource' => ['column' => 'funding_source'],
        'projectBlog' => ['column' => 'blog_url'],
        'projectImage' => ['url' => 'logo'],
    ],

    'wedigbio_start_date' => env('WEDIGBIO_START_DATE'),
    'wedigbio_end_date' => env('WEDIGBIO_END_DATE'),

    'deployment_fields' => [
        'APP_ENV',
        'APP_DOMAIN',
        'APP_SERVER_USER',
        'APP_CURRENT_PATH',
        'QUEUE_CUSTOM_PROCS',
        'QUEUE_NUM_PROCS',
        'QUEUE_CHART',
        'QUEUE_CLASSIFICATION',
        'QUEUE_DEFAULT',
        'QUEUE_EVENT',
        'QUEUE_GEOLOCATE',
        'QUEUE_IMPORT',
        'QUEUE_OCR',
        'QUEUE_EXPORT',
        'QUEUE_RECONCILE',
        'QUEUE_SNS_IMAGE_EXPORT',
        'QUEUE_SNS_RECONCILIATION',
        'QUEUE_SNS_TESSERACT_OCR',
        'QUEUE_WORKFLOW',
        'QUEUE_PUSHER_PROCESS',
        'QUEUE_LAMBDA_OCR',
        'QUEUE_BIOSPEX_EVENT',
        'QUEUE_PUSHER_CLASSIFICATION',
        'QUEUE_PUSHER_HANDLER',
        'QUEUE_WEDIGBIO_EVENT',
        'QUEUE_SERNEC_FILE',
        'QUEUE_SERNEC_ROW',
        'REVERB_DEBUG',
        'SUPERVISOR_GROUP',
    ],
];
