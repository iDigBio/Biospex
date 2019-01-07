<?php
// Beging Expedition public routes
$router->get('expeditions')->uses('ExpeditionsController@index')->name('expeditions.get.index');
$router->get('expeditions/public/{sort?}/{order?}')->uses('ExpeditionsController@index')->name('expeditions.public.get.sort');
$router->get('expeditions/completed/{sort?}/{order?}')->uses('ExpeditionsController@completed')->name('expeditions.completed.get.sort');