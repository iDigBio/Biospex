<?php

$router->get('projects/{projects}/advertises')->uses('AdvertisesController@index')->name('admin.advertises.index');
$router->get('projects/{projects}/advertises/download')->uses('AdvertisesController@download')->name('admin.advertises.download');