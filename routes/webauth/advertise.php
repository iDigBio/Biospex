<?php

$router->get('projects/{projects}/advertises')->uses('AdvertisesController@index')->name('webauth.advertises.index');
$router->get('projects/{projects}/advertises/show')->uses('AdvertisesController@show')->name('webauth.advertises.show');