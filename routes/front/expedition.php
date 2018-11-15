<?php
// Beging Expedition public routes
$router->get('expeditions')->uses('ExpeditionsController@index')->name('expeditions.get.index');
$router->get('expeditions/public/{sorting?}')->uses('ExpeditionsController@index')->name('expeditions.public.get.sort');
$router->get('expeditions/completed/{sorting?}')->uses('ExpeditionsController@completed')->name('expeditions.completed.get.sort');