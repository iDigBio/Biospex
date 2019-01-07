<?php

// Welcome
$router->get('/welcome')->uses('HomeController@welcome')->name('admin.home.welcome');