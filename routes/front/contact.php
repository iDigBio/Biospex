<?php
// Contact routes

// Contact form
$router->get('contact')->uses('ContactController@index')->name('contact.get.index');
$router->post('contact')->uses('ContactController@create')->name('contact.post.create');
