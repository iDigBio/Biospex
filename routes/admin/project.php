<?php

// Begin ProjectsController
$router->get('projects')->uses('ProjectsController@index')->name('admin.projects.index');
$router->get('projects/create')->uses('ProjectsController@create')->name('admin.projects.create');
$router->post('projects/create')->uses('ProjectsController@store')->name('admin.projects.store');
$router->get('projects/{projects}')->uses('ProjectsController@show')->name('admin.projects.show');
$router->get('projects/{projects}/edit')->uses('ProjectsController@edit')->name('admin.projects.edit');
$router->put('projects/{projects}')->uses('ProjectsController@update')->name('admin.projects.update');
$router->get('projects/{projects}/clone')->uses('ProjectsController@clone')->name('admin.projects.clone');
$router->get('projects/{projects}/explore')->uses('ProjectsController@explore')->name('admin.projects.explore');
$router->get('projects/{projects}/statistics')->uses('ProjectsController@statistics')->name('admin.projects.statistics');

$router->post('projects/sort')->uses('ProjectsController@sort')->name('admin.projects.sort');

$router->post('projects/{projects}/ocr')->uses('ProjectsController@ocr')->name('admin.projects.ocr');

$router->delete('projects/{projects}')->uses('ProjectsController@delete')->name('admin.projects.delete');
$router->delete('projects/{projects}/subject')->uses('ProjectsController@deleteSubjects')->name('admin.projects.deleteSubjects');
