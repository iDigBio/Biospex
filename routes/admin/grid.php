<?php

// Project/Grid
$router->get('grids/{projects}/explore', ['as' => 'admin.grids.explore', 'uses' => 'GridsController@explore']);
$router->post('grids/{projects}/delete', ['as' => 'admin.grids.delete', 'uses' => 'GridsController@delete']);
$router->post('grids/{projects}/export', ['as' => 'admin.grids.export', 'uses' => 'GridsController@export']);

$router->get('grids/{projects}/expeditions/create', ['as' => 'admin.grids.create', 'uses' => 'GridsController@expeditionsCreate']);
$router->get('grids/{projects}/expeditions/{expeditions}', ['as' => 'admin.grids.show', 'uses' => 'GridsController@expeditionsShow']);
$router->get('grids/{projects}/expeditions/{expeditions}/edit', ['as' => 'admin.grids.edit', 'uses' => 'GridsController@expeditionsEdit']);
$router->post('grids/{projects}/expeditions/{expeditions}/export', ['as' => 'admin.grids.expedition.export', 'uses' => 'GridsController@export']);

