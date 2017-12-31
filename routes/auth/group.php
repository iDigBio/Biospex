<?php

// Begin GroupsController
$router->get('groups')->uses('GroupsController@index')->name('web.groups.index');
$router->get('groups/create')->uses('GroupsController@create')->name('web.groups.create');
$router->post('groups')->uses('GroupsController@store')->name('web.groups.store');
$router->get('groups/{groups}')->uses('GroupsController@show')->name('web.groups.show');
$router->get('groups/{groups}/edit')->uses('GroupsController@edit')->name('web.groups.edit');
$router->put('groups/{groups}')->uses('GroupsController@update')->name('web.groups.update');
$router->delete('groups/{groups}')->uses('GroupsController@delete')->name('web.groups.delete');
$router->delete('groups/{groups}/destroy')->uses('GroupsController@destroy')->name('web.groups.destroy');
$router->get('groups/{groups}/restore')->uses('GroupsController@restore')->name('web.groups.restore');
