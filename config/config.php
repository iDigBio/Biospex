<?php

return [

    /**
     * Site variables
     */
    'current_path'    => env('CURRENT_PATH'),
    'server_user'     => env('SERVER_USER'),
    'registration'    => env('REGISTRATION'),

    'cache_enabled' => env('CACHE_ENABLED', true),
    'cache_minutes' => env('CACHE_MINUTES', 60),

    /* Beanstalk Tubes */
    'num_procs'             => env('NUM_PROCS'),
    'default_tube'          => env('QUEUE_DEFAULT_TUBE'),

    /**
     * Directories
     */
    'reports_dir' => 'reports',
    'rapid_import_dir' => 'imports/rapid',
    'rapid_export_dir' => 'exports/rapid',
    'geolocate_fields_file' => 'exports/rapid/geolocate-fields.json',

    /**
     * Columns used in select statement for grid.
     */
    'default_grid_visible'    => [
        '_id',
        'gbif',
        'idigbio',
        'gbifID_gbifR',
        'idigbio_uuid_idbP',
    ],

    /**
     * Fields used for validation.
     */
    'validation_fields' => [
        'gbifID_gbifR',
        'idigbio_uuid_idbP'
    ],

    /**
     * Update column field tags.
     */
    'column_tags' => [
        '_gbifR',
        '_gbifP',
        '_idbP',
        '_idbR',
        '_rapid'
    ],

    /**
     * Protected fields.
     */
    'protected_fields' => [
        '_id', 'updated_at', 'created_at'
    ],
];
