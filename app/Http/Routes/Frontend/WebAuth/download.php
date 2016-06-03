<?php

$router->get('projects/{projects}/expeditions/{expeditions}/downloads', [
    'uses' => 'DownloadsController@index',
    'as'   => 'projects.expeditions.downloads.get.index'
]);