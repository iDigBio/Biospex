<?php

// Begin Notifications Controller
$router->get('notifications')->uses('NotificationsController@index')->name('web.notifications.index');
$router->get('notifications/{notifications}')->uses('NotificationsController@show')->name('web.notifications.show');
$router->delete('notifications/{notifications}')->uses('NotificationsController@delete')->name('web.notifications.delete');
$router->delete('notifications/{notifications}/destroy')->uses('NotificationsController@destroy')->name('web.notifications.destroy');
$router->get('notifications/{notifications}/restore')->uses('NotificationsController@restore')->name('web.notifications.restore');