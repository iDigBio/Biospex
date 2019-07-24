<?php

return [

    /**
     * Site variables
     */
    'current_path'    => env('CURRENT_PATH'),
    'server_user'     => env('SERVER_USER'),
    'registration'    => env('REGISTRATION'),
    'expedition_size' => env('EXPEDITION_SIZE'),
    'echo_id'         => env('ECHO_ID'),
    'echo_key'        => env('ECHO_KEY'),
    'echo_ssl_crt'    => env('ECHO_SSL_CRT'),
    'echo_ssl_key'    => env('ECHO_SSL_KEY'),

    'api_url'           => env('API_URL'),
    'api_version'       => env('API_VERSION'),
    'api_client_id'     => env('API_CLIENT_ID'),
    'api_client_secret' => env('API_CLIENT_SECRET'),

    'admin_group'    => env('ADMIN_GROUP', 'Admin'),
    'admin_group_id' => env('ADMIN_GROUP_ID', 1),

    'scratch_dir'                  => 'scratch',
    'export_dir'                   => 'exports',
    'reports_dir'                  => 'reports',
    'import_dir'                   => 'subjects',
    'nfn_downloads_dir'            => 'nfndownloads',
    'nfn_downloads_classification' => 'nfndownloads/classification',
    'nfn_downloads_reconcile'      => 'nfndownloads/reconcile',
    'nfn_downloads_summary'        => 'nfndownloads/summary',
    'nfn_downloads_transcript'     => 'nfndownloads/transcript',

    'missing_project_logo' => env('APP_URL') . '/images/placeholders/project.png',
    'missing_expedition_logo' => env('APP_URL') . '/images/placeholders/card-image-place-holder02.jpg',
    'missing_avatar_small' => env('APP_URL') . '/images/avatars/small/missing.png',
    'missing_avatar_medium' => env('APP_URL') . '/images/avatars/medium/missing.png',

    'python_path'      => env('LABEL_RECONCILIATIONS_PATH').'/venv/bin/python',
    'reconcile_path'   => env('LABEL_RECONCILIATIONS_PATH').'/reconcile.py',

    'project_chart_series' => resource_path('json/projectChartSeries.json'),

    'ocr_disable'    => env('OCR_DISABLE', false),

    'poll_ocr_channel'        => env('POLL_OCR_CHANNEL'),
    'poll_export_channel'     => env('POLL_EXPORT_CHANNEL'),
    'poll_scoreboard_channel' => env('POLL_SCOREBOARD_CHANNEL'),

    'cache_enabled'       => env('CACHE_ENABLED', true),
    'cache_minutes'       => env('CACHE_MINUTES', 60),

    'project_resources' => [
        'Website URL','Video URL','File Download'
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
        'StateProvince'  => 'state_province',
        'State_Province' => 'state_province',
        'State'          => 'state_province',
        'County'         => 'county',
        'subject_county' => 'county',
    ],

    'dwcOccurrenceFields'   => [
        'stateProvince'  => 'state_province',
        'State_Province' => 'state_province',
        'State'          => 'state_province',
        'County'         => 'county',
    ],

    /* Beanstalk Tubes */
    'num_procs'             => env('NUM_PROCS'),
    'chart_tube'            => env('QUEUE_CHART_TUBE'),
    'classification_tube'   => env('QUEUE_CLASSIFICATION_TUBE'),
    'default_tube'          => env('QUEUE_DEFAULT_TUBE'),
    'event_tube'            => env('QUEUE_EVENT_TUBE'),
    'import_tube'           => env('QUEUE_IMPORT_TUBE'),
    'export_tube'           => env('QUEUE_EXPORT_TUBE'),
    'stat_tube'             => env('QUEUE_STAT_TUBE'),
    'workflow_tube'         => env('QUEUE_WORKFLOW_TUBE'),
    'ocr_tube'              => env('QUEUE_OCR_TUBE'),
    'pusher_tube'           => env('QUEUE_PUSHER_TUBE'),

    /* Images */
    /* Min and max logo and banner sizes used in Project model for Codesleve Stapler */
    'thumbDefaultImg'       => 'thumbs/default_thumb.png',
    'thumbOutputDir'        => 'thumbs',
    'thumbWidth'            => 300,
    'thumbHeight'           => 300,
    'logo'                  => '300x200',
    'banner'                => '1200x250',

    /**
     * Visible columns in jqGrid.
     */
    'model_columns'         => [
        'Assigned',
        'Id',
        'AccessURI',
        'Ocr',
    ],

    /**
     * Columns used in select statement for grid.
     */
    'defaultGridVisible'    => [
        'id',
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
    'nfnSkipCsv'                => env('NFN_SKIP_CSV'), // Skip csv creation for expedition ids
    'nfnTranscriptionsComplete' => env('NFN_TRANSCRIPTIONS_COMPLETE', 3),

    'nfnCsvMap' => [
        'subjectId'        => '_id',
        'imageName'        => '_id',
        'imageURL'         => 'accessURI',
        'references'       => ['occurrence' => 'references'],
        'scientificName'   => ['occurrence' => 'scientificName'],
        'country'          => ['occurrence' => 'country'],
        'stateProvince'    => ['occurrence' => 'stateProvince'],
        'county'           => ['occurrence' => 'county'],
        'eol'              => ['occurrence' => 'scientificName'],
        'mol'              => ['occurrence' => 'scientificName'],
        'idigbio'          => ['occurrence' => 'scientificName'],
        '#institutionCode' => ['occurrence' => 'institutionCode'],
        '#collectionCode'  => ['occurrence' => 'collectionCode'],
        '#catalogNumber'   => ['occurrence' => 'catalogNumber'],
        '#recordId'        => ['occurrence' => 'recordId'],
        '#expeditionId'    => '',
        '#expeditionTitle' => '',
    ],

    'nfnApi' => [
        'clientId'     => env('NFN_API_CLIENT_ID'),
        'clientSecret' => env('NFN_API_CLIENT_SECRET'),
        'apiUri'       => env('NFN_API_URI'),
        'tokenUri'     => env('NFN_API_TOKEN_URI'),
        'redirectUri'  => env('NFN_REDIRECT_URI'),
    ],

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
];
