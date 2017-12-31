<?php

// Begin ProjectsController
$router->get('projects')->uses('ProjectsController@index')->name('web.projects.index');
$router->get('projects/create')->uses('ProjectsController@create')->name('web.projects.create');
$router->post('projects/create')->uses('ProjectsController@store')->name('web.projects.store');
$router->get('projects/{projects}')->uses('ProjectsController@show')->name('web.projects.show');
$router->get('projects/{projects}/edit')->uses('ProjectsController@edit')->name('web.projects.edit');
$router->put('projects/{projects}')->uses('ProjectsController@update')->name('web.projects.update');
$router->delete('projects/{projects}')->uses('ProjectsController@delete')->name('web.projects.delete');
$router->delete('projects/{projects}/destroy')->uses('ProjectsController@destroy')->name('web.projects.destroy');
$router->get('projects/{projects}/restore')->uses('ProjectsController@restore')->name('web.projects.restore');
$router->get('projects/{projects}/duplicate')->uses('ProjectsController@duplicate')->name('web.projects.duplicate');
$router->get('projects/{projects}/explore')->uses('ProjectsController@explore')->name('projects.get.explore');
$router->get('projects/{projects}/ocr')->uses('ProjectsController@ocr')->name('web.projects.ocr');
$router->get('projects/{projects}/stats')->uses('ProjectsController@stats')->name('web.projects.stats');
