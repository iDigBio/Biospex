<?php

// Begin ExpeditionsController
$router->get('expeditions', [
    'uses' => 'ExpeditionsController@index',
    'as'   => 'admin.expeditions.index'
]);

$router->get('expeditions/{expeditions}/edit', [
    'uses' => 'ExpeditionsController@index',
    'as'   => 'admin.expeditions.edit'
]);

$router->put('expeditions/{expeditions}', [
    'uses' => 'ExpeditionsController@update',
    'as'   => 'admin.expeditions.update'
]);

$router->post('expeditions', [
    'uses' => 'ExpeditionsController@store',
    'as'   => 'admin.expeditions.store'
]);

$router->delete('expeditions/{expeditions}', [
    'uses' => 'ExpeditionsController@delete',
    'as'   => 'admin.expeditions.delete'
]);

$router->delete('expeditions/{expeditions}/destroy', [
    'uses' => 'ExpeditionsController@destroy',
    'as'   => 'admin.expeditions.destroy'
]);

$router->get('expeditions/{expeditions}/restore', [
    'uses' => 'ExpeditionsController@restore',
    'as'   => 'admin.expeditions.restore'
]);

// End ExpeditionsController
