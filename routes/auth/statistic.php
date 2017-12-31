<?php

$router->get('projects/{projects}/statistics')->uses('StatisticsController@index')->name('web.statistics.index');