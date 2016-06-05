<?php
$router->get('faq', [
    'uses' => 'FaqsController@index',
    'as'   => 'admin.faqs.index'
]);

$router->get('faq/create', [
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

$router->get('faq/{faq}/edit', [
    'uses' => 'FaqsController@edit',
    'as'   => 'admin.faqs.edit'
]);

$router->put('faq/{faq}', [
    'uses' => 'FaqsController@update',
    'as'   => 'admin.faqs.update'
]);

$router->delete('faq/{faq}', [
    'uses' => 'FaqsController@delete',
    'as'   => 'admin.faq.delete'
]);