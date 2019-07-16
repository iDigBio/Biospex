<?php

// Home
/**
 * @param $router Router
 */
$router->get('/')->uses('HomeController@index')->name('home');
$router->get('/project-list/{count?}')->uses('HomeController@projects')->name('home.project-list');

