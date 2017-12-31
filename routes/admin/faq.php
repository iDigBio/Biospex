<?php

// Index
$router->get('faqs')->uses('FaqsController@index')->name('admin.faqs.index');

// Begin Faqs
$router->get('faqs/{categories}')->uses('FaqsController@create')->name('admin.faqs.create');
$router->post('faqs/{categories?}')->uses('FaqsController@store')->name('admin.faqs.store');
$router->get('faqs/{categories}/{faqs}')->uses('FaqsController@edit')->name('admin.faqs.edit');
$router->put('faqs/{categories}/{faqs}')->uses('FaqsController@update')->name('admin.faqs.update');
$router->delete('faqs/{categories}/{faqs}')->uses('FaqsController@delete')->name('admin.faqs.delete');

// Begin Categories
$router->get('faqs/{categories}/{faqs}/categories')->uses('FaqsController@editCategory')->name('admin.faqs.categories.edit');
$router->put('faqs/{categories}/{faqs}/categories')->uses('FaqsController@updateCategory')->name('admin.faqs.categories.update');
$router->post('faqs/create/category')->uses('FaqsController@storeCategory')->name('admin.faqs.category.store');
