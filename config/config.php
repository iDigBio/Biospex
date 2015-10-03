<?php

return [

    /**
     * Site variables
     */
    'registration'             => env('APP_REGISTRATION'),
    'translate'                => env('APP_TRANSLATE'),

    'scratch_dir'              => storage_path('scratch'),
    'nfn_export_dir'           => storage_path('exports/nfn'),
    'subject_import_dir'       => storage_path('imports/subjects'),
    'transcription_import_dir' => storage_path('imports/transcriptions'),
    'export_reports_dir'       => storage_path('exports/report'),

    'ocr_post_url'             => env('OCR_POSTURL'),
    'ocr_get_url'              => env('OCR_GETURL'),
    'ocr_delete_url'           => env('OCR_DELETEURL'),
    'ocr_crop'                 => env('OCR_CROP'),
    'disable_ocr'              => env('OCR_DISABLE', false),

    /**
     * iDigBio api query url
     */
    'recordset_url'            => 'https://beta-api.idigbio.org/v2/download/?rq={"recordset":"RECORDSET_ID"}',

    /**
     * Match used in Notes From Nature transcription import for matching.
     */
    'collection'               => env('APP_nfncollection'),

    /**
     * DCA import row types for multimedia.
     */
    'metaFileRowTypes' => [
        'http://rs.tdwg.org/ac/terms/multimedia' => 'multimedia_raw',
        'http://rs.gbif.org/terms/1.0/image' => 'images',
        'http://rs.tdwg.org/dwc/terms/occurrence' => 'occurrence'
    ],

    /* Added Tubes */
    'beanstalkd'               => [
        'default'  => env('QUEUE_DEFAULT_TUBE'),
        'import'   => env('QUEUE_SUBJECTS_TUBE'),
        'workflow' => env('QUEUE_WORKFLOW_TUBE'),
        'ocr'      => env('QUEUE_OCR_TUBE')
    ],

    'images'                   => [
        'thumb_default_img'    => 'images/default_image.jpg',
        'thumb_output_dir'     => storage_path('images'),
        'thumb_width'          => 150,
        'thumb_height'         => 150,
        'library'              => 'gmagick',
        'quality'              => 100,
        'image_type_extension' => [
            'image/jpeg' => "jpg",
            'image/png'  => "png",
            'image/tiff' => "tif",
            'image/gif'  => "gif"
        ],
    ],

    /** Min and max logo and banner sizes used in Project model for Codesleve Stapler */
    'logo'                     => '300x200',
    'banner'                   => '1200x300',

    /**
     * Possible identifiers in subject uploads.
     */
    'identifiers'              => [
        'identifier',
        'providerManagedID',
        'uuid',
        'recordId',
    ],

    /**
     * Visible columns in jqGrid.
     */
    'model_columns'            => [
        'Assigned',
        'Id',
        'AccessURI',
        'Ocr'
    ],

    /**
     * Columns used in select statement for grid.
     */
    'select_columns'           => [
        'expedition_ids',
        'id',
        'accessURI',
        'ocr'
    ],

    /**
     * Default advertise fields for PPSR_CORE
     */
    'ppsr'                     => [
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

    'status_select'            => [
        'starting' => 'Starting',
        'acting'   => 'Acting',
        'complete' => 'Complete',
        'hiatus'   => 'Hiatus'
    ],

    /**
     * Default group permissions
     */
    'group_permissions'        => [
        "project_create"    => 1,
        "project_edit"      => 1,
        "project_view"      => 1,
        "project_delete"    => 1,
        "group_create"      => 1,
        "group_edit"        => 1,
        "group_view"        => 1,
        "group_delete"      => 1,
        "expedition_create" => 1,
        "expedition_edit"   => 1,
        "expedition_view"   => 1,
        "expedition_delete" => 1
    ],
];
