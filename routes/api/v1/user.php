<?php

$router->get('users', ['as' => 'users.get.index', 'uses' => 'UserController@index']);
$router->get('users/{guid}', ['as' => 'projects.get.show', 'uses' => 'UserController@show']);