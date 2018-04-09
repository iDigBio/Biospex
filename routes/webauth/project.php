<?php

// Begin ProjectsController
$router->get('projects')->uses('ProjectsController@index')->name('webauth.projects.index');
$router->get('projects/create')->uses('ProjectsController@create')->name('webauth.projects.create');
$router->post('projects/create')->uses('ProjectsController@store')->name('webauth.projects.store');
$router->get('projects/{projects}')->uses('ProjectsController@show')->name('webauth.projects.show');
$router->get('projects/{projects}/edit')->uses('ProjectsController@edit')->name('webauth.projects.edit');
$router->put('projects/{projects}')->uses('ProjectsController@update')->name('webauth.projects.update');
$router->delete('projects/{projects}')->uses('ProjectsController@delete')->name('webauth.projects.delete');
$router->delete('projects/{projects}/destroy')->uses('ProjectsController@destroy')->name('webauth.projects.destroy');
$router->get('projects/{projects}/restore')->uses('ProjectsController@restore')->name('webauth.projects.restore');
$router->get('projects/{projects}/duplicate')->uses('ProjectsController@duplicate')->name('webauth.projects.duplicate');
$router->get('projects/{projects}/explore')->uses('ProjectsController@explore')->name('projects.get.explore');
$router->get('projects/{projects}/ocr')->uses('ProjectsController@ocr')->name('webauth.projects.ocr');
$router->get('projects/{projects}/stats')->uses('ProjectsController@stats')->name('webauth.projects.stats');
