<?php

// Begin Notifications Controller
$router->get('notifications', [
    'uses' => 'NotificationsController@index',
    'as'   => 'web.notifications.index'
]);

$router->get('notifications/{notifications}', [
    'uses' => 'NotificationsController@show',
    'as'   => 'web.notifications.show'
]);

$router->delete('notifications/{notifications}', [
    'uses' => 'NotificationsController@delete',
    'as'   => 'web.notifications.delete'
]);

$router->delete('notifications/{notifications}/destroy', [
    'uses' => 'NotificationsController@destroy',
    'as'   => 'web.notifications.destroy'
]);

$router->get('notifications/{notifications}/restore', [
    'uses' => 'NotificationsController@restore',
    'as'   => 'web.notifications.restore'
]);
