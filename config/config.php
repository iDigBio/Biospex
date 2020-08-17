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

];
