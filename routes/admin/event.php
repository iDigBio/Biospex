<?php

// Begin EventsController
$router->get('events')->uses('EventsController@index')->name('admin.events.index');
$router->get('events/create')->uses('EventsController@create')->name('admin.events.create');
$router->post('events/create')->uses('EventsController@store')->name('admin.events.store');
$router->get('events/{events}')->uses('EventsController@show')->name('admin.events.show');
$router->get('events/{events}/edit')->uses('EventsController@edit')->name('admin.events.edit');
$router->put('events/{events}')->uses('EventsController@update')->name('admin.events.update');
$router->delete('events/{events}')->uses('EventsController@delete')->name('admin.events.delete');
$router->get('events/{events}/transcriptions')->uses('EventsController@exportTranscriptions')->name('admin.events.exportTranscriptions');
$router->get('events/{events}/users')->uses('EventsController@exportUsers')->name('admin.events.exportUsers');