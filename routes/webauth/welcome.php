<?php

// Welcome
$router->get('/welcome')->uses('HomeController@welcome')->name('webauth.home.welcome');