<?php
// Beging Expedition public routes
$router->get('expeditions')->uses('ExpeditionsController@index')->name('expeditions.get.index');