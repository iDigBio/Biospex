<?php

$router->get('projects/{projects}/advertises', [
    'uses' => 'AdvertisesController@index',
    'as'   => 'web.advertises.index'
]);

$router->get('projects/{projects}/advertises/show', [
    'uses' => 'AdvertisesController@show',
    'as'   => 'web.advertises.show'
]);
