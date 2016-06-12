<?php
$router->get('faq', [
    'uses' => 'FaqsController@index',
    'as'   => 'admin.faqs.index'
]);

$router->get('faq/create/{category?}', [
    'uses' => 'FaqsController@create',
    'as'   => 'admin.faqs.create'
]);

$router->post('faq/create', [
    'uses' => 'FaqsController@store',
    'as'   => 'admin.faqs.store'
]);

$router->post('faq/createCategory', [
    'uses' => 'FaqsController@storeCategory',
    'as'   => 'admin.faqs.category.store'
]);

$router->get('faq/{faq}', [
    'uses' => 'FaqsController@show',
    'as'   => 'admin.faqs.show'
]);

$router->get('faq/{category}/{faq?}/edit', [
    'uses' => 'FaqsController@edit',
    'as'   => 'admin.faqs.edit'
]);

$router->put('faq/{category}/{faq}', [
    'uses' => 'FaqsController@update',
    'as'   => 'admin.faqs.update'
]);

$router->put('faq/{category}', [
    'uses' => 'FaqsController@updateCategory',
    'as'   => 'admin.faqs.categories.update'
]);

$router->delete('faq/{category}/{faq?}/edit', [
    'uses' => 'FaqsController@delete',
    'as'   => 'admin.faqs.delete'
]);