<?php

// Begin GroupsController
$router->get('groups')->uses('GroupsController@index')->name('webauth.groups.index');
$router->get('groups/create')->uses('GroupsController@create')->name('webauth.groups.create');
$router->post('groups')->uses('GroupsController@store')->name('webauth.groups.store');
$router->get('groups/{groups}')->uses('GroupsController@show')->name('webauth.groups.show');
$router->get('groups/{groups}/edit')->uses('GroupsController@edit')->name('webauth.groups.edit');
$router->put('groups/{groups}')->uses('GroupsController@update')->name('webauth.groups.update');
$router->delete('groups/{groups}')->uses('GroupsController@delete')->name('webauth.groups.delete');
$router->delete('groups/{groups}/{user}')->uses('GroupsController@deleteUser')->name('webauth.groups.deleteUser');

$router->delete('groups/{groups}/destroy')->uses('GroupsController@destroy')->name('webauth.groups.destroy');
$router->get('groups/{groups}/restore')->uses('GroupsController@restore')->name('webauth.groups.restore');
