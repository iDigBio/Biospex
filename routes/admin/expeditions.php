<?php

// Begin ExpeditionsController
$router->get('expeditions')->uses('ExpeditionsController@index')->name('admin.expeditions.index');
$router->get('expeditions/{expeditions}/edit')->uses('ExpeditionsController@index')->name('admin.expeditions.edit');
$router->put('expeditions/{expeditions}')->uses('ExpeditionsController@update')->name('admin.expeditions.update');
$router->post('expeditions')->uses('ExpeditionsController@store')->name('admin.expeditions.store');
$router->delete('expeditions/{expeditions}')->uses('ExpeditionsController@delete')->name('admin.expeditions.delete');
$router->delete('expeditions/{expeditions}/destroy')->uses('ExpeditionsController@destroy')->name('admin.expeditions.destroy');
$router->get('expeditions/{expeditions}/restore')->uses('ExpeditionsController@restore')->name('admin.expeditions.restore');