<?php

return [
    'enabled' => env('GEOLOCATE_ENABLED', true),
    'actor_id' => env('GEOLOCATE_ACTOR_ID'),
    'workflow_id' => env('GEOLOCATE_WORKFLOW_ID'),
    'fields_file' => resource_path('json/geolocate-fields.json'),
    'api' => [
        'geolocate_uname' => env('GEOLOCATE_UNAME'),
        'geolocate_token' => env('GEOLOCATE_TOKEN'),
        'geolocate_stats_uri' => env('GEOLOCATE_STATS_URI'),
        'geolocate_download_uri' => env('GEOLOCATE_DOWNLOAD_URI'),
    ],
    'dir' => [
        'parent' => env('GEOLOCATE_DIR', 'geolocate'),
        'csv' => env('GEOLOCATE_DIR', 'geolocate').'/csv',
        'export' => env('GEOLOCATE_DIR', 'geolocate').'/export',
        'geo-reconciled' => env('GEOLOCATE_DIR', 'geolocate').'/geo-reconciled',
        'kml' => env('GEOLOCATE_DIR', 'geolocate').'/kml',
    ],
];
