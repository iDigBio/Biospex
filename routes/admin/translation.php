<?php

$router->get('translations')->uses('TranslationsController@getIndex')->name('admin.translations.index');
$router->get('translations/view/{group?}')->uses('TranslationsController@getView')->name('admin.translations.view');
$router->post('translations/edit/{group?}')->uses('TranslationsController@postEdit')->name('admin.translations.edit');
$router->post('translations/import')->uses('TranslationsController@postImport')->name('admin.translations.import');
$router->post('translations/find')->uses('TranslationsController@postFind')->name('admin.translations.find');
$router->post('translations/publish/{group}')->uses('TranslationsController@postPublish')->name('admin.translations.publish');
$router->post('translations/add/{group}')->uses('TranslationsController@postAdd')->name('admin.translations.add');
$router->post('translations/delete/{group}/{key}')->uses('TranslationsController@postDelete')->name('admin.translations.delete');
$router->get('translations/preview/{id}')->uses('TranslationsController@preview')->name('admin.translations.preview');