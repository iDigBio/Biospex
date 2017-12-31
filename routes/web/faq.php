<?php

// Begin Faq
$router->get('faq')->uses('FaqsController@index')->name('web.faqs.index');
$router->get('faq/{category}')->uses('FaqsController@show')->name('web.faqs.show');