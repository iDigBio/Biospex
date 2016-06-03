<?php

// Project/Grid
$router->get('/projects/{projects}/grids/load', ['as' => 'projects.grids.load', 'uses' => 'GridsController@load']);
$router->get('/projects/{projects}/grids/explore', ['as' => 'projects.grids.explore', 'uses' => 'GridsController@explore']);
$router->get('/projects/{projects}/grids/expeditions/create', ['as' => 'projects.grids.expeditions.create', 'uses' => 'GridsController@expeditionsCreate']);
$router->get('/projects/{projects}/grids/expeditions/{expeditions}', ['as' => 'projects.grids.expeditions.show', 'uses' => 'GridsController@expeditionsShow']);
$router->get('/projects/{projects}/grids/expeditions/{expeditions}/edit', ['as' => 'projects.grids.expeditions.edit', 'uses' => 'GridsController@expeditionsEdit']);
