<?php

$router->get('expeditions', ['as' => 'expeditions.get.index', 'uses' => 'ExpeditionController@index']);
$router->get('expeditions/{guid}', ['as' => 'expeditions.get.show', 'uses' => 'ExpeditionController@show']);

