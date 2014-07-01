<?php

return array(

    'imgTypes' => array(
        'image/jpeg' => '.jpg',
        'image/png' => '.png',
        'image/tiff' => '.tiff',
    ),

    /** Meta.xml file search fields */
    'metaData' => array(
        'multimediaFile' => 'multimedia',
        'occurrenceFile' => 'occurrence',
        'identifier'     => 'identifier',
        'remoteImgUrl'   => 'bestQualityAccessURI'
    ),

    /** Default expedition data directories */
    'dataDir' => storage_path() . '/data',
    'dataTmp' => storage_path() . '/data/tmp',

    /** Default logo and banner sizes used in Project model for Codesleve Stapler */
    'logo' => '100x100',
    'banner' => '468x60',

    /**
     * Default group permissions
     */

    'group_permissions' => array(
        "project_create" => 1,
        "project_edit" => 1,
        "project_view" => 1,
        "project_delete" => 1,
        "group_create" => 1,
        "group_edit" => 1,
        "group_view" => 1,
        "group_delete" => 1,
        "expedition_create" => 1,
        "expedition_edit" => 1,
        "expedition_view" => 1,
        "expedition_delete" => 1
    ),


    /**
     * Allow/Disallow registration
     */
    'registration' => true,
);