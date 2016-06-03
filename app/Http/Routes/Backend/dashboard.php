<?php

$router->get('dashboard', [
    'uses' => 'DashboardController@index',
    'as'   => 'dashboard.get.index'
]);
