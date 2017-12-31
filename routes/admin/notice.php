<?php

// Begin NoticesController
$router->get('notices')->uses('NoticesController@index')->name('admin.notices.index');
$router->get('notices/create')->uses('NoticesController@create')->name('admin.notices.create');
$router->post('notices/create')->uses('NoticesController@store')->name('admin.notices.store');
$router->get('notices/{notices}')->uses('NoticesController@show')->name('admin.notices.show');
$router->get('notices/{notices}/edit')->uses('NoticesController@edit')->name('admin.notices.edit');
$router->put('notices/{notices}')->uses('NoticesController@update')->name('admin.notices.update');
$router->delete('notices/{notices}')->uses('NoticesController@delete')->name('admin.notices.delete');
$router->delete('notices/{notices}/trash')->uses('NoticesController@trash')->name('admin.notices.trash');
$router->get('notices/{notices}/enable')->uses('NoticesController@enable')->name('admin.notices.enable');
$router->get('notices/{notices}/disable')->uses('NoticesController@disable')->name('admin.notices.disable');