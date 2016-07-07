<?php

// Begin WorkflowsController
$router->get('workflows', [
    'uses' => 'WorkflowsController@index',
    'as'   => 'admin.workflows.index'
]);

$router->get('workflows/create', [
    'uses' => 'WorkflowsController@create',
    'as'   => 'admin.workflows.create'
]);

$router->post('workflows/create', [
    'uses' => 'WorkflowsController@store',
    'as'   => 'admin.workflows.store'
]);

$router->get('workflows/{workflows}', [
    'uses' => 'WorkflowsController@show',
    'as'   => 'admin.workflows.show'
]);

$router->get('workflows/{workflows}/edit', [
    'uses' => 'WorkflowsController@edit',
    'as'   => 'admin.workflows.edit'
]);

$router->put('workflows/{workflows}', [
    'uses' => 'WorkflowsController@update',
    'as'   => 'admin.workflows.update'
]);

$router->delete('workflows/{workflows}', [
    'uses' => 'WorkflowsController@delete',
    'as'   => 'admin.workflows.delete'
]);

$router->delete('workflows/{workflows}/trash', [
    'uses' => 'WorkflowsController@trash',
    'as'   => 'admin.workflows.trash'
]);

$router->get('workflows/{workflows}/enable', [
    'uses' => 'WorkflowsController@enable',
    'as'   => 'admin.workflows.enable'
]);

$router->get('workflows/{workflows}/disable', [
    'uses' => 'WorkflowsController@disable',
    'as'   => 'admin.workflows.disable'
]);