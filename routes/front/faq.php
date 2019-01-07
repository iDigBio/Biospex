<?php

// Begin Faq
$router->get('faq')->uses('FaqsController@index')->name('faqs.get.index');
$router->get('faq/{category}')->uses('FaqsController@show')->name('faqs.get.show');