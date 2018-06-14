<?php

// Begin WorkflowsController
$router->get('workflows')->uses('WorkflowsController@index')->name('admin.workflows.index');
$router->get('workflows/create')->uses('WorkflowsController@create')->name('admin.workflows.create');
$router->post('workflows/create')->uses('WorkflowsController@store')->name('admin.workflows.store');
$router->get('workflows/{workflows}')->uses('WorkflowsController@show')->name('admin.workflows.show');
$router->get('workflows/{workflows}/edit')->uses('WorkflowsController@edit')->name('admin.workflows.edit');
$router->put('workflows/{workflows}')->uses('WorkflowsController@update')->name('admin.workflows.update');
$router->delete('workflows/{workflows}')->uses('WorkflowsController@delete')->name('admin.workflows.delete');
$router->get('workflows/{workflows}/enable')->uses('WorkflowsController@enable')->name('admin.workflows.enable');
$router->get('workflows/{workflows}/disable')->uses('WorkflowsController@disable')->name('admin.workflows.disable');