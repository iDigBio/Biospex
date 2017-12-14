<?php

// Begin Expeditions Controller
$router->get('expeditions')->uses('ExpeditionsController@index')->name('web.expeditions.index');
$router->get('projects/{projects}/expeditions/create')->uses('ExpeditionsController@create')->name('web.expeditions.create');
$router->post('projects/{projects}/expeditions')->uses('ExpeditionsController@store')->name('web.expeditions.store');
$router->get('projects/{projects}/expeditions/{expeditions}')->uses('ExpeditionsController@show')->name('web.expeditions.show');
$router->get('projects/{projects}/expeditions/{expeditions}/edit')->uses('ExpeditionsController@edit')->name('web.expeditions.edit');
$router->put('projects/{projects}/expeditions/{expeditions}')->uses('ExpeditionsController@update')->name('web.expeditions.update');
$router->delete('projects/{projects}/expeditions/{expeditions}')->uses('ExpeditionsController@delete')->name('web.expeditions.delete');
$router->delete('projects/{projects}/expeditions/{expeditions}/destroy')->uses('ExpeditionsController@destroy')->name('web.expeditions.destroy');
$router->get('projects/{projects}/expeditions/{expeditions}/restore')->uses('ExpeditionsController@restore')->name('web.expeditions.restore');
$router->get('projects/{projects}/expeditions/{expeditions}/duplicate')->uses('ExpeditionsController@duplicate')->name('web.expeditions.duplicate');
$router->get('projects/{projects}/expeditions/{expeditions}/process')->uses('ExpeditionsController@process')->name('web.expeditions.process');
$router->delete('projects/{projects}/expeditions/{expeditions}/stop')->uses('ExpeditionsController@stop')->name('web.expeditions.stop');
$router->get('projects/{projects}/expeditions/{expeditions}/ocr')->uses('ExpeditionsController@ocr')->name('web.expeditions.ocr');