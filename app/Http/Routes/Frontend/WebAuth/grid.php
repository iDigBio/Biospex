<?php

// Project/Grid
$router->get('/projects/{projects}/grids/load', ['as' => 'web.grids.load', 'uses' => 'GridsController@load']);
$router->get('/projects/{projects}/grids/explore', ['as' => 'web.grids.explore', 'uses' => 'GridsController@explore']);
$router->get('/projects/{projects}/grids/expeditions/create', ['as' => 'web.grids.create', 'uses' => 'GridsController@expeditionsCreate']);
$router->get('/projects/{projects}/grids/expeditions/{expeditions}', ['as' => 'web.grids.show', 'uses' => 'GridsController@expeditionsShow']);
$router->get('/projects/{projects}/grids/expeditions/{expeditions}/edit', ['as' => 'web.grids.edit', 'uses' => 'GridsController@expeditionsEdit']);
