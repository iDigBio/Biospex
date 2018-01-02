<?php

$router->get('server')->uses('ServerController@index')->name('admin.server.index');
$router->get('server/show')->uses('ServerController@show')->name('admin.server.show');
$router->get('server/log')->uses('LogController@index')->name('admin.server.log');