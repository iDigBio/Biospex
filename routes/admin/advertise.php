<?php

$router->get('projects/{projects}/advertises')->uses('AdvertisesController@index')->name('admin.advertises.index');
$router->get('projects/{projects}/advertises/show')->uses('AdvertisesController@show')->name('admin.advertises.show');