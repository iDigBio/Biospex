<?php

$router->get('projects/{projects}/advertises')->uses('AdvertisesController@index')->name('web.advertises.index');
$router->get('projects/{projects}/advertises/show')->uses('AdvertisesController@show')->name('web.advertises.show');