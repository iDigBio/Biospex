<?php

$router->get('dashboard', [
    'uses' => 'DashboardController@index',
    'as'   => 'admin.dashboard.index'
]);
