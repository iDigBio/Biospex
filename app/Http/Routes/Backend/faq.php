<?php

// Index
$router->get('faqs', [
    'uses' => 'FaqsController@index',
    'as'   => 'admin.faqs.index'
]);

// Begin Faqs
$router->get('faqs/{categories}', [
    'uses' => 'FaqsController@create',
    'as'   => 'admin.faqs.create'
]);

$router->post('faqs/{categories?}', [
    'uses' => 'FaqsController@store',
    'as'   => 'admin.faqs.store'
]);

$router->get('faqs/{categories}/{faqs}', [
    'uses' => 'FaqsController@edit',
    'as'   => 'admin.faqs.edit'
]);

$router->put('faqs/{categories}/{faqs}', [
    'uses' => 'FaqsController@update',
    'as'   => 'admin.faqs.update'
]);


$router->delete('faqs/{categories}/{faqs}', [
    'uses' => 'FaqsController@delete',
    'as'   => 'admin.faqs.delete'
]);
// End Faqs


// Begin Categories
$router->get('faqs/{categories}/{faqs}/categories', [
    'uses' => 'FaqsController@editCategory',
    'as'   => 'admin.faqs.categories.edit'
]);

$router->put('faqs/{categories}/{faqs}/categories', [
    'uses' => 'FaqsController@updateCategory',
    'as'   => 'admin.faqs.categories.update'
]);

$router->post('faqs/create/category', [
    'uses' => 'FaqsController@storeCategory',
    'as'   => 'admin.faqs.category.store'
]);
// End Categories