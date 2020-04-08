<?php

// Begin BingosController
$router->get('bingos')->uses('BingosController@index')->name('admin.bingos.index');
$router->post('bingos/sort/')->uses('BingosController@sort')->name('admin.bingos.sort');
$router->get('bingos/create')->uses('BingosController@create')->name('admin.bingos.create');
$router->post('bingos/create')->uses('BingosController@store')->name('admin.bingos.store');
$router->get('bingos/{bingos}')->uses('BingosController@read')->name('admin.bingos.read');
$router->get('bingos/{bingos}/edit')->uses('BingosController@edit')->name('admin.bingos.edit');
$router->put('bingos/{bingos}')->uses('BingosController@update')->name('admin.bingos.update');
$router->delete('bingos/{bingos}')->uses('BingosController@delete')->name('admin.bingos.delete');

$router->get('bingos/{bingos}/generate')->uses('BingosController@generate')->name('front.bingos.generate');
