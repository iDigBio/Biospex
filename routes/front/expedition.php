<?php
// Beging Expedition public routes
$router->get('expeditions')->uses('ExpeditionsController@index')->name('front.expeditions.index');
$router->post('expeditions/sort')->uses('ExpeditionsController@sort')->name('front.expeditions.sort');