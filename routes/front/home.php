<?php

// Home
/**
 * @param $router Router
 */
$router->get('/')->uses('HomeController@index')->name('home');

