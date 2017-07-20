<?php
// Begin Expeditions Controller
$router->get('expeditions', [
    'uses' => 'ExpeditionsController@index',
    'as'   => 'web.expeditions.index'
]);

$router->get('projects/{projects}/expeditions', [
    'uses' => 'ExpeditionsController@ajax',
    'as'   => 'web.expeditions.get.ajax'
]);

$router->get('projects/{projects}/expeditions/create', [
    'uses' => 'ExpeditionsController@create',
    'as'   => 'web.expeditions.create'
]);

$router->post('projects/{projects}/expeditions', [
    'uses' => 'ExpeditionsController@store',
    'as'   => 'web.expeditions.store'
]);

$router->get('projects/{projects}/expeditions/{expeditions}', [
    'uses' => 'ExpeditionsController@show',
    'as'   => 'web.expeditions.show'
]);

$router->get('projects/{projects}/expeditions/{expeditions}/edit', [
    'uses' => 'ExpeditionsController@edit',
    'as'   => 'web.expeditions.edit'
]);

$router->put('projects/{projects}/expeditions/{expeditions}', [
    'uses' => 'ExpeditionsController@update',
    'as'   => 'web.expeditions.update'
]);

$router->delete('projects/{projects}/expeditions/{expeditions}', [
    'uses' => 'ExpeditionsController@delete',
    'as'   => 'web.expeditions.delete'
]);

$router->delete('projects/{projects}/expeditions/{expeditions}/destroy', [
    'uses' => 'ExpeditionsController@destroy',
    'as'   => 'web.expeditions.destroy'
]);

$router->get('projects/{projects}/expeditions/{expeditions}/restore', [
    'uses' => 'ExpeditionsController@restore',
    'as'   => 'web.expeditions.restore'
]);

$router->get('projects/{projects}/expeditions/{expeditions}/duplicate', [
    'uses' => 'ExpeditionsController@duplicate',
    'as'   => 'web.expeditions.duplicate'
]);

$router->get('projects/{projects}/expeditions/{expeditions}/process', [
    'uses' => 'ExpeditionsController@process',
    'as'   => 'web.expeditions.process'
]);

$router->delete('projects/{projects}/expeditions/{expeditions}/stop', [
    'uses' => 'ExpeditionsController@stop',
    'as'   => 'web.expeditions.stop'
]);

$router->get('projects/{projects}/expeditions/{expeditions}/ocr', [
    'uses' => 'ExpeditionsController@ocr',
    'as'   => 'web.expeditions.ocr'
]);

$router->get('projects/{projects}/expeditions/{expeditions}/transcripts', [
    'uses' => 'ExpeditionsController@transcripts',
    'as'   => 'web.expeditions.transcripts'
]);