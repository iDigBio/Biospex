<?php

// Home
$router->get('/')->uses('HomeController@index')->name('home');
$router->get('/project-list/{count?}')->uses('HomeController@projects')->name('home.project-list');
$router->get('/chart')->uses('HomeController@test')->name('home.test');


