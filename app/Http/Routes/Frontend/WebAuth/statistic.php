<?php

$router->get('projects/{projects}/statistics', [
    'uses' => 'StatisticsController@index',
    'as'   => 'web.statistics.index'
]);
