<?php

$router->get('projects/{projects}/expeditions/{expeditions}/downloads')->uses('DownloadsController@index')->name('web.downloads.index');
$router->get('projects/{projects}/expeditions/{expeditions}/regenerate')->uses('DownloadsController@regenerate')->name('web.downloads.regenerate');
$router->get('projects/{projects}/expeditions/{expeditions}/summary')->uses('DownloadsController@summary')->name('web.downloads.summary');