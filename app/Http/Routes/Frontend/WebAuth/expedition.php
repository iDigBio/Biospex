<?php
// Begin Expeditions Controller
$router->get('expeditions', [
    'uses' => 'ExpeditionsController@index',
    'as'   => 'expeditions.get.index'
]);

$router->get('projects/{projects}/expeditions', [
    'uses' => 'ExpeditionsController@ajax',
    'as'   => 'projects.expeditions.get.ajax'
]);

$router->get('projects/{projects}/expeditions/create', [
    'uses' => 'ExpeditionsController@create',
    'as'   => 'projects.expeditions.get.create'
]);

$router->post('projects/{projects}/expeditions', [
    'uses' => 'ExpeditionsController@store',
    'as'   => 'projects.expeditions.post.store'
]);

$router->get('projects/{projects}/expeditions/{expeditions}', [
    'uses' => 'ExpeditionsController@show',
    'as'   => 'projects.expeditions.get.show'
]);

$router->get('projects/{projects}/expeditions/{expeditions}/edit', [
    'uses' => 'ExpeditionsController@edit',
    'as'   => 'projects.expeditions.get.edit'
]);

$router->put('projects/{projects}/expeditions/{expeditions}', [
    'uses' => 'ExpeditionsController@update',
    'as'   => 'projects.expeditions.put.update'
]);

$router->delete('projects/{projects}/expeditions/{expeditions}', [
    'uses' => 'ExpeditionsController@delete',
    'as'   => 'projects.expeditions.delete.delete'
]);

$router->get('projects/{projects}/expeditions/{expeditions}/duplicate', [
    'uses' => 'ExpeditionsController@duplicate',
    'as'   => 'projects.expeditions.get.duplicate'
]);

$router->get('projects/{projects}/expeditions/{expeditions}/process', [
    'uses' => 'ExpeditionsController@process',
    'as'   => 'projects.expeditions.get.process'
]);

$router->delete('projects/{projects}/expeditions/{expeditions}/stop', [
    'uses' => 'ExpeditionsController@stop',
    'as'   => 'projects.expeditions.delete.stop'
]);

$router->get('projects/{projects}/expeditions/{expeditions}/ocr', [
    'uses' => 'ExpeditionsController@ocr',
    'as'   => 'projects.expeditions.get.ocr'
]);