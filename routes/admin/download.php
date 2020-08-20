<?php
$router->get('projects/{projects}/expeditions/{expeditions}/downloads')->uses('DownloadsController@index')->name('admin.downloads.index');
$router->get('projects/{projects}/expeditions/{expeditions}/downloads/{downloads}')->uses('DownloadsController@download')->name('admin.downloads.download');
$router->get('projects/{projects}/expeditions/{expeditions}/regenerate')->uses('DownloadsController@regenerate')->name('admin.downloads.regenerate');
$router->get('projects/{projects}/expeditions/{expeditions}/downloads/{downloads}/batch')->uses('DownloadsController@batch')->name('admin.downloads.batch');
$router->get('projects/{projects}/expeditions/{expeditions}/batch/{files}')->uses('DownloadsController@batchDownload')->name('admin.downloads.batchDownload');
$router->get('projects/{projects}/expeditions/{expeditions}/summary')->uses('DownloadsController@summary')->name('admin.downloads.summary');
$router->get('reports/{file}')->uses('DownloadsController@report')->name('admin.downloads.report');