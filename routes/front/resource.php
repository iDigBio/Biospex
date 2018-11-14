<?php

// Begin Resource
$router->get('resource')->uses('ResourcesController@index')->name('front.resources.index');
$router->get('resource/{id}')->uses('ResourcesController@download')->name('front.resources.download');