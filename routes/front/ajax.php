<?php
// Begin Ajax Routes
$router->get('ajax/chart/{project}')->uses('AjaxController@loadAmChart')->name('ajax.get.chart');
$router->get('ajax/scoreboard/{event}')->uses('AjaxController@scoreboard')->name('ajax.get.scoreboard');
$router->get('ajax/step/{event}/{load?}')->uses('AjaxController@eventStepChart')->name('ajax.get.step');
$router->get('poll')->uses('AjaxController@poll')->name('ajax.get.poll');
$router->get('bingos/{bingo}/winner/{map}')->uses('AjaxController@bingoWinner')->name('ajax.get.bingoWinner');
