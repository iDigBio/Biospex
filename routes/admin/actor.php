<?php

// Begin ActorsController
$router->get('actors')->uses('ActorsController@index')->name('admin.actors.index');
$router->get('actors/create')->uses('ActorsController@create')->name('admin.actors.create');
$router->post('actors/create')->uses('ActorsController@store')->name('admin.actors.store');
$router->get('actors/{actors}/edit')->uses('ActorsController@edit')->name('admin.actors.edit');
$router->put('actors/{actors}')->uses('ActorsController@update')->name('admin.actors.update');
$router->delete('actors/{actors}')->uses('ActorsController@delete')->name('admin.actors.delete');

