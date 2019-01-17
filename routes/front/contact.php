<?php
// Contact routes

// Contact form
$router->get('contact')->uses('ContactController@index')->name('front.contact.index');
$router->post('contact')->uses('ContactController@create')->name('front.contact.create');
