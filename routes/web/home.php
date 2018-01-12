<?php

// Home and Welcome
$router->get('/')->uses('HomeController@index')->name('home');
$router->get('/project-list/{count?}')->uses('HomeController@projects')->name('home.project-list');

// Contact form
$router->get('contact')->uses('HomeController@getContact')->name('home.get.contact');
$router->post('contact')->uses('HomeController@postContact')->name('home.post.contact');

// Begin Vision
$router->get('ourvision')->uses('HomeController@vision')->name('home.get.vision');

// Begin Project Page
$router->get('project/{slug}')->uses('HomeController@project')->name('home.get.project');
$router->get('project/{project}/chart')->uses('HomeController@loadAmChart')->name('home.get.chart');

