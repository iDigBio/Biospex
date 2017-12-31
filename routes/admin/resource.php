<?php

// Begin ResourcesController
$router->get('resources')->uses('ResourcesController@index')->name('admin.resources.index');
$router->get('resources/create')->uses('ResourcesController@create')->name('admin.resources.create');
$router->post('resources/create')->uses('ResourcesController@store')->name('admin.resources.store');
$router->get('resources/{resources}')->uses('ResourcesController@show')->name('admin.resources.show');
$router->get('resources/{resources}/edit')->uses('ResourcesController@edit')->name('admin.resources.edit');
$router->put('resources/{resources}')->uses('ResourcesController@update')->name('admin.resources.update');
$router->delete('resources/{resources}')->uses('ResourcesController@delete')->name('admin.resources.delete');
$router->delete('resources/{resources}/trash')->uses('ResourcesController@trash')->name('admin.resources.trash');
$router->post('resources/{resources}/order/{order}')->uses('ResourcesController@order')->name('admin.resources.order');
