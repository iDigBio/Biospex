<?php
$router->get('bingos')->uses('EventsController@index')->name('front.bingos.index');
$router->get('bingos/{event}')->uses('EventsController@read')->name('front.bingos.read');