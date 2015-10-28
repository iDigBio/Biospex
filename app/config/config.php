<?php

return [

    /**
     * Site variables
     */
    'site.domain'            => $_ENV['site.domain'],
    'adminEmail'             => $_ENV['site.adminEmail'],
    'registration'           => $_ENV['site.registration'],
    'translate'              => false,
    'scratchDir'             => storage_path('scratch'),
    'nfnExportDir'           => storage_path('exports/nfn'),
    'exportReportsDir'       => storage_path('exports/report'),
    'subjectImportDir'       => storage_path('imports/subjects'),
    'transcriptionImportDir' => storage_path('imports/transcriptions'),
    /**
     * API settings
     */
    'api'                    => [
        'domain' => $_ENV['api.domain'],
    ],
    /**
     * iDigBio api query url
     */
    'recordsetUrl'           => 'https://beta-api.idigbio.org/v2/download/?rq={"recordset":"RECORDSET_ID"}',
    /**
     * Match used in Notes From Nature transcription import for matching.
     */
    'collection'             => $_ENV['nfn.collection'],
    /**
     * DCA import row types for multimedia.
     */
    'metaFileRowTypes'       => [
        'http://rs.tdwg.org/ac/terms/multimedia'  => 'multimedia_raw',
        'http://rs.gbif.org/terms/1.0/image'      => 'images',
        'http://rs.tdwg.org/dwc/terms/occurrence' => 'occurrence',
        'http://biospex.loc/media'                => 'multimedia-10',
        'http://biospex.loc/occurrence'           => 'occurrence-10',
    ],
    /*
     * OCR
     */
    'ocrPostUrl'             => $_ENV['site.ocrPostUrl'],
    'ocrGetUrl'              => $_ENV['site.ocrGetUrl'],
    'ocrDeleteUrl'           => $_ENV['site.ocrDeleteUrl'],
    'ocrCrop'                => $_ENV['site.ocrCrop'],
    'disableOcr'             => $_ENV['site.disableOcr'],
    /**
     * Beanstalkd queues for myqueue.conf per site.
     */
    'beanstalkd'             => [
        'default'  => $_ENV['beanstalkd.default'],
        'import'   => $_ENV['beanstalkd.import'],
        'workflow' => $_ENV['beanstalkd.workflow'],
        'ocr'      => $_ENV['beanstalkd.ocr']
    ],
    /** Imagine settings */
    'images'                 => [
        'thumbDefaultImg'    => '/assets/default_image.jpg',
        'thumbOutputDir'     => storage_path('images'),
        'thumbWidth'         => 150,
        'thumbHeight'        => 150,
        'library'            => 'gmagick',
        'quality'            => 100,
        'imageTypeExtension' => [
            'image/jpeg' => "jpg",
            'image/png'  => "png",
            'image/tiff' => "tif",
            'image/gif'  => "gif"
        ],
    ],
    /** Min and max logo and banner sizes used in Project model for Codesleve Stapler */
    'logo'                   => '300x200',
    'banner'                 => '1200x300',
    /**
     * Possible identifiers in subject uploads.
     */
    'identifiers'            => [
        'identifier',
        'providerManagedID',
        'uuid',
        'recordId',
    ],
    /**
     * Visible columns in jqGrid.
     */
    'modelColumns'           => [
        'Assigned',
        'id',
        'accessURI',
        'ocr'
    ],
    /**
     * Columns used in select statement for grid.
     */
    'selectColumns'          => [
        'expedition_ids',
        'id',
        'accessURI',
        'ocr'
    ],
    /**
     * Default advertise fields for PPSR_CORE
     */
    'ppsr'                   => [
        'ProjectGUID'             => ['private' => 'uuid'],
        'ProjectName'             => ['column' => 'title'],
        'ProjectDataProvider'     => ['value' => $_ENV['site.name']],
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
    'statusSelect'           => [
        'starting' => 'Starting',
        'acting'   => 'Acting',
        'complete' => 'Complete',
        'hiatus'   => 'Hiatus'
    ],
    /**
     * Default group permissions
     */

    'group_permissions'      => [
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
