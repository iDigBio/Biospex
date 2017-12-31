<?php

$router->get('groups', ['as' => 'groups.get.index', 'uses' => 'GroupController@index']);
$router->get('groups/{guid}', ['as' => 'projects.get.show', 'uses' => 'GroupController@show']);