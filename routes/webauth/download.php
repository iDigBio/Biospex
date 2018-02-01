<?php

$router->get('projects/{projects}/expeditions/{expeditions}/downloads')->uses('DownloadsController@index')->name('webauth.downloads.index');
$router->get('projects/{projects}/expeditions/{expeditions}/regenerate')->uses('DownloadsController@regenerate')->name('webauth.downloads.regenerate');
$router->get('projects/{projects}/expeditions/{expeditions}/summary')->uses('DownloadsController@summary')->name('webauth.downloads.summary');