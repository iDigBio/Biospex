<?php

// Project/Grid
$router->get('/projects/{projects}/grids/load', ['as' => 'admin.grids.load', 'uses' => 'GridsController@load']);
$router->get('/projects/{projects}/grids/explore', ['as' => 'admin.grids.explore', 'uses' => 'GridsController@explore']);
$router->post('/projects/{projects}/grids/explore', ['as' => 'admin.grids.delete', 'uses' => 'GridsController@delete']);
$router->get('/projects/{projects}/grids/expeditions/create', ['as' => 'admin.grids.create', 'uses' => 'GridsController@expeditionsCreate']);
$router->get('/projects/{projects}/grids/expeditions/{expeditions}', ['as' => 'admin.grids.show', 'uses' => 'GridsController@expeditionsShow']);
$router->get('/projects/{projects}/grids/expeditions/{expeditions}/edit', ['as' => 'admin.grids.edit', 'uses' => 'GridsController@expeditionsEdit']);
$router->get('/projects/{projects}/grids/export', ['as' => 'admin.grids.project.export', 'uses' => 'GridsController@export']);
$router->get('/projects/{projects}/grids/expeditions/{expeditions}/export', ['as' => 'admin.grids.expedition.export', 'uses' => 'GridsController@export']);
