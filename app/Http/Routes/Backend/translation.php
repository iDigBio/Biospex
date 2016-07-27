<?php

$router->get('translations', [
    'uses' => 'TranslationsController@getIndex',
    'as' => 'admin.translations.index'
]);

$router->get('translations/view/{group?}', [
    'uses' => 'TranslationsController@getView',
    'as' => 'admin.translations.view'
]);

$router->post('translations/edit/{group?}', [
    'uses' => 'TranslationsController@postEdit',
    'as' => 'admin.translations.edit'
]);

$router->post('translations/import', [
    'uses' => 'TranslationsController@postImport',
    'as' => 'admin.translations.import'
]);

$router->post('translations/find', [
    'uses' => 'TranslationsController@postFind',
    'as' => 'admin.translations.find'
]);

$router->post('translations/publish/{group}', [
    'uses' => 'TranslationsController@postPublish',
    'as' => 'admin.translations.publish'
]);

$router->post('translations/add/{group}', [
    'uses' => 'TranslationsController@postAdd',
    'as' => 'admin.translations.add'
]);

$router->post('translations/delete/{group}/{key}', [
    'uses' => 'TranslationsController@postDelete',
    'as' => 'admin.translations.delete'
]);

$router->get('translations/preview/{id}', [
    'uses' => 'TranslationsController@preview',
    'as' => 'admin.translations.preview'
]);