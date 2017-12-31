<?php

// Begin ProjectsController
$router->get('projects')->uses('ProjectsController@index')->name('admin.projects.index');
$router->get('projects/{projects}/edit')->uses('ProjectsController@index')->name('admin.projects.edit');
$router->put('projects/{projects}')->uses('ProjectsController@update')->name('admin.projects.update');
$router->post('projects')->uses('ProjectsController@store')->name('admin.projects.store');
$router->delete('projects/{projects}')->uses('ProjectsController@delete')->name('admin.projects.delete');
$router->delete('projects/{projects}/destroy')->uses('ProjectsController@destroy')->name('admin.projects.destroy');
$router->get('projects/{projects}/restore')->uses('ProjectsController@restore')->name('admin.projects.restore');

