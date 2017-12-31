<?php

// Begin Resource
$router->get('resource')->uses('ResourcesController@index')->name('web.resources.index');
$router->get('resource/{id}')->uses('ResourcesController@download')->name('web.resources.download');