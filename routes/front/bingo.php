<?php
$router->get('bingos')->uses('BingosController@index')->name('front.bingos.index');
$router->get('bingos/{bingo}')->uses('BingosController@show')->name('front.bingos.show');
$router->get('bingos/{bingo}/generate')->uses('BingosController@generate')->name('front.bingos.generate');