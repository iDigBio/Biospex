<?php

$router->get('projects/{projects}/statistics')->uses('StatisticsController@index')->name('webauth.statistics.index');