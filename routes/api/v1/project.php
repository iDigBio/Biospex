<?php

$router->get('projects', ['as' => 'projects.get.index', 'uses' => 'ProjectController@index']);
$router->get('projects/{guid}', ['as' => 'projects.get.show', 'uses' => 'ProjectController@show']);