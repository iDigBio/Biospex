<?php

$router->get('projects/{projects}/expeditions/{expeditions}/downloads/{downloads}')->uses('DownloadsController@show')->name('projects.expeditions.downloads.get.show');
