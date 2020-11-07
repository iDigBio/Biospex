<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
$router->get('projects/{projects}/expeditions/{expeditions}/downloads')->uses('DownloadsController@index')->name('admin.downloads.index');
$router->get('reports/{file}')->uses('DownloadsController@report')->name('admin.downloads.report');
$router->get('projects/{projects}/expeditions/{expeditions}/downloads/{downloads}')->uses('DownloadsController@download')->name('admin.downloads.download');
$router->get('projects/{projects}/expeditions/{expeditions}/downloads/{downloads}/tar')->uses('DownloadsController@downloadTar')->name('admin.downloads.downloadTar');
$router->get('projects/{projects}/expeditions/{expeditions}/downloads/{files}/tar-batch')->uses('DownloadsController@downloadTarBatch')->name('admin.downloads.downloadTarBatch');
$router->get('projects/{projects}/expeditions/{expeditions}/regenerate')->uses('DownloadsController@regenerate')->name('admin.downloads.regenerate');
$router->get('projects/{projects}/expeditions/{expeditions}/downloads/{downloads}/batch')->uses('DownloadsController@batch')->name('admin.downloads.batch');
$router->get('projects/{projects}/expeditions/{expeditions}/summary')->uses('DownloadsController@summary')->name('admin.downloads.summary');