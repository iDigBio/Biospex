<?php

return [

    /**
     * Site variables
     */
    'current_path' => env('CURRENT_PATH'),
    'server_user'  => env('SERVER_USER'),
    'registration' => env('REGISTRATION'),

    'cache_enabled'         => env('CACHE_ENABLED', true),
    'cache_minutes'         => env('CACHE_MINUTES', 60),

    /* Beanstalk Tubes */
    'num_procs'             => env('NUM_PROCS'),
    'default_tube'          => env('QUEUE_DEFAULT_TUBE'),

    /**
     * Directories and files
     */
    'reports_dir'           => 'reports',
    'rapid_import_dir'      => 'imports/rapid',
    'rapid_export_dir'      => 'exports/rapid',
    'header_import'         => 'imports/rapid/header.json',
    'geolocate_fields_file' => resource_path('files/rapid-exports/geolocate-fields.json'),
    'people_fields_file'    => resource_path('files/rapid-exports/people-fields.json'),
    'taxonomic_fields_file' => resource_path('files/rapid-exports/taxonomic-fields.json'),
    'geolocate_view_file'   => resource_path('files/rapid-views/geolocate.json'),

    /**
     * Columns used in select statement for grid.
     */
    'default_grid_visible'  => [
        '_id',
        'country_rapid',
        'locality_gbifR',
        'locality_idbR',
        'recordedBy_gbifR',
        'recordedBy_idbR',
    ],

    /**
     * Fields used for validation.
     */
    'validation_fields'     => [
        'gbifID_gbifR',
        'idigbio_uuid_idbP',
    ],

    /**
     * Update column field tags.
     */
    'column_tags'           => [
        '_gbifR',
        '_gbifP',
        '_idbP',
        '_idbR',
        '_rapid',
    ],

    /**
     * Protected fields.
     */
    'protected_fields'      => [
        '_id',
        'updated_at',
        'created_at',
    ],

    /**
     * Columns reserved for _id field.
     */
    'reserved_columns'      => [
        'geolocate' => ['CatalogNumber' => '_id'],
        'people'    => ['BIOSPEXid' => '_id'],
    ],

    /**
     * Export types and extensions.
     */
    'export_extensions'     => [
        'csv' => '.csv',
    ],
];
