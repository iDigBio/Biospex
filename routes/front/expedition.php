<?php
// Beging Expedition public routes
$router->get('expeditions')->uses('ExpeditionsController@index')->name('expeditions.get.index');
$router->post('expeditions/sort')->uses('ExpeditionsController@sort')->name('expeditions.post.sort');