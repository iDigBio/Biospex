<?php

$router->get('projects/{projects}/expeditions/{expeditions}/downloads', [
    'uses' => 'DownloadsController@index',
    'as'   => 'web.downloads.index'
]);