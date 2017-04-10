<?php

$router->get('projects/{projects}/expeditions/{expeditions}/downloads', [
    'uses' => 'DownloadsController@index',
    'as'   => 'web.downloads.index'
]);

$router->get('projects/{projects}/expeditions/{expeditions}/regenerate', [
    'uses' => 'DownloadsController@regenerate',
    'as'   => 'web.downloads.regenerate'
]);