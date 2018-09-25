<?php
// Begin Ajax Routes
$router->get('ajax/{project}/chart')->uses('AjaxController@loadAmChart')->name('ajax.get.chart');
$router->get('ajax/{event}/scoreboard')->uses('AjaxController@scoreboard')->name('ajax.get.scoreboard');
$router->get('poll')->uses('AjaxController@poll')->name('ajax.get.poll');
