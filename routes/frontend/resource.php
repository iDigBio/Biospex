<?php

// Begin Resource
$router->get('resource')->uses('ResourcesController@index')->name('frontend.resources.index');
$router->get('resource/{id}')->uses('ResourcesController@download')->name('frontend.resources.download');