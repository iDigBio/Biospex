<?php

$router->get('projects/{projects}/statistics')->uses('StatisticsController@index')->name('admin.statistics.index');