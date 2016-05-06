<?php

return [

    /**
     * Site variables
     */
    'app_url' => env('APP_URL'),
    'api_url' => env('API_URL'),
    'app_domain' => env('APP_DOMAIN'),
    'app_nodejs_domain' => env('APP_NODEJS_DOMAIN'),
    'app_ip' => env('APP_IP'),
    'registration' => env('APP_REGISTRATION'),
    'translate' => env('APP_TRANSLATE'),

    'scratch_dir' => storage_path('scratch'),
    'nfn_export_dir' => storage_path('exports/nfn'),
    'subject_import_dir' => storage_path('imports/subjects'),
    'transcription_import_dir' => storage_path('imports/transcriptions'),
    'export_reports_dir' => storage_path('exports/report'),

    'ocr_post_url' => env('OCR_POSTURL'),
    'ocr_get_url' => env('OCR_GETURL'),
    'ocr_delete_url' => env('OCR_DELETEURL'),
    'ocr_crop' => env('OCR_CROP'),
    'ocr_disable' => env('OCR_DISABLE', false),
    'ocr_chunk' => env('OCR_CHUNK'),
    'ocr_api_key' => env('OCR_API_KEY'),
    'ocr_poll_channel' => env('OCR_POLL_CHANNEL'),

    /**
     * iDigBio api query url
     */
    'recordset_url' => 'https://beta-api.idigbio.org/v2/download/?rq={"recordset":"RECORDSET_ID"}',

    /**
     * Match used in Notes From Nature transcription import for matching.
     */
    'collection' => env('APP_nfncollection'),

    /**
     * DCA import row types for multimedia.
     */
    'metaFileRowTypes' => [
        'http://rs.tdwg.org/ac/terms/multimedia' => ['multimedia_raw', 'multimedia', 'multimedia-10'],
        'http://rs.gbif.org/terms/1.0/image' => ['images'],
        'http://rs.tdwg.org/dwc/terms/occurrence' => ['occurrence_raw, occurrence', 'occurrence-10']
    ],

    /* Added Tubes */
    'beanstalkd' => [
        'default' => env('QUEUE_DEFAULT_TUBE'),
        'import' => env('QUEUE_IMPORT_TUBE'),
        'workflow' => env('QUEUE_WORKFLOW_TUBE'),
        'ocr' => env('QUEUE_OCR_TUBE'),
        'event' => env('QUEUE_EVENT_TUBE')
    ],

    'images' => [
        'thumbDefaultImg' => public_path('/img/default_image.jpg'),
        'thumbOutputDir' => storage_path('images'),
        'thumbWidth' => 300,
        'thumbHeight' => 300,
        'imageTypeExtension' => [
            'image/jpeg' => "jpg",
            'image/png' => "png",
            'image/tiff' => "tif",
            'image/gif' => "gif"
        ],
    ],

    /** Min and max logo and banner sizes used in Project model for Codesleve Stapler */
    'logo' => '300x200',
    'banner' => '1200x300',

    /**
     * Possible identifiers in subject uploads.
     */
    'identifiers' => [
        'identifier',
        'providerManagedID',
        'uuid',
        'recordId',
    ],

    /**
     * Visible columns in jqGrid.
     */
    'model_columns' => [
        'Assigned',
        'Id',
        'AccessURI',
        'Ocr'
    ],

    /**
     * Columns used in select statement for grid.
     */
    'select_columns' => [
        'expedition_ids',
        'id',
        'accessURI',
        'ocr'
    ],
    
    'nfnImageSize' => [
        'largeWidth' => env('NFN_LRG_WIDTH'),
        'smallWidth' => env('NFN_SM_WIDTH'),
    ],

    'nfnCsvMap' => [
        'subjectId' => '_id',
        'imageName' => '',
        'imageURL' => 'accessURI',
        'references' => ['occurrence' => 'references'],
        '#institutionCode' => ['occurrence' => 'institutionCode'],
        '#collectionCode' => ['occurrence' => 'collectionCode'],
        '#catalogNumber' => ['occurrence' => 'catalogNumber'],
        '#expeditionId' => '',
    ],

    /**
     * Default advertise fields for PPSR_CORE
     */
    'ppsr' => [
        'ProjectGUID' => ['private' => 'uuid'],
        'ProjectName' => ['column' => 'title'],
        'ProjectDataProvider' => ['value' => env('APP_NAME')],
        'ProjectDescription' => ['column' => 'description_long'],
        'ProjectDateLastUpdated' => ['date' => 'updated_at'],
        'ProjectContactName' => ['column' => 'contact'],
        'ProjectContactEmail' => ['column' => 'contact_email'],
        'ProjectStatus' => ['column' => 'status'],
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

    'status_select' => [
        'starting' => 'Starting',
        'active' => 'Active',
        'complete' => 'Complete',
        'hiatus' => 'Hiatus'
    ],
];
