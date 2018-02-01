<?php

// Begin EventsController
$router->get('events')->uses('EventsController@index')->name('webauth.events.index');
$router->get('events/create')->uses('EventsController@create')->name('webauth.events.create');
$router->post('events/create')->uses('EventsController@store')->name('webauth.events.store');
$router->get('events/{events}')->uses('EventsController@show')->name('webauth.events.show');
$router->get('events/{events}/edit')->uses('EventsController@edit')->name('webauth.events.edit');
$router->put('events/{events}')->uses('EventsController@update')->name('webauth.events.update');
$router->delete('events/{events}')->uses('EventsController@delete')->name('webauth.events.delete');