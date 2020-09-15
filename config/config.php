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
    'rapid_import_dir' => 'imports/rapid',

    /**
     * Columns used in select statement for grid.
     */
    'defaultGridVisible'    => [
        '_id',
        'gbif',
        'idigbio',
        'gbifID_gbifR',
        'idigbio_uuid_idbP',
    ],

    /**
     * Fields used for validation.
     */
    'validationFields' => [
        'gbifID_gbifR',
        'idigbio_uuid_idbP'
    ],

    /**
     * Update column field tags.
     */
    'updateColumnTags' => [
        '_gbifR',
        '_gbifP',
        '_idbP',
        '_idbR',
        '_rapid'
    ],

    /**
     * Protected fields.
     */
    'protectedFields' => [
        '_id', 'updated_at', 'created_at'
    ],

    /**
     * Export fields
     */
    'geoLocateFields' => 'exports/geolocate-fields.json',

    /**
     * Forms
     */
    'geolocateFrmsDir' => 'exports/geolocate-frms',
];
