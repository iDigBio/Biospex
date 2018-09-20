<?php

// Begin Expeditions Controller
$router->get('expeditions')->uses('ExpeditionsController@index')->name('admin.expeditions.index');
$router->get('projects/{projects}/expeditions/create')->uses('ExpeditionsController@create')->name('admin.expeditions.create');
$router->post('projects/{projects}/expeditions')->uses('ExpeditionsController@store')->name('admin.expeditions.store');
$router->get('projects/{projects}/expeditions/{expeditions}')->uses('ExpeditionsController@show')->name('admin.expeditions.show');
$router->get('projects/{projects}/expeditions/{expeditions}/edit')->uses('ExpeditionsController@edit')->name('admin.expeditions.edit');
$router->put('projects/{projects}/expeditions/{expeditions}')->uses('ExpeditionsController@update')->name('admin.expeditions.update');
$router->delete('projects/{projects}/expeditions/{expeditions}')->uses('ExpeditionsController@delete')->name('admin.expeditions.delete');
$router->delete('projects/{projects}/expeditions/{expeditions}/destroy')->uses('ExpeditionsController@destroy')->name('admin.expeditions.destroy');
$router->get('projects/{projects}/expeditions/{expeditions}/restore')->uses('ExpeditionsController@restore')->name('admin.expeditions.restore');
$router->get('projects/{projects}/expeditions/{expeditions}/duplicate')->uses('ExpeditionsController@duplicate')->name('admin.expeditions.duplicate');
$router->get('projects/{projects}/expeditions/{expeditions}/process')->uses('ExpeditionsController@process')->name('admin.expeditions.process');
$router->delete('projects/{projects}/expeditions/{expeditions}/stop')->uses('ExpeditionsController@stop')->name('admin.expeditions.stop');
$router->get('projects/{projects}/expeditions/{expeditions}/ocr')->uses('ExpeditionsController@ocr')->name('admin.expeditions.ocr');