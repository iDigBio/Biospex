<?php

$router->get('projects/{projects}/expeditions/{expeditions}/downloads')->uses('DownloadsController@index')->name('admin.downloads.index');
$router->get('projects/{projects}/expeditions/{expeditions}/regenerate')->uses('DownloadsController@regenerate')->name('admin.downloads.regenerate');
$router->get('projects/{projects}/expeditions/{expeditions}/summary')->uses('DownloadsController@summary')->name('admin.downloads.summary');