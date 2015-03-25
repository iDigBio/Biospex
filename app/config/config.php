<?php

return [

    /** Set Admin email */
    'adminEmail'        => $_ENV['site.adminEmail'],

    /**
     * Allow/Disallow registration
     */
    'registration'      => $_ENV['site.registration'],

    /** Turn on language translation in main menu */
    'translate'         => false,

    /** Default expedition data directories */
    'dataDir'           => storage_path() . '/data',
    'dataTmp'           => storage_path() . '/data/tmp',

    'disableConfig'     => true,

    /*
     * OCR
     */
    'ocrPostUrl'        => $_ENV['site.ocrPostUrl'],
    'ocrGetUrl'         => $_ENV['site.ocrGetUrl'],
    'ocrDeleteUrl'      => $_ENV['site.ocrDeleteUrl'],
    'ocrCrop'           => $_ENV['site.ocrCrop'],

    'disableOcr'        => true,

    /*
     * Beanstalkd queues for myqueue.conf per site.
     */
    'beanstalkd'        => [
        'default'         => $_ENV['beanstalkd.default'],
        'subjectsImport'  => $_ENV['beanstalkd.subjectsImport'],
        'workflowManager' => $_ENV['beanstalkd.workflowManager'],
        'ocr'             => $_ENV['beanstalkd.ocr']
    ],

    /** Imagine settings */
    'images'            => [
        'thumbDefaultImg'    => '/assets/default_image.jpg',
        'thumbOutputDir'     => storage_path() . '/images',
        'thumbWidth'         => 150,
        'thumbHeight'        => 150,
        'library'            => 'imagick',
        'quality'            => 100,
        'imageTypeExtension' => [
            'image/jpeg' => ".jpg",
            'image/png'  => ".png",
            'image/tiff' => ".tif",
            'image/gif'  => ".gif"
        ],
    ],

    /** Min and max logo and banner sizes used in Project model for Codesleve Stapler */
    'logo'              => '300x200',
    'banner'            => '1200x300',

    /**
     * Possible identifiers in subject uploads.
     */
    'identifiers'       => [
        'identifier',
        'providerManagedID',
        'uuid',
        'recordId',
    ],

    /**
     * Visible columns in jqGrid.
     */
    'modelColumns'      => [
        'Assigned',
        'Id',
        'AccessURI',
        'Ocr'
    ],

    /**
     * Columns used in select statement for grid.
     */
    'selectColumns'     => [
        'expedition_ids',
        'id',
        'accessURI',
        'ocr'
    ],

    /**
     * Default advertise fields for PPSR_CORE
     */
    'ppsr'              => [
        'ProjectGUID'             => ['private' => 'uuid'],
        'ProjectName'             => ['column' => 'title'],
        'ProjectDataProvider'     => ['value' => $_ENV['site.name']],
        'ProjectDescription'      => ['column' => 'description_long'],
        'ProjectDateLastUpdated'  => ['private' => 'updated_at'],
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

    'statusSelect'      => [
        'starting' => 'Starting',
        'acting'   => 'Acting',
        'complete' => 'Complete',
        'hiatus'   => 'Hiatus'
    ],

    /**
     * Default group permissions
     */

    'group_permissions' => [
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