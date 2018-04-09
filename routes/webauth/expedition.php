<?php

// Begin Expeditions Controller
$router->get('expeditions')->uses('ExpeditionsController@index')->name('webauth.expeditions.index');
$router->get('projects/{projects}/expeditions/create')->uses('ExpeditionsController@create')->name('webauth.expeditions.create');
$router->post('projects/{projects}/expeditions')->uses('ExpeditionsController@store')->name('webauth.expeditions.store');
$router->get('projects/{projects}/expeditions/{expeditions}')->uses('ExpeditionsController@show')->name('webauth.expeditions.show');
$router->get('projects/{projects}/expeditions/{expeditions}/edit')->uses('ExpeditionsController@edit')->name('webauth.expeditions.edit');
$router->put('projects/{projects}/expeditions/{expeditions}')->uses('ExpeditionsController@update')->name('webauth.expeditions.update');
$router->delete('projects/{projects}/expeditions/{expeditions}')->uses('ExpeditionsController@delete')->name('webauth.expeditions.delete');
$router->delete('projects/{projects}/expeditions/{expeditions}/destroy')->uses('ExpeditionsController@destroy')->name('webauth.expeditions.destroy');
$router->get('projects/{projects}/expeditions/{expeditions}/restore')->uses('ExpeditionsController@restore')->name('webauth.expeditions.restore');
$router->get('projects/{projects}/expeditions/{expeditions}/duplicate')->uses('ExpeditionsController@duplicate')->name('webauth.expeditions.duplicate');
$router->get('projects/{projects}/expeditions/{expeditions}/process')->uses('ExpeditionsController@process')->name('webauth.expeditions.process');
$router->delete('projects/{projects}/expeditions/{expeditions}/stop')->uses('ExpeditionsController@stop')->name('webauth.expeditions.stop');
$router->get('projects/{projects}/expeditions/{expeditions}/ocr')->uses('ExpeditionsController@ocr')->name('webauth.expeditions.ocr');