<?php
// Begin Ajax Routes
$router->get('ajax/chart/{project}')->uses('AjaxController@loadAmChart')->name('ajax.get.chart');
$router->get('ajax/scoreboard/{event}')->uses('AjaxController@scoreboard')->name('ajax.get.scoreboard');
$router->get('poll')->uses('AjaxController@poll')->name('ajax.get.poll');
