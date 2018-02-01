<?php

// Project/Grid
$router->get('/projects/{projects}/grids/load', ['as' => 'webauth.grids.load', 'uses' => 'GridsController@load']);
$router->get('/projects/{projects}/grids/explore', ['as' => 'webauth.grids.explore', 'uses' => 'GridsController@explore']);
$router->post('/projects/{projects}/grids/explore', ['as' => 'webauth.grids.delete', 'uses' => 'GridsController@delete']);
$router->get('/projects/{projects}/grids/expeditions/create', ['as' => 'webauth.grids.create', 'uses' => 'GridsController@expeditionsCreate']);
$router->get('/projects/{projects}/grids/expeditions/{expeditions}', ['as' => 'webauth.grids.show', 'uses' => 'GridsController@expeditionsShow']);
$router->get('/projects/{projects}/grids/expeditions/{expeditions}/edit', ['as' => 'webauth.grids.edit', 'uses' => 'GridsController@expeditionsEdit']);
$router->get('/projects/{projects}/grids/export', ['as' => 'webauth.grids.project.export', 'uses' => 'GridsController@export']);
$router->get('/projects/{projects}/grids/expeditions/{expeditions}/export', ['as' => 'webauth.grids.expedition.export', 'uses' => 'GridsController@export']);
